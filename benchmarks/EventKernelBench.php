<?php

declare(strict_types=1);

namespace Crell\KernelBench\Benchmarks;

use Crell\KernelBench\Events\EventKernel;

class EventKernelBench extends KernelBench
{
    public function getKernel(): object
    {
        return $this->container->get(EventKernel::class);
    }

}
