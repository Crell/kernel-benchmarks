<?php

declare(strict_types=1);

namespace Crell\KernelBench\Benchmarks;

use Crell\KernelBench\EventsUnified\EventUnifiedKernel;

class UnifiedEventKernelBench extends KernelBench
{
    public function getKernel(): object
    {
        return $this->container->get(EventUnifiedKernel::class);
    }
}
