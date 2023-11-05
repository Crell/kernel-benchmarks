<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Events;

use Crell\KernelBench\Events\Events\CarriesError;
use Crell\KernelBench\Events\Events\CarriesResponse;
use Crell\KernelBench\Events\Events\ErrorCarrier;
use Crell\KernelBench\Events\Events\ResponseCarrier;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Http\Message\ServerRequestInterface;

class ResolveRequest implements CarriesResponse, CarriesError, StoppableEventInterface
{
    use ResponseCarrier;
    use ErrorCarrier;

    // @todo Here's where we want asymmetric visibility.
    public function __construct(
        private ServerRequestInterface $request,
    ) {}

    public function request(): ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;
        return $this;
    }

    // @todo Maybe this should return something.
    public function setAttribute(string $key, mixed $value): void
    {
        $this->request = $this->request->withAttribute($key, $value);
    }

    public function isPropagationStopped(): bool
    {
        return isset($this->response) || isset($this->error);
    }

}
