<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\ResolveRequest;

use Crell\KernelBench\Errors\MethodNotAllowed;
use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\EventsUnified\Events\ResolveRequest;
use Crell\KernelBench\Services\Router\RouteMethodNotAllowed;
use Crell\KernelBench\Services\Router\RouteNotFound;
use Crell\KernelBench\Services\Router\Router;
use Crell\KernelBench\Services\Router\RouteResult;
use Psr\Log\LoggerInterface;

readonly class Routing
{
    public function __construct(
        private Router $router,
    ) {}

    public function __invoke(ResolveRequest $event): void
    {
        $request = $event->request();
        $routingResult = $this->router->route($request);
        if ($routingResult instanceof RouteNotFound) {
            $event->setError(new NotFound($request, $routingResult));
        }
        if ($routingResult instanceof RouteMethodNotAllowed) {
            $event->setError(new MethodNotAllowed($request, $routingResult->allowedMethods));
        }
        $event->setAttribute(RouteResult::class, $routingResult);
    }
}
