<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners;

use Crell\ApiProblem\ApiProblem;
use Crell\ApiProblem\HttpConverter;
use Crell\KernelBench\Errors\PermissionDenied;
use Crell\KernelBench\Events\Events\HandleError;
use Crell\KernelBench\Services\Routing\RequestFormat;

readonly class PermissionDeniedJson
{
    public function __construct(
        private HttpConverter $converter,
    ) {}

    public function __invoke(HandleError $event): void
    {
        if ($this->accepts($event)) {
            $problem = (new ApiProblem('Permission Denied'))
                ->setStatus(403);
            $event->setResponse($this->converter->toJsonResponse($problem));
        }
    }

    private function accepts(HandleError $event): bool
    {
        return $event->error instanceof PermissionDenied
            && $event->request->getAttribute(RequestFormat::class)->accept === 'json';
    }
}
