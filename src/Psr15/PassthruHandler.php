<?php

declare(strict_types=1);

namespace Crell\KernelBench\Psr15;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * I really dislike this double-layer thing in PSR-15. It's really hard to follow.
 */
readonly class PassthruHandler implements RequestHandlerInterface
{
    public function __construct(
        private MiddlewareInterface $next,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

    }

}
