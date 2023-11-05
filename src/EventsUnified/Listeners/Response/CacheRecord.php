<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\Response;

use Crell\KernelBench\EventsUnified\Events\HandleResponse;
use Crell\KernelBench\Services\RequestCache;

readonly class CacheRecord
{
    public function __construct(
        private RequestCache $cache,
    ) {}

    public function __invoke(HandleResponse $event): void
    {
        if ($event->getResponse()->getStatusCode() === 200 && in_array(strtoupper($event->request->getMethod()), ['GET', 'HEAD'])) {
            $this->cache->setResponseFor($event->request, $event->getResponse());
        }
    }
}
