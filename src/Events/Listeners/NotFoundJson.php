<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners;

use Crell\ApiProblem\ApiProblem;
use Crell\ApiProblem\HttpConverter;
use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\Events\Events\ProcessActionResult;
use Crell\KernelBench\Services\Routing\RequestFormat;

readonly class NotFoundJson
{
    public function __construct(
        private HttpConverter $converter,
    ) {}

    public function __invoke(ProcessActionResult $event): void
    {
        if (!$this->accepts($event)) {
            $problem = (new ApiProblem('Not Found'))
                ->setStatus(404);
            $event->setResponse($this->converter->toJsonResponse($problem));
        }
    }

    private function accepts(ProcessActionResult $event): bool
    {
        return $event->result instanceof NotFound
            && $event->request->getAttribute(RequestFormat::class)->accept === 'json';
    }
}
