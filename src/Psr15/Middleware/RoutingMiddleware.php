<?php

declare(strict_types=1);

namespace Crell\KernelBench\Psr15\Middleware;

use Crell\KernelBench\Errors\MethodNotAllowed;
use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\Events\Events\HandleError;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Router\RouteMethodNotAllowed;
use Crell\KernelBench\Services\Router\RouteNotFound;
use Crell\KernelBench\Services\Router\Router;
use Crell\KernelBench\Services\Router\RouteResult;
use Crell\KernelBench\Services\Router\RouteSuccess;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class RoutingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Router $router,
        private ResponseBuilder $responseBuilder,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $this->router->route($request);

        if ($result instanceof RouteSuccess) {
            $request = $request->withAttribute(RouteResult::class, $result);
            return $handler->handle($request);
        }
        if ($result instanceof RouteNotFound) {
            /** @var HandleError $event */
            $event = $this->eventDispatcher->dispatch(new HandleError(new NotFound($request, $result), $request));
            return $event->getResponse() ?? $this->responseBuilder->notFound('Not Found', 'text/plain');
        }
        if ($result instanceof RouteMethodNotAllowed) {
            /** @var HandleError $event */
            $event = $this->eventDispatcher->dispatch(new HandleError(new MethodNotAllowed($request, $result->allowedMethods), $request));
            return $event->getResponse() ?? $this->responseBuilder->createResponse(405, 'Not Found', 'text/plain');
        }

        // It should be impossible to get here, for type reasons.
        return $this->responseBuilder->createResponse(500, 'How did that happen?', 'text/plain');
    }
}
