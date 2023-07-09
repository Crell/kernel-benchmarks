<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners\Response;

use Crell\KernelBench\Events\Events\HandleResponse;
use Crell\KernelBench\Services\RequestCache;

readonly class CacheRecord
{
    public function __construct(
        private RequestCache $cache,
    ) {}

    public function __invoke(HandleResponse $event): void
    {
        $this->cache->setResponseFor($event->request, $event->getResponse());
    }
}
