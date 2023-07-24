<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes\Error;

use Crell\KernelBench\Errors\Error;
use Crell\KernelBench\Errors\MethodNotAllowed;
use Crell\KernelBench\Monad\Pipes\ErrorPipe;
use Crell\KernelBench\Services\ResponseBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MethodNotAllowedPipe implements ErrorPipe
{
    public function __construct(
        private ResponseBuilder $responseBuilder,
    ) {}

    /**
     * @param MethodNotAllowed $error
     */
    public function __invoke(Error $error, ServerRequestInterface $request): ResponseInterface|Error
    {
        return $this->responseBuilder->createResponse(405, 'Method Not Allowed. use one of ' . implode(' ', $error->allowedMethods), 'text/plain');
    }

}
