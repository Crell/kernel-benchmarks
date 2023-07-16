<?php

declare(strict_types=1);

namespace Crell\KernelBench\Benchmarks;

use Crell\KernelBench\Monad\MonadicKernel;

class MonadicKernelBench extends KernelBench
{
    public function getKernel(): object
    {
        return $this->container->get(MonadicKernel::class);
    }

}
