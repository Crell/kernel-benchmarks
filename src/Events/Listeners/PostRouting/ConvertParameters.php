<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners\PostRouting;

use Crell\KernelBench\Events\Events\PostRouting;
use Crell\KernelBench\Services\ParamConverter;
use Crell\KernelBench\Services\Routing\RouteResult;
use Crell\KernelBench\Services\Routing\RouteSuccess;

readonly class ConvertParameters
{
    public function __construct(
        private ParamConverter $converter,
    ) {}

    public function __invoke(PostRouting $event): void
    {
        $request = $event->request();

        /** @var RouteSuccess $result */
        $result = $request->getAttribute(RouteResult::class);

        $newVars = $this->converter->convert($result->vars, $result->parameters);

        if ($newVars) {
            $event->setRequest(
                $request->withAttribute(RouteResult::class, $result->withAddedVars($newVars))
            );
        }
    }
}
