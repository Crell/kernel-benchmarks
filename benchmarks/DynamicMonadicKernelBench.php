<?php

declare(strict_types=1);

namespace Crell\KernelBench\Benchmarks;

use Crell\KernelBench\Monad\DynamicMonadicKernel;

class DynamicMonadicKernelBench extends KernelBench
{
    public function getKernel(): object
    {
        return $this->container->get(DynamicMonadicKernel::class);
    }

}
