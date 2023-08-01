<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Events;

use Crell\KernelBench\Events\Events\CarriesResponse;
use Crell\KernelBench\Events\Events\ResponseCarrier;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Http\Message\ServerRequestInterface;

class ExceptionPreRouting implements StoppableEventInterface, CarriesResponse
{
    use ResponseCarrier;

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

    public function isPropagationStopped(): bool
    {
        return isset($this->response);
    }
}
