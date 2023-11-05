<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\ProcessResult;

use Crell\KernelBench\EventsUnified\Events\ProcessActionResult;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Router\RequestFormat;
use Crell\Tukio\ListenerPriority;

/**
 * Generically handle any JSON object that hasn't already been customized.
 */
readonly class JsonResult
{
    public function __construct(
        private ResponseBuilder $responseBuilder,
    ) {}

    #[ListenerPriority(priority: -100)]
    public function __invoke(ProcessActionResult $event): void
    {
        if ($this->accepts($event)) {
            $response = $this->responseBuilder->ok(json_encode($event->result, JSON_THROW_ON_ERROR), 'application/json');
            $event->setResponse($response);
        }
    }

    private function accepts(ProcessActionResult $event): bool
    {
        return $event->request->getAttribute(RequestFormat::class)->accept === 'json';
    }
}
