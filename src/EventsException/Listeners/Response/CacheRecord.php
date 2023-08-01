<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Listeners\Response;

use Crell\KernelBench\EventsException\Events\ExceptionHandleResponse;
use Crell\KernelBench\Services\RequestCache;

readonly class CacheRecord
{
    public function __construct(
        private RequestCache $cache,
    ) {}

    public function __invoke(ExceptionHandleResponse $event): void
    {
        if ($event->getResponse()->getStatusCode() === 200 && in_array(strtoupper($event->request->getMethod()), ['GET', 'HEAD'])) {
            $this->cache->setResponseFor($event->request, $event->getResponse());
        }
    }
}
