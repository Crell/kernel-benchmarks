<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events;

use Crell\KernelBench\Errors\Error;
use Crell\KernelBench\Errors\NoResultHandlerFound;
use Crell\KernelBench\Events\Events\HandleError;
use Crell\KernelBench\Events\Events\HandleResponse;
use Crell\KernelBench\Events\Events\PostRouting;
use Crell\KernelBench\Events\Events\PreRouting;
use Crell\KernelBench\Events\Events\ProcessActionResult;
use Crell\KernelBench\Events\Events\RoutingResult;
use Crell\KernelBench\Services\Routing\Router;
use Crell\KernelBench\Services\Routing\RouteResult;
use Crell\KernelBench\Services\Routing\RouteSuccess;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class EventKernel implements RequestHandlerInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private Router $router,
        private ContainerInterface $container,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var PreRouting $event */
        $event = $this->dispatcher->dispatch(new PreRouting($request));
        if ($response = $event->getResponse()) {
            return $response;
        }
        if ($error = $event->getError()) {
            return $this->handleError($error, $event->request());
        }
        $request = $event->request();

        $routingResult = $this->router->route($request);
        $request = $request->withAttribute(RouteResult::class, $routingResult);

        /** @var RoutingResult $event */
        $event = $this->dispatcher->dispatch(new RoutingResult($request, $routingResult));
        if ($response = $event->getResponse()) {
            return $response;
        }
        if ($error = $event->getError()) {
            return $this->handleError($error, $event->request());
        }
        $request = $event->request();

        /** @var PostRouting $event */
        $event = $this->dispatcher->dispatch(new PostRouting($request));
        if ($response = $event->getResponse()) {
            return $response;
        }
        if ($error = $event->getError()) {
            return $this->handleError($error, $event->request());
        }
        $request = $event->request();

        // Call the action.
        $result = $this->callAction($request);

        if (! $result instanceof ResponseInterface) {
            /** @var ProcessActionResult $event */
            $event = $this->dispatcher->dispatch(new ProcessActionResult($result, $request));
            $response = $event->getResponse();
            if (!$response instanceof ResponseInterface) {
                return $this->handleError(new NoResultHandlerFound($request, $result), $event->request);
            }
            $result = $response;
        }

        /** @var HandleResponse $event */
        $event = $this->dispatcher->dispatch(new HandleResponse($result, $request));

        return $event->getResponse();
    }

    private function handleError(Error $error, ServerRequestInterface $request): ResponseInterface
    {
        /** @var ProcessActionResult $event */
        $event = $this->dispatcher->dispatch(new HandleError($error, $request));
        return $event->getResponse();
    }

    private function callAction(ServerRequestInterface $request): mixed
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

        return $this->container->get($routeResult->action)(...$args);
    }
}
