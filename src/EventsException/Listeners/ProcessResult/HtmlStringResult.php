<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Listeners\ProcessResult;

use Crell\KernelBench\EventsException\Events\ExceptionProcessActionResult;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Router\RequestFormat;

readonly class HtmlStringResult
{
    public function __construct(
        private ResponseBuilder $responseBuilder,
    ) {}

    public function __invoke(ExceptionProcessActionResult $event): void
    {
        if ($this->accepts($event)) {
            $event->setResponse($this->responseBuilder->ok($event->result, 'text/html'));
        }
    }

    private function accepts(ExceptionProcessActionResult $event): bool
    {
        return is_string($event->result)
            && $event->request->getAttribute(RequestFormat::class)->accept === 'html';
    }
}
