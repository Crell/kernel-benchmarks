<?php

declare(strict_types=1);

namespace Crell\KernelBench\Psr15;

use Crell\KernelBench\Services\Routing\RouteResult;
use Crell\KernelBench\Services\Routing\RouteSuccess;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class ActionRunner implements RequestHandlerInterface
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var RouteSuccess $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        $definedParams = $routeResult?->parameters;

        $available = $routeResult->vars;

        // Have to check the types to avoid possible name collisions.
        foreach ($definedParams as $name => $type) {
            if (is_a($type, ServerRequestInterface::class, true)) {
                $available[$name] = $request;
            } elseif (is_a($type, RouteResult::class, true)) {
                // Not sure if this is a good one to include or not.
                $available[$name] = $routeResult;
            }
        }

        $args = array_intersect_key($available, $definedParams);

        $result = $this->container->get($routeResult->action)(...$args);

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        // Unclear what to do here for a View event equivalent.

    }

}
