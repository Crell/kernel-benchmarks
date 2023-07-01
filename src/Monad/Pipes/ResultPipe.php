<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes;

use Psr\Http\Message\ServerRequestInterface;

interface ResultPipe
{
    public function __invoke(object $subject, ServerRequestInterface $request): object;
}
