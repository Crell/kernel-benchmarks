<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException;

use Crell\KernelBench\Events\Events\HandleResponse;
use Crell\KernelBench\Events\Events\PostRouting;
use Crell\KernelBench\Events\Events\PreRouting;
use Crell\KernelBench\Events\Events\ProcessActionResult;
use Crell\KernelBench\Events\Events\RoutingResult;
use Crell\KernelBench\EventsException\Events\ExceptionHandleResponse;
use Crell\KernelBench\EventsException\Events\ExceptionPostRouting;
use Crell\KernelBench\EventsException\Events\ExceptionPreRouting;
use Crell\KernelBench\EventsException\Events\ExceptionRoutingResult;
use Crell\KernelBench\EventsException\Events\HandleException;
use Crell\KernelBench\EventsException\Exceptions\MethodNotAllowed;
use Crell\KernelBench\EventsException\Exceptions\NoResultHandlerFound;
use Crell\KernelBench\EventsException\Exceptions\NotFound;
use Crell\KernelBench\Services\ActionInvoker;
use Crell\KernelBench\Services\Router\RouteMethodNotAllowed;
use Crell\KernelBench\Services\Router\RouteNotFound;
use Crell\KernelBench\Services\Router\Router;
use Crell\KernelBench\Services\Router\RouteResult;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class ExceptionEventKernel implements RequestHandlerInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private Router $router,
        private ActionInvoker $invoker,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            /** @var PreRouting $event */
            $event = $this->dispatcher->dispatch(new ExceptionPreRouting($request));
            if ($response = $event->getResponse()) {
                return $response;
            }
            $request = $event->request();

            // This is the routing.  It's kinda hard coded, but that's OK since it's important.
            $routingResult = $this->router->route($request);
            if ($routingResult instanceof RouteNotFound) {
                throw NotFound::create($request, $routingResult);
            }
            if ($routingResult instanceof RouteMethodNotAllowed) {
                throw MethodNotAllowed::create($request, $routingResult->allowedMethods);
            }
            $request = $request->withAttribute(RouteResult::class, $routingResult);

            /** @var RoutingResult $event */
            $event = $this->dispatcher->dispatch(new ExceptionRoutingResult($request, $routingResult));
            if ($response = $event->getResponse()) {
                return $response;
            }
            $request = $event->request();

            /** @var PostRouting $event */
            $event = $this->dispatcher->dispatch(new ExceptionPostRouting($request));
            if ($response = $event->getResponse()) {
                return $response;
            }
            $request = $event->request();

            // Call the action.
            $result = $this->invoker->invokeAction($request);

            if (! $result instanceof ResponseInterface) {
                /** @var ProcessActionResult $event */
                $event = $this->dispatcher->dispatch(new ProcessActionResult($result, $request));
                $response = $event->getResponse();
                if (!$response instanceof ResponseInterface) {
                    throw NoResultHandlerFound::create($request, $result);
                }
                $result = $response;
            }

            /** @var HandleResponse $event */
            $event = $this->dispatcher->dispatch(new ExceptionHandleResponse($result, $request));

            return $event->getResponse();
        } catch (\Exception $e) {
            return $this->handleException($e, $request);
        }
    }

    private function handleException(\Exception $ex, ServerRequestInterface $request): ResponseInterface
    {
        /** @var ProcessActionResult $event */
        $event = $this->dispatcher->dispatch(new HandleException($ex, $request));
        return $event->getResponse();
    }
}
