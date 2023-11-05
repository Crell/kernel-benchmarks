<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified;

use Crell\KernelBench\Errors\Error;
use Crell\KernelBench\Events\Events\HandleError;
use Crell\KernelBench\Events\Events\HandleResponse;
use Crell\KernelBench\Events\Events\ProcessActionResult;
use Crell\KernelBench\EventsUnified\Events\ResolveRequest;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class EventUnifiedKernel implements RequestHandlerInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ResolveRequest $event */
        $event = $this->dispatcher->dispatch(new ResolveRequest($request));
        if ($response = $event->getResponse()) {
            return $this->dispatcher->dispatch(new HandleResponse($response, $request))->getResponse();
        }

        return $this->handleError($event->getError(), $event->request());
    }

    private function handleError(Error $error, ServerRequestInterface $request): ResponseInterface
    {
        /** @var ProcessActionResult $event */
        $event = $this->dispatcher->dispatch(new HandleError($error, $request));
        return $event->getResponse();
    }
}
