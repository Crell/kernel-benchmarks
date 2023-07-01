<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services\Routing;

readonly class RouteSuccess extends RouteResult
{
    /**
     * @param array<string, mixed> $vars
     *   The placeholder arguments extracted from the route path.
     */
    public function __construct(
        public string $action,
        public string $method,
        public array $parameters,
        public array $vars = [],
    ) {}

    /**
     * @param array<string, mixed> $vars
     */
    public function withAddedVars(array $vars): self
    {
        return new self(
            action: $this->action,
            method: $this->method,
            parameters: $this->parameters,
            vars: $vars + $this->vars,
        );
    }
}
