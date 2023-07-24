<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events;

use Crell\KernelBench\Errors\Error;
use Crell\KernelBench\Errors\MethodNotAllowed;
use Crell\KernelBench\Errors\NoResultHandlerFound;
use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\Events\Events\HandleError;
use Crell\KernelBench\Events\Events\HandleResponse;
use Crell\KernelBench\Events\Events\PostRouting;
use Crell\KernelBench\Events\Events\PreRouting;
use Crell\KernelBench\Events\Events\ProcessActionResult;
use Crell\KernelBench\Events\Events\RoutingResult;
use Crell\KernelBench\Services\ActionInvoker;
use Crell\KernelBench\Services\Router\RouteMethodNotAllowed;
use Crell\KernelBench\Services\Router\RouteNotFound;
use Crell\KernelBench\Services\Router\Router;
use Crell\KernelBench\Services\Router\RouteResult;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class EventKernel implements RequestHandlerInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private Router $router,
        private ActionInvoker $invoker,
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

        // This is the routing.  It's kinda hard coded, but that's OK since it's important.
        $routingResult = $this->router->route($request);
        if ($routingResult instanceof RouteNotFound) {
            return $this->handleError(new NotFound($request, $routingResult), $request);
        }
        if ($routingResult instanceof RouteMethodNotAllowed) {
            return $this->handleError(new MethodNotAllowed($request, $routingResult->allowedMethods), $request);
        }
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
        $result = $this->invoker->invokeAction($request);

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
}
