<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services\Routing;

readonly class RouteMethodNotAllowed extends RouteResult
{
    /**
     * @param string[] $allowedMethods
     */
    public function __construct(public array $allowedMethods)
    {
    }
}
