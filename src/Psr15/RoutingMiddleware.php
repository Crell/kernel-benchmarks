<?php

declare(strict_types=1);

namespace Crell\KernelBench\Psr15;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class RoutingMiddleware implements MiddlewareInterface
{
    public function __construct(

    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

    }

}
