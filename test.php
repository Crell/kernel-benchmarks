<?php

declare(strict_types=1);

use Crell\KernelBench\Benchmarks\KernelBench;
use Crell\KernelBench\Events\EventKernel;
use Crell\KernelBench\Events\OptimizedEventKernel;
use Crell\KernelBench\EventsException\ExceptionEventKernel;
use Crell\KernelBench\EventsException\OptimizedExceptionEventKernel;
use Crell\KernelBench\Monad\DynamicMonadicKernel;
use Crell\KernelBench\Monad\MonadicKernel;
use Crell\KernelBench\Psr15\StackMiddlewareKernel;

require_once './vendor/autoload.php';

class Bencher extends KernelBench {
    public function __construct(public readonly string $kernel)
    {
        $this->setupContainer();
        $this->setupListeners();
        $this->setupRequests();
    }

    public readonly \Psr\Container\ContainerInterface $container;

    private object $kernelInstance;

    public function getKernel(): object
    {
        return $this->kernelInstance ??= $this->container->get($this->kernel);
    }
}

$dynamicMonadic = new Bencher(DynamicMonadicKernel::class);
$monadic = new Bencher(MonadicKernel::class);
$event = new Bencher(EventKernel::class);
$exceptionEvent = new Bencher(ExceptionEventKernel::class);
$stack = new Bencher(StackMiddlewareKernel::class);
$optimizedEvent = new Bencher(OptimizedEventKernel::class);
$optimizedExceptionEvent = new Bencher(OptimizedExceptionEventKernel::class);

$benchers = [
//    $dynamicMonadic,
//    $monadic,
    $event,
//    $exceptionEvent,
//    $optimizedEvent,
//    $stack,
//    $optimizedExceptionEvent,
];

$methods = [
    'bench_staticroute',
    'bench_missing_route',
    'bench_bad_method',
    'bench_get_product',
    'bench_create_product_unauthorized',
    'bench_create_product_authenticated',
    'bench_staticroute_json',
    'bench_get_product_json',
    'bench_create_product_json_unauthorized',
    'bench_create_product_json_authenticated',
];

// Load the kernels to prewarm everything out of the container.
// That lets us skip the container cost when benchmarking.

/** @var KernelBench $b */
foreach ($benchers as $b) {
    $b->getKernel();
}

\spx_profiler_start();
foreach ($benchers as $bencher) {
    foreach ($methods as $method) {
        print "==========$bencher->kernel: $method\n";
        $bencher->$method();
    }
}
\spx_profiler_stop();
