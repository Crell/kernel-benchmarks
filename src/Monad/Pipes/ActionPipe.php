<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes;

use Psr\Http\Message\ServerRequestInterface;

interface ActionPipe
{
    public function __invoke(ServerRequestInterface $request): object;
}
