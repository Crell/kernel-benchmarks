<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\ResolveRequest;

use Crell\KernelBench\Events\Events\PreRouting;
use Crell\KernelBench\EventsUnified\Events\ResolveRequest;
use Psr\Log\LoggerInterface;
use Crell\Tukio\ListenerBefore;

readonly class LogRequest
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    #[ListenerBefore(Routing::class)]
    public function __invoke(ResolveRequest $event): void
    {
        $this->logger->info('Request received to {path}', [
            'path' => $event->request()->getUri()->getPath(),
        ]);
    }
}
