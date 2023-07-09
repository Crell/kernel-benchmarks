<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes;

use Crell\KernelBench\Errors\Error;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ErrorPipe
{
    public function __invoke(Error $error, ServerRequestInterface $request): ResponseInterface|Error;
}
