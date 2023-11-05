<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\ResolveRequest;

use Crell\KernelBench\Events\Events\PreRouting;
use Crell\KernelBench\EventsUnified\Events\ResolveRequest;
use Crell\KernelBench\Services\FormatDeriver;
use Crell\KernelBench\Services\Router\RequestFormat;
use Crell\Tukio\ListenerBefore;
use Psr\Log\LoggerInterface;

readonly class DeriveRequestFormats
{
    public function __construct(
        private FormatDeriver $deriver,
    ) {}

    #[ListenerBefore(Routing::class)]
    public function __invoke(ResolveRequest $event): void
    {
        $request = $event->request();

        $contentFormat = $this->deriver->mapType($request->getHeader('content-type')[0] ?? '');

        $acceptFormat = $this->deriver->mapType($request->getHeader('accept')[0] ?? '');

        // If there is no Accept header but there is a content type, assume the agent
        // wants the same type back.
        if ($acceptFormat === 'unknown' && $contentFormat !== 'unknown') {
            $acceptFormat = $contentFormat;
        }

        $event->setAttribute(RequestFormat::class, new RequestFormat(
            accept: $acceptFormat,
            content: $contentFormat,
        ));
    }
}
