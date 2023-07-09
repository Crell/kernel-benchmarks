<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes;

use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Router\RouteResult;
use Crell\KernelBench\Services\Router\RouteSuccess;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class HandleActionPipe implements ActionPipe
{
    public function __construct(
        private ContainerInterface $container,
        private ResponseBuilder $responseBuilder,
    ) {}

    public function __invoke(ServerRequestInterface $request): object
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

        $actionResult = $this->container->get($routeResult->action)(...$args);

        // Assume a string response means HTML.
        // This may not be an ideal assumption, but it's probably right.
        if (is_string($actionResult)) {
            return $this->responseBuilder->ok(body: $actionResult, contentType: 'text/html');
        }

        return $actionResult;
    }
}
