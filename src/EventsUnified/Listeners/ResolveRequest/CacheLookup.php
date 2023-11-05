<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\ResolveRequest;

use Crell\KernelBench\Events\Events\PreRouting;
use Crell\KernelBench\EventsUnified\Events\ResolveRequest;
use Crell\KernelBench\Services\RequestCache;
use Crell\Tukio\ListenerBefore;
use Psr\Log\LoggerInterface;

readonly class CacheLookup
{
    public function __construct(
        private RequestCache $cache,
    ) {}

    #[ListenerBefore(Routing::class)]
    public function __invoke(ResolveRequest $event): void
    {
        if (in_array(strtoupper($event->request()->getMethod()), ['GET', 'HEAD'])) {
            $response = $this->cache->getResponseFor($event->request());
            if ($response) {
                $event->setResponse($response);
            }
        }
    }
}
