<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services\Routing;

use Crell\KernelBench\Services\Actions\ProductCreate;
use Crell\KernelBench\Services\Actions\ProductGet;
use Crell\KernelBench\Services\Actions\StaticPath;
use Psr\Http\Message\RequestInterface;

class Router
{
    private array $routes = [];

    public function __construct()
    {
        $this->routes = [
            '/static/path' => [
                'get' => new RouteSuccess(
                    action: StaticPath::class,
                    method: 'GET',
                    parameters: [],
                ),
            ],
            '/product/1' => [
                'get' => new RouteSuccess(
                    action: ProductGet::class,
                    method: 'GET',
                    parameters: ['product' => 1],
                ),
            ],
            '/product' => [
                'post' => new RouteSuccess(
                    action: ProductCreate::class,
                    method: 'POST',
                    parameters: [],
                ),
            ],
        ];
    }

    public function route(RequestInterface $request): RouteResult
    {
        $routeSet = $this->routes[$request->getUri()->getPath()] ?? null;

        if (is_null($routeSet)) {
            return new RouteNotFound();
        }

        $result = $routeSet[strtolower($request->getMethod())] ?? null;

        if (is_null($result)) {
            return new RouteMethodNotAllowed(['GET', 'POST']);
        }

        return $result;
    }
}
