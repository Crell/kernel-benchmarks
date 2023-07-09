<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes;

use Crell\KernelBench\Errors\Error;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResponsePipe
{
    public function __invoke(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface|Error;
}
