<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners;

use Crell\KernelBench\Events\Events\PreRouting;
use Psr\Log\LoggerInterface;

readonly class LogRequest
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function __invoke(PreRouting $event): void
    {
        $this->logger->info('Request received to {path}', [
            'path' => $event->request()->getUri()->getPath(),
        ]);
    }
}
