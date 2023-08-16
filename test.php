<?php

declare(strict_types=1);

use Crell\KernelBench\Events\EventKernel;
use Crell\KernelBench\Events\OptimizedEventKernel;
use Crell\KernelBench\EventsException\ExceptionEventKernel;
use Crell\KernelBench\Monad\DynamicMonadicKernel;
use Crell\KernelBench\Monad\MonadicKernel;
use Crell\KernelBench\Psr15\StackMiddlewareKernel;

require_once './vendor/autoload.php';

class Bencher extends \Crell\KernelBench\Benchmarks\KernelBench {
    public function __construct(public readonly string $kernel)
    {
        $this->setupContainer();
        $this->setupListeners();
        $this->setupRequests();
    }

    public function getKernel(): object
    {
        return $this->container->get($this->kernel);
    }
}

$dynamicMonadic = new Bencher(DynamicMonadicKernel::class);
$monadic = new Bencher(MonadicKernel::class);
$event = new Bencher(EventKernel::class);
$exceptionEvent = new Bencher(ExceptionEventKernel::class);
$stack = new Bencher(StackMiddlewareKernel::class);
$optimizedEvent = new Bencher(OptimizedEventKernel::class);

$benchers = [
    $dynamicMonadic,
    $monadic,
    $event,
    $exceptionEvent,
    $optimizedEvent,
    $stack,
];

//$runner->bench_event_staticroute();
//$runner->bench_event_staticroute_json();
//$runner->bench_monad_staticroute_json();
//$dynamicMonadic->bench_get_product();
//$monadic->bench_get_product();
//$event->bench_create_product_unauthorized();
//$event->bench_bad_format();
//$dynamicMonadic->bench_bad_format();
//$monadic->bench_bad_format();
//$stack->bench_bad_format();
//$event->bench_create_product_authenticated();
//$stack->bench_get_product();

//$exceptionEvent->bench_missing_route();

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

foreach ($benchers as $bencher) {
    foreach ($methods as $method) {
        print "$bencher->kernel: $method\n";
        $bencher->$method();
    }
}
