<?php

declare(strict_types=1);

namespace Crell\KernelBench\Psr15;

use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Routing\RouteMethodNotAllowed;
use Crell\KernelBench\Services\Routing\RouteNotFound;
use Crell\KernelBench\Services\Routing\Router;
use Crell\KernelBench\Services\Routing\RouteResult;
use Crell\KernelBench\Services\Routing\RouteSuccess;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * It's unclear how to handle different response formats.
 */
readonly class RoutingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Router $router,
        private ResponseBuilder $responseBuilder,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $this->router->route($request);

        if ($result instanceof RouteSuccess) {
            $request = $request->withAttribute(RouteResult::class, $result);
            return $handler->handle($request);
        }
        if ($result instanceof RouteNotFound) {
            $this->createNotFoundResponse();
        }
        if ($result instanceof RouteMethodNotAllowed) {
            $this->createMethodNotAllowedResponse();
        }
    }

    private function createNotFoundResponse(): ResponseInterface
    {

    }

    private function createMethodNotAllowedResponse(): ResponseInterface
    {

    }

}
