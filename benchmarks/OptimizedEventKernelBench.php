<?php

declare(strict_types=1);

namespace Crell\KernelBench\Benchmarks;

use Crell\KernelBench\Events\OptimizedEventKernel;

class OptimizedEventKernelBench extends KernelBench
{
    public function getKernel(): object
    {
        return $this->container->get(OptimizedEventKernel::class);
    }

}
