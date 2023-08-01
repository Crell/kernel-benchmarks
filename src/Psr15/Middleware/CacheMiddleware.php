<?php

declare(strict_types=1);

namespace Crell\KernelBench\Psr15\Middleware;

use Crell\KernelBench\Services\RequestCache;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class CacheMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RequestCache $cache,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!in_array(strtoupper($request->getMethod()), ['GET', 'HEAD'])) {
            return $handler->handle($request);
        }

        if ($response = $this->cache->getResponseFor($request)) {
            return $response;
        }

        $response = $handler->handle($request);

        if ($response->getStatusCode() === 200) {
            $this->cache->setResponseFor($request, $response);
        }

        return $response;
    }
}
