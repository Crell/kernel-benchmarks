<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes\Request;

use Crell\KernelBench\Monad\Pipes\RequestPipe;
use Crell\KernelBench\Services\RequestCache;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class CacheLookupPipe implements RequestPipe
{
    public function __construct(
        private RequestCache $cache,
    ) {}

    public function __invoke(ServerRequestInterface $request): ServerRequestInterface|ResponseInterface
    {
        return $this->cache->getResponseFor($request) ?? $request;
    }
}
