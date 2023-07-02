<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Events;

use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Http\Message\RequestInterface;

class ProcessActionResult implements StoppableEventInterface, CarriesResponse, CarriesError
{
    use ResponseCarrier;
    use ErrorCarrier;

    public function __construct(
        public readonly mixed $result,
        public readonly RequestInterface $request,
    ) {}

    public function isPropagationStopped(): bool
    {
        return isset($this->response) || isset($this->error);
    }
}
