<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes\Error;

use Crell\KernelBench\Errors\Error;
use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\Monad\Pipes\ErrorPipe;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Router\RequestFormat;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class HtmlNotFoundPipe implements ErrorPipe
{
    public function __construct(
        private ResponseBuilder $responseBuilder,
    ) {}

    /**
     * @param NotFound $error
     */
    public function __invoke(Error $error, ServerRequestInterface $request): ResponseInterface|Error
    {
        if ($request->getAttribute(RequestFormat::class)->accept === 'html') {
            return $this->responseBuilder->notFound('<p>Not Found</p>', 'text/html');
        }

        // Let someone else deal with it.
        return $error;
    }
}
