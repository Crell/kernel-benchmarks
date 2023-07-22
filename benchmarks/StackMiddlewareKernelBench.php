<?php

declare(strict_types=1);

namespace Crell\KernelBench\Benchmarks;

use Crell\KernelBench\Psr15\StackMiddlewareKernel;

class StackMiddlewareKernelBench extends KernelBench
{
    public function getKernel(): object
    {
        return $this->container->get(StackMiddlewareKernel::class);
    }

}
