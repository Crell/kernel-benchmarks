<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Listeners\Exceptions;

use Crell\KernelBench\EventsException\Events\HandleException;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\Tukio\ListenerPriority;

readonly class UnhandledError
{
    public function __construct(
        private ResponseBuilder $responseBuilder,
    ) {}

    #[ListenerPriority(priority: -100)]
    public function __invoke(HandleException $event): void
    {
        // Always apply, because nothing else did.
        $body = sprintf('No error handler found for %s', $event->error::class);
        $event->setResponse($this->responseBuilder->createResponse(500, $body));
    }
}
