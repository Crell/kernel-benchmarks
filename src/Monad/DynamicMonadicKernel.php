<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad;

use Crell\KernelBench\Monad\Pipes\ActionPipe;
use Crell\KernelBench\Monad\Pipes\ErrorPipe;
use Crell\KernelBench\Monad\Pipes\RequestPipe;
use Crell\KernelBench\Monad\Pipes\ResponsePipe;
use Crell\KernelBench\Monad\Pipes\ResultPipe;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DynamicMonadicKernel implements RequestHandlerInterface
{
    /** @var array<RequestPipe> */
    private array $requestPipes = [];

    /** @var array<ResponsePipe>  */
    private array $responsePipes = [];

    /** @var array<array{format: string, pipe: ResultPipe}>  */
    private array $resultPipes = [];

    /** @var array<array{type: string, pipe: ErrorPipe}>  */
    private array $errorPipes = [];

    private ActionPipe $actionPipe;

    /**
     * @tod This gets ugly fast.
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {}

    public function addRequestPipe(RequestPipe $pipe): self
    {
        $this->requestPipes[] = $pipe;
        return $this;
    }

    public function addResultPipe(string $format, ResultPipe $pipe): self
    {
        $this->resultPipes[] =  ['format' => $format, 'pipe' => $pipe];
        return $this;
    }

    public function addResponsePipe(ResponsePipe $pipe): self
    {
        $this->responsePipes[] = $pipe;
        return $this;
    }

    public function addErrorPipe(string $type, ErrorPipe $pipe): self
    {
        $this->errorPipes[] = ['type' => $type, 'pipe' => $pipe];
        return $this;
    }

    public function setActionPipe(ActionPipe $pipe): self
    {
        $this->actionPipe = $pipe;
        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pipeline = new RequestPipeline($request);

        foreach ($this->requestPipes as $pipe) {
            $pipeline = $pipeline->request($pipe);
        }

        $pipeline = $pipeline->action($this->actionPipe);

        foreach ($this->resultPipes as $pipe) {
            $pipeline = $pipeline->result($pipe['format'], $pipe['pipe']);
        }
        foreach ($this->responsePipes as $pipe) {
            $pipeline = $pipeline->response($pipe);
        }
        foreach ($this->errorPipes as $pipe) {
            $pipeline = $pipeline->error($pipe['type'], $pipe['pipe']);
        }

        if (! $pipeline->val instanceof ResponseInterface) {
            return $this->handleServerError($request, $pipeline);
        }
        return $pipeline->val;
    }

    private function handleServerError(ServerRequestInterface $request, mixed $result): ResponseInterface
    {
        $body = $this->streamFactory->createStream('Could not resolve to a response.');
        return $this->responseFactory->createResponse(500)->withBody($body);
    }
}
