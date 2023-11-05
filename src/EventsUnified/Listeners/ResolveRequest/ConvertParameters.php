<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\ResolveRequest;

use Crell\KernelBench\Events\Events\PostRouting;
use Crell\KernelBench\EventsUnified\Events\ResolveRequest;
use Crell\KernelBench\Services\ParamConverter;
use Crell\KernelBench\Services\Router\RouteResult;
use Crell\KernelBench\Services\Router\RouteSuccess;
use Crell\Tukio\ListenerAfter;
use Crell\Tukio\ListenerBefore;
use Psr\Log\LoggerInterface;

readonly class ConvertParameters
{
    public function __construct(
        private ParamConverter $converter,
    ) {}

    #[ListenerAfter(Routing::class)]
    #[ListenerBefore(ExecuteAction::class)]
    public function __invoke(ResolveRequest $event): void
    {
        $request = $event->request();

        /** @var RouteSuccess $result */
        $result = $request->getAttribute(RouteResult::class);

        $newVars = $this->converter->convert($result->vars, $result->parameters);

        if ($newVars) {
            $event->setAttribute(RouteResult::class, $result->withAddedVars($newVars));
        }
    }
}
