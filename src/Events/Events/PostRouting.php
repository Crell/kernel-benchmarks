<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Events;

use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostRouting implements StoppableEventInterface, CarriesResponse, CarriesError
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

    public function isPropagationStopped(): bool
    {
        return isset($this->response) || isset($this->error);
    }
}
