<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\ResolveRequest;

use Crell\KernelBench\Errors\NoResultHandlerFound;
use Crell\KernelBench\Events\Events\ProcessActionResult;
use Crell\KernelBench\EventsUnified\Events\ResolveRequest;
use Crell\KernelBench\Services\ActionInvoker;
use Crell\Tukio\ListenerAfter;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

readonly class ExecuteAction
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private ActionInvoker $invoker,
    ) {}

    #[ListenerAfter(Routing::class)]
    public function __invoke(ResolveRequest $event)
    {
        $request = $event->request();

        // Call the action.
        $result = $this->invoker->invokeAction($request);

        if ($result instanceof ResponseInterface) {
            $event->setResponse($result);
        } else {
            /** @var ProcessActionResult $event */
            $actionEvent = $this->dispatcher->dispatch(new ProcessActionResult($result, $request));
            $response = $actionEvent->getResponse();
            if ($response instanceof ResponseInterface) {
                $event->setResponse($response);
            } else {
                $event->setError(new NoResultHandlerFound($request, $result));
            }
        }
    }
}
