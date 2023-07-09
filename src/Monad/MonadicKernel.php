<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad;

use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\Monad\Pipes\DeriveFormatPipe;
use Crell\KernelBench\Monad\Pipes\HandleActionPipe;
use Crell\KernelBench\Monad\Pipes\HtmlNotFoundPipe;
use Crell\KernelBench\Monad\Pipes\HtmlResultPipe;
use Crell\KernelBench\Monad\Pipes\JsonNotFoundPipe;
use Crell\KernelBench\Monad\Pipes\JsonResultPipe;
use Crell\KernelBench\Monad\Pipes\ParameterConverterPipe;
use Crell\KernelBench\Monad\Pipes\RoutePipe;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class MonadicKernel implements RequestHandlerInterface
{
    // @todo Probably need to make the Kernel container aware and grab these directly.
    public function __construct(
        private RoutePipe $routePipe,
        private HandleActionPipe $actionPipe,
        private JsonNotFoundPipe $jsonNotFoundPipe,
        private HtmlNotFoundPipe $htmlNotFoundPipe,
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
        private DeriveFormatPipe $deriveFormatPipe,
        private ParameterConverterPipe $parameterConverterPipe,
        private JsonResultPipe $jsonResultPipe,
        private HtmlResultPipe $htmlResultPipe,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pipeline = new RequestPipeline($request);

        $result = $pipeline
            ->request($this->deriveFormatPipe)
            ->request($this->routePipe)
            ->request($this->parameterConverterPipe)
            ->action($this->actionPipe)
            ->result('json', $this->jsonResultPipe)
            ->result('html', $this->htmlResultPipe)
            ->error(NotFound::class, $this->jsonNotFoundPipe)
            ->error(NotFound::class, $this->htmlNotFoundPipe)
//            ->result('json', $this->jsonResultPipe, ApiProblem::class)
        ;

        if (! $result->val instanceof ResponseInterface) {
            return $this->handleServerError($request, $result);
        }
        return $result->val;
    }

    private function handleServerError(ServerRequestInterface $request, mixed $result): ResponseInterface
    {
        $body = $this->streamFactory->createStream('Could not resolve to a response.');
        return $this->responseFactory->createResponse(500)->withBody($body);
    }
}
