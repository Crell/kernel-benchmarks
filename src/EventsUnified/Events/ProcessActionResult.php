<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Events;

use Crell\KernelBench\Events\Events\CarriesError;
use Crell\KernelBench\Events\Events\CarriesResponse;
use Crell\KernelBench\Events\Events\ErrorCarrier;
use Crell\KernelBench\Events\Events\ResponseCarrier;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProcessActionResult implements StoppableEventInterface, CarriesResponse, CarriesError
{
    use ResponseCarrier;
    use ErrorCarrier;

    public function __construct(
        public mixed $result,
        public readonly ServerRequestInterface $request,
    ) {}

    public function isPropagationStopped(): bool
    {
        return isset($this->response) || isset($this->error);
    }
}
