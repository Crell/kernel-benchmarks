<?php

declare(strict_types=1);

namespace Crell\KernelBench\Benchmarks;

use Crell\KernelBench\Events\EventKernel;
use Crell\KernelBench\Monad\MonadicKernel;
use Crell\KernelBench\Services\ClassFinder;
use Crell\KernelBench\Services\EventDispatcher\Provider;
use Crell\Tukio\Dispatcher;
use DI\ContainerBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PhpBench\Benchmark\Metadata\Annotations\AfterMethods;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\OutputTimeUnit;
use PhpBench\Benchmark\Metadata\Annotations\RetryThreshold;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function DI\autowire;
use function DI\get;

/**
 * @Revs(100)
 * @Iterations(10)
 * @Warmup(2)
 * @BeforeMethods({"setupContainer", "setupListeners", "setupRequests"})
 * @AfterMethods({"tearDown"})
 * @OutputTimeUnit("milliseconds", precision=4)
 * @RetryThreshold(10.0)
 */
class KernelBench
{
    private readonly MonadicKernel $monadicKernel;

    private readonly ContainerInterface $container;

    private ServerRequestInterface $staticRouteRequest;
    private ServerRequestInterface $productGetRequest;
    private ServerRequestInterface $productCreateRequest;
    private ServerRequestInterface $staticRouteRequestJson;
    private ServerRequestInterface $productGetRequestJson;
    private ServerRequestInterface $productCreateRequestJson;

    public function setupRequests(): void
    {
        $this->staticRouteRequest = new ServerRequest('GET', '/static/path', ['accept' => 'text/html']);
        $this->productGetRequest = new ServerRequest('GET', '/product/1', ['accept' => 'text/html']);
        $this->productCreateRequest = (new ServerRequest(
            'POST',
            '/product',
            ['accept' => 'text/html', 'content-type' => 'application/json']
        ))->withBody($this->container->get(StreamFactoryInterface::class)->createStream('{"name":"Beep","color": "Blue","price": 9.99}'));

        $this->staticRouteRequestJson = new ServerRequest('GET', '/static/path', ['accept' => 'application/json']);
        $this->productGetRequestJson = new ServerRequest('GET', '/product/1', ['accept' => 'application/json']);
        $this->productCreateRequestJson = (new ServerRequest(
            'POST',
            '/product',
            ['accept' => 'application/json', 'content-type' => 'application/json']
        ))->withBody($this->container->get(StreamFactoryInterface::class)->createStream('{"name":"Beep","color": "Blue","price": 9.99}'));
    }

    public function setupContainer(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);

        $finder = new ClassFinder();

        $containerBuilder->addDefinitions([
            EventKernel::class => autowire(),
            NullLogger::class => autowire(),
            Dispatcher::class => autowire(),
            Provider::class => autowire(),
            ListenerProviderInterface::class => get(Provider::class),
            EventDispatcherInterface::class => get(Dispatcher::class),
            LoggerInterface::class => get(NullLogger::class),
            ResponseFactoryInterface::class => get(Psr17Factory::class),
            StreamFactoryInterface::class => get(Psr17Factory::class),
            RequestFactoryInterface::class => get(Psr17Factory::class),
            ServerRequestFactoryInterface::class => get(Psr17Factory::class),
        ]);
        $paths = [
            './src/Services',
            './src/Events/Listeners',
            './src/Psr15',
        ];

        foreach ($paths as $path) {
            foreach ($finder->find($path) as $class) {
                $containerBuilder->addDefinitions([
                    $class => autowire(),
                ]);
            }
        }

        $this->container = $containerBuilder->build();
    }

    public function setupListeners(): void
    {
        /** @var Provider $provider */
        $provider = $this->container->get(Provider::class);

        $finder = new ClassFinder();

        foreach ($finder->find('./src/Events/Listeners') as $class) {
            $provider->addSelfCallingListener($class);
        }
    }

    public function setUpMonadicKernel(): void
    {
    }

    public function bench_event_staticroute(): void
    {
        /** @var EventKernel $kernel */
        $kernel = $this->container->get(EventKernel::class);

        $response = $kernel->handle($this->staticRouteRequest);
    }

    public function bench_event_get_product(): void
    {
        /** @var EventKernel $kernel */
        $kernel = $this->container->get(EventKernel::class);

        $response = $kernel->handle($this->productGetRequest);
    }

    public function bench_event_create_product(): void
    {
        /** @var EventKernel $kernel */
        $kernel = $this->container->get(EventKernel::class);

        $response = $kernel->handle($this->productCreateRequest);
    }

    public function bench_event_staticroute_json(): void
    {
        /** @var EventKernel $kernel */
        $kernel = $this->container->get(EventKernel::class);

        $response = $kernel->handle($this->staticRouteRequestJson);
    }

    public function bench_event_get_product_json(): void
    {
        /** @var EventKernel $kernel */
        $kernel = $this->container->get(EventKernel::class);

        $response = $kernel->handle($this->productGetRequestJson);
    }

    public function bench_event_create_product_json(): void
    {
        /** @var EventKernel $kernel */
        $kernel = $this->container->get(EventKernel::class);

        $response = $kernel->handle($this->productCreateRequestJson);
    }

    public function tearDown(): void {}
}
