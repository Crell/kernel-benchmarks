<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners\PreRouting;

use Crell\KernelBench\Events\Events\PreRouting;
use Crell\KernelBench\Services\RequestCache;

readonly class CacheLookup
{
    public function __construct(
        private RequestCache $cache,
    ) {}

    public function __invoke(PreRouting $event): void
    {
        if (in_array(strtoupper($event->request()->getMethod()), ['GET', 'HEAD'])) {
            $response = $this->cache->getResponseFor($event->request());
            if ($response) {
                $event->setResponse($response);
            }
        }
    }
}
