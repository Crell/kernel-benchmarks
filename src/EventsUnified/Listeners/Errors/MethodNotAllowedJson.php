<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\Errors;

use Crell\ApiProblem\ApiProblem;
use Crell\ApiProblem\HttpConverter;
use Crell\KernelBench\Errors\MethodNotAllowed;
use Crell\KernelBench\EventsUnified\Events\HandleError;
use Crell\KernelBench\Services\Router\RequestFormat;

readonly class MethodNotAllowedJson
{
    public function __construct(
        private HttpConverter $converter,
    ) {}

    public function __invoke(HandleError $event): void
    {
        if ($this->accepts($event)) {
            $problem = (new ApiProblem('Method Not Allowed'))
                ->setStatus(405);
            $event->setResponse($this->converter->toJsonResponse($problem));
        }
    }

    private function accepts(HandleError $event): bool
    {
        return $event->error instanceof MethodNotAllowed
            && $event->request->getAttribute(RequestFormat::class)->accept === 'json';
    }
}
