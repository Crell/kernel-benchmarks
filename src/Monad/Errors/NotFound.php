<?php

declare(strict_types=1);

namespace Crell\KernelBench\Monad\Errors;

use Crell\KernelBench\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

readonly class NotFound implements Error
{
    public function __construct(
        public ServerRequestInterface $request,
        public RouteResult $routeResult,
    ) {}
}
