<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners\PreRouting;

use Crell\KernelBench\Events\Events\PreRouting;
use Crell\KernelBench\Services\FormatDeriver;
use Crell\KernelBench\Services\Router\RequestFormat;

readonly class DeriveRequestFormats
{
    public function __construct(
        private FormatDeriver $deriver,
    ) {}

    public function __invoke(PreRouting $event): void
    {
        $request = $event->request();

        $contentFormat = $this->deriver->mapType($request->getHeader('content-type')[0] ?? '');

        $acceptFormat = $this->deriver->mapType($request->getHeader('accept')[0] ?? '');

        // If there is no Accept header but there is a content type, assume the agent
        // wants the same type back.
        if ($acceptFormat === 'unknown' && $contentFormat !== 'unknown') {
            $acceptFormat = $contentFormat;
        }

        $request = $request->withAttribute(RequestFormat::class, new RequestFormat(
            accept: $acceptFormat,
            content: $contentFormat,
        ));

        $event->setRequest($request);
    }
}
