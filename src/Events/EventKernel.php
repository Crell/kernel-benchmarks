<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class EventKernel implements RequestHandlerInterface
{

    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: Implement handle() method.
    }
}
