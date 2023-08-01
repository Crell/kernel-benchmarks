<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Listeners\PreRouting;

use Crell\KernelBench\EventsException\Events\ExceptionPreRouting;
use Crell\KernelBench\Services\RequestCache;

readonly class CacheLookup
{
    public function __construct(
        private RequestCache $cache,
    ) {}

    public function __invoke(ExceptionPreRouting $event): void
    {
        $response = $this->cache->getResponseFor($event->request());
        if ($response) {
            $event->setResponse($response);
        }
    }
}
