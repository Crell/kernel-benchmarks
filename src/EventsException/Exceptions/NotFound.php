<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Exceptions;

use Crell\KernelBench\Services\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

class NotFound extends \RuntimeException
{
    public readonly ServerRequestInterface $request;
    public readonly RouteResult $routeResult;

    /**
     * @param string[] $allowedMethods
     */
    public static function create(ServerRequestInterface $request, RouteResult $routeResult): self
    {
        $new = new self();
        $new->request = $request;
        $new->routeResult = $routeResult;

        return $new;
    }
}
