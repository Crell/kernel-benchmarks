<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad;

use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\Errors\PermissionDenied;
use Crell\KernelBench\Monad\Pipes\Error\HtmlForbiddenPipe;
use Crell\KernelBench\Monad\Pipes\Error\HtmlNotFoundPipe;
use Crell\KernelBench\Monad\Pipes\Error\JsonForbiddenPipe;
use Crell\KernelBench\Monad\Pipes\Error\JsonNotFoundPipe;
use Crell\KernelBench\Monad\Pipes\HandleActionPipe;
use Crell\KernelBench\Monad\Pipes\Request\AuthenticateRequestPipe;
use Crell\KernelBench\Monad\Pipes\Request\AuthorizeRequestPipe;
use Crell\KernelBench\Monad\Pipes\Request\CacheLookupPipe;
use Crell\KernelBench\Monad\Pipes\Request\DeriveFormatPipe;
use Crell\KernelBench\Monad\Pipes\Request\LogRequestPipe;
use Crell\KernelBench\Monad\Pipes\Request\ParameterConverterPipe;
use Crell\KernelBench\Monad\Pipes\Request\RoutePipe;
use Crell\KernelBench\Monad\Pipes\Response\CacheRecordPipe;
use Crell\KernelBench\Monad\Pipes\Result\HtmlResultPipe;
use Crell\KernelBench\Monad\Pipes\Result\JsonResultPipe;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class MonadicKernel implements RequestHandlerInterface
{
    /**
     * @tod This gets ugly fast.
     */
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
        private AuthenticateRequestPipe $authenticateRequestPipe,
        private AuthorizeRequestPipe $authorizeRequestPipe,
        private CacheLookupPipe $cacheLookupPipe,
        private CacheRecordPipe $cacheRecordPipe,
        private LogRequestPipe $logRequestPipe,
        private HtmlForbiddenPipe $htmlForbiddenPipe,
        private JsonForbiddenPipe $jsonForbiddenPipe,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pipeline = new RequestPipeline($request);

        $result = $pipeline
            ->request($this->logRequestPipe)
            ->request($this->cacheLookupPipe)
            ->request($this->authenticateRequestPipe)
            ->request($this->deriveFormatPipe)
            ->request($this->routePipe)
            ->request($this->authorizeRequestPipe)
            ->request($this->parameterConverterPipe)
            ->action($this->actionPipe)
            ->result('json', $this->jsonResultPipe)
            ->result('html', $this->htmlResultPipe)
            ->response($this->cacheRecordPipe)
            ->error(NotFound::class, $this->jsonNotFoundPipe)
            ->error(NotFound::class, $this->htmlNotFoundPipe)
            ->error(PermissionDenied::class, $this->jsonForbiddenPipe)
            ->error(PermissionDenied::class, $this->htmlForbiddenPipe)
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
