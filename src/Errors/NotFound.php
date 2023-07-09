<?php

declare(strict_types=1);

namespace Crell\KernelBench\Errors;

use Crell\KernelBench\Services\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

readonly class NotFound implements Error
{
    public function __construct(
        public ServerRequestInterface $request,
        public RouteResult $routeResult,
    ) {}
}
