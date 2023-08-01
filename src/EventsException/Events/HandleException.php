<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Events;

use Crell\KernelBench\Events\Events\CarriesResponse;
use Crell\KernelBench\Events\Events\ResponseCarrier;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Http\Message\ServerRequestInterface;

class HandleException implements StoppableEventInterface, CarriesResponse
{
    use ResponseCarrier;

    public function __construct(
        public readonly \Exception $error,
        public readonly ServerRequestInterface $request,
    ) {}

    public function isPropagationStopped(): bool
    {
        return isset($this->response);
    }
}
