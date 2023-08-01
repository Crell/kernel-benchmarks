<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Listeners\PreRouting;

use Crell\KernelBench\EventsException\Events\ExceptionPreRouting;
use Psr\Log\LoggerInterface;

readonly class LogRequest
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function __invoke(ExceptionPreRouting $event): void
    {
        $this->logger->info('Request received to {path}', [
            'path' => $event->request()->getUri()->getPath(),
        ]);
    }
}
