<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners\Errors;

use Crell\ApiProblem\ApiProblem;
use Crell\ApiProblem\HttpConverter;
use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\Events\Events\HandleError;
use Crell\KernelBench\Services\Router\RequestFormat;

readonly class NotFoundJson
{
    public function __construct(
        private HttpConverter $converter,
    ) {}

    public function __invoke(HandleError $event): void
    {
        if ($this->accepts($event)) {
            $problem = (new ApiProblem('Not Found'))
                ->setStatus(404);
            $event->setResponse($this->converter->toJsonResponse($problem));
        }
    }

    private function accepts(HandleError $event): bool
    {
        return $event->error instanceof NotFound
            && $event->request->getAttribute(RequestFormat::class)->accept === 'json';
    }
}
