<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners;

use Crell\KernelBench\Events\Events\PreRouting;
use Crell\KernelBench\Services\RequestCache;

readonly class CacheLookup
{
    public function __construct(
        private RequestCache $cache,
    ) {}

    public function __invoke(PreRouting $event): void
    {
        $response = $this->cache->getResponseFor($event->request());
        if ($response) {
            $event->setResponse($response);
        }
    }
}
