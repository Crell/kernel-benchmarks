<?php

declare(strict_types=1);

namespace Crell\KernelBench\Benchmarks;

use Crell\KernelBench\Errors\MethodNotAllowed;
use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\Errors\PermissionDenied;
use Crell\KernelBench\Events\EventKernel;
use Crell\KernelBench\Monad\DynamicMonadicKernel;
use Crell\KernelBench\Monad\Pipes\Error\HtmlForbiddenPipe;
use Crell\KernelBench\Monad\Pipes\Error\HtmlNotFoundPipe;
use Crell\KernelBench\Monad\Pipes\Error\JsonForbiddenPipe;
use Crell\KernelBench\Monad\Pipes\Error\JsonNotFoundPipe;
use Crell\KernelBench\Monad\Pipes\Error\MethodNotAllowedPipe;
use Crell\KernelBench\Monad\Pipes\HandleActionPipe;
use Crell\KernelBench\Monad\Pipes\Request\AuthenticateRequestPipe;
use Crell\KernelBench\Monad\Pipes\Request\AuthorizeRequestPipe;
use Crell\KernelBench\Monad\Pipes\Request\CacheLookupPipe;
use Crell\KernelBench\Monad\Pipes\Request\DeriveFormatPipe;
use Crell\KernelBench\Monad\Pipes\Request\LogRequestPipe;
use Crell\KernelBench\Monad\Pipes\Request\ParameterConverterPipe;
use Crell\KernelBench\Monad\Pipes\Request\RoutePipe;
use Crell\KernelBench\Monad\Pipes\Response\CacheRecordPipe;
use Crell\KernelBench\Monad\Pipes\Result\HtmlResultPipe;
use Crell\KernelBench\Monad\Pipes\Result\JsonResultPipe;
use Crell\KernelBench\Psr15\ActionRunner;
use Crell\KernelBench\Psr15\Middleware\AuthenticationMiddleware;
use Crell\KernelBench\Psr15\Middleware\AuthorizationMiddleware;
use Crell\KernelBench\Psr15\Middleware\CacheMiddleware;
use Crell\KernelBench\Psr15\Middleware\DeriveFormatMiddleware;
use Crell\KernelBench\Psr15\Middleware\LogMiddleware;
use Crell\KernelBench\Psr15\Middleware\ParamConverterMiddleware;
use Crell\KernelBench\Psr15\Middleware\RoutingMiddleware;
use Crell\KernelBench\Psr15\StackMiddlewareKernel;
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
use Psr\Http\Message\ResponseInterface;
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
abstract class KernelBench
{
    protected readonly ContainerInterface $container;

    private ServerRequestInterface $missingRouteRequest;
    private ServerRequestInterface $badFormatRequest;
    private ServerRequestInterface $staticRouteRequest;
    private ServerRequestInterface $productGetRequest;
    private ServerRequestInterface $productCreateRequestUnauthorized;
    private ServerRequestInterface $productCreateRequestAuthenticated;
    private ServerRequestInterface $staticRouteRequestJson;
    private ServerRequestInterface $productGetRequestJson;
    private ServerRequestInterface $productCreateRequestJsonUnauthorized;
    private ServerRequestInterface $productCreateRequestJsonAuthenticated;

    public function setupRequests(): void
    {
        $this->missingRouteRequest = new ServerRequest('GET', '/does/not/exist', ['accept' => 'text/html']);
        $this->badFormatRequest = new ServerRequest('PUT', '/static/path', ['accept' => 'text/html']);

        $this->staticRouteRequest = new ServerRequest('GET', '/static/path', ['accept' => 'text/html']);
        $this->productGetRequest = new ServerRequest('GET', '/product/1', ['accept' => 'text/html']);
        $this->productCreateRequestUnauthorized = (new ServerRequest(
            'POST',
            '/product',
            ['accept' => 'text/html', 'content-type' => 'application/json']
        ))->withBody($this->container->get(StreamFactoryInterface::class)->createStream('{"name":"Beep","color": "Blue","price": 9.99}'));
        $this->productCreateRequestAuthenticated = $this->productCreateRequestUnauthorized->withHeader('auth', 'creator');

        $this->staticRouteRequestJson = $this->staticRouteRequest->withHeader('accept', 'application/json');
        $this->productGetRequestJson = $this->productGetRequest->withHeader('accept', 'application/json');
        $this->productCreateRequestJsonUnauthorized = $this->productCreateRequestUnauthorized->withHeader('accept', 'application/json');
        $this->productCreateRequestJsonAuthenticated = $this->productCreateRequestAuthenticated->withHeader('accept', 'application/json');
    }

    public function setupContainer(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);

        $finder = new ClassFinder();

        $paths = [
            './src/Services',
            './src/Events/Listeners',
            './src/Psr15',
            './src/Monad',
        ];

        foreach ($paths as $path) {
            foreach ($finder->find($path) as $class) {
                $containerBuilder->addDefinitions([
                    $class => autowire(),
                ]);
            }
        }

        // Manual definitions come second, so they overwrite anything auto-derived above.
        $containerBuilder->addDefinitions([
            StackMiddlewareKernel::class => autowire(StackMiddlewareKernel::class)
                ->constructor(baseHandler: get(ActionRunner::class))
                // These will run last to first, ie, the earlier listed ones are "more inner."
                // That makes interlacing request, response, and "both" middlewares tricky.
                ->method('addMiddleware', get(ParamConverterMiddleware::class))
                ->method('addMiddleware', get(AuthorizationMiddleware::class))
                ->method('addMiddleware', get(RoutingMiddleware::class))
                ->method('addMiddleware', get(DeriveFormatMiddleware::class))
                ->method('addMiddleware', get(AuthenticationMiddleware::class))
                ->method('addMiddleware', get(CacheMiddleware::class))
                ->method('addMiddleware', get(LogMiddleware::class))
            ,
            DynamicMonadicKernel::class => autowire(DynamicMonadicKernel::class)
                ->method('addRequestPipe', get(LogRequestPipe::class))
                ->method('addRequestPipe', get(CacheLookupPipe::class))
                ->method('addRequestPipe', get(AuthenticateRequestPipe::class))
                ->method('addRequestPipe', get(DeriveFormatPipe::class))
                ->method('addRequestPipe', get(RoutePipe::class))
                ->method('addRequestPipe', get(AuthorizeRequestPipe::class))
                ->method('addRequestPipe', get(ParameterConverterPipe::class))
                ->method('addResultPipe', 'json', get(JsonResultPipe::class))
                ->method('addResultPipe', 'html', get(HtmlResultPipe::class))
                ->method('addResponsePipe', get(CacheRecordPipe::class))
                ->method('addErrorPipe', MethodNotAllowed::class, get(MethodNotAllowedPipe::class))
                ->method('addErrorPipe', NotFound::class, get(JsonNotFoundPipe::class))
                ->method('addErrorPipe', NotFound::class, get(HtmlNotFoundPipe::class))
                ->method('addErrorPipe', PermissionDenied::class, get(JsonForbiddenPipe::class))
                ->method('addErrorPipe', PermissionDenied::class, get(HtmlForbiddenPipe::class))
                ->method('setActionPipe', get(HandleActionPipe::class))
            ,
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

        foreach ($finder->find('./src/EventsException/Listeners') as $class) {
            $provider->addSelfCallingListener($class);
        }
    }

    abstract public function getKernel(): object;

    public function bench_staticroute(): void
    {
        $response = $this->getKernel()->handle($this->staticRouteRequest);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Response was bad.');
        }
    }

    public function bench_missing_route(): void
    {
        /** @var ResponseInterface $response */
        $response = $this->getKernel()->handle($this->missingRouteRequest);
        if ($response->getStatusCode() !== 404) {
            throw new \Exception('Response was bad.');
        }
    }

//    public function bench_bad_format(): void
//    {
//        /** @var ResponseInterface $response */
//        $response = $this->getKernel()->handle($this->badFormatRequest);
//        if ($response->getStatusCode() !== 405) {
//            throw new \Exception('Response was bad.');
//        }
//    }

    public function bench_get_product(): void
    {
        /** @var ResponseInterface $response */
        $response = $this->getKernel()->handle($this->productGetRequest);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Response was bad.');
        }
    }

    public function bench_create_product_unauthorized(): void
    {
        $response = $this->getKernel()->handle($this->productCreateRequestUnauthorized);
        if ($response->getStatusCode() !== 403) {
            throw new \Exception('Response was bad.');
        }
    }

    public function bench_create_product_authenticated(): void
    {
        $response = $this->getKernel()->handle($this->productCreateRequestAuthenticated);
        if ($response->getStatusCode() !== 201) {
            throw new \Exception('Response was bad.');
        }
    }

    public function bench_staticroute_json(): void
    {
        $response = $this->getKernel()->handle($this->staticRouteRequestJson);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Response was bad.');
        }
    }

    public function bench_get_product_json(): void
    {
        $response = $this->getKernel()->handle($this->productGetRequestJson);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Response was bad.');
        }
    }

    public function bench_create_product_json_unauthorized(): void
    {
        $response = $this->getKernel()->handle($this->productCreateRequestJsonUnauthorized);
        if ($response->getStatusCode() !== 403) {
            throw new \Exception('Response was bad.');
        }
    }

    public function bench_create_product_json_authenticated(): void
    {
        $response = $this->getKernel()->handle($this->productCreateRequestJsonAuthenticated);
        if ($response->getStatusCode() !== 201) {
            throw new \Exception('Response was bad.');
        }
    }

    public function tearDown(): void {}
}
