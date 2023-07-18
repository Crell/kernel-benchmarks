<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Pipes\Request;

use Crell\KernelBench\Errors\Error;
use Crell\KernelBench\Errors\MethodNotAllowed;
use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\Monad\Pipes\RequestPipe;
use Crell\KernelBench\Services\Router\RouteMethodNotAllowed;
use Crell\KernelBench\Services\Router\RouteNotFound;
use Crell\KernelBench\Services\Router\Router;
use Crell\KernelBench\Services\Router\RouteResult;
use Crell\KernelBench\Services\Router\RouteSuccess;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class RoutePipe implements RequestPipe
{
    public function __construct(
        private Router $router,
    ) {}

    public function __invoke(ServerRequestInterface $request): ServerRequestInterface|ResponseInterface|Error
    {
        $routingResult = $this->router->route($request);
        $request = $request->withAttribute(RouteResult::class, $routingResult);

        return match (true) {
            $routingResult instanceof RouteNotFound => new NotFound($request, $routingResult),
            $routingResult instanceof RouteMethodNotAllowed => new MethodNotAllowed($request, $routingResult->allowedMethods),
            $routingResult instanceof RouteSuccess => $request,
            default => throw new \LogicException('It should not be possible to get here.'),
        };
    }
}
