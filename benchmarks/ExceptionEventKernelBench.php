<?php

declare(strict_types=1);

namespace Crell\KernelBench\Benchmarks;

use Crell\KernelBench\EventsException\ExceptionEventKernel;

class ExceptionEventKernelBench extends KernelBench
{
    public function getKernel(): object
    {
        return $this->container->get(ExceptionEventKernel::class);
    }

}
