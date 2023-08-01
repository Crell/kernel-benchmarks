<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Listeners\Exceptions;

use Crell\ApiProblem\ApiProblem;
use Crell\ApiProblem\HttpConverter;
use Crell\KernelBench\EventsException\Exceptions\NotFound;
use Crell\KernelBench\EventsException\Events\HandleException;
use Crell\KernelBench\Services\Router\RequestFormat;

readonly class NotFoundJson
{
    public function __construct(
        private HttpConverter $converter,
    ) {}

    public function __invoke(HandleException $event): void
    {
        if ($this->accepts($event)) {
            $problem = (new ApiProblem('Not Found'))
                ->setStatus(404);
            $event->setResponse($this->converter->toJsonResponse($problem));
        }
    }

    private function accepts(HandleException $event): bool
    {
        return $event->error instanceof NotFound
            && $event->request->getAttribute(RequestFormat::class)->accept === 'json';
    }
}
