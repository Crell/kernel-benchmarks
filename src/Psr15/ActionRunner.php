<?php

declare(strict_types=1);

namespace Crell\KernelBench\Psr15;

use Crell\KernelBench\Services\Routing\RouteResult;
use Crell\KernelBench\Services\Routing\RouteSuccess;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class ActionRunner implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var RouteSuccess $route */
        $route = $request->getAttribute(RouteResult::class);

        $args = $route->vars + $route->parameters;

        $result = ($route->action)(...$args);

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        //

    }

}
