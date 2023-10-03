<?php

declare(strict_types=1);

namespace Crell\KernelBench\Benchmarks;

use Crell\KernelBench\EventsException\OptimizedExceptionEventKernel;

class OptimizedExceptionEventKernelBench extends KernelBench
{
    public function getKernel(): object
    {
        return $this->container->get(OptimizedExceptionEventKernel::class);
    }

}
