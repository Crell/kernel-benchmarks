<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Listeners\Exceptions;

use Crell\KernelBench\EventsException\Events\HandleException;
use Crell\KernelBench\EventsException\Exceptions\NotFound;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Router\RequestFormat;
use Crell\KernelBench\Services\Template;

readonly class NotFoundHtml
{
    public function __construct(
        private Template $template,
        private ResponseBuilder $responseBuilder,
    ) {}

    public function __invoke(HandleException $event): void
    {
        if ($this->accepts($event)) {
            $body = $this->template->render('page_not_found');
            $event->setResponse($this->responseBuilder->notFound($body));
        }
    }

    private function accepts(HandleException $event): bool
    {
        return ($event->error instanceof NotFound)
            && ($event->request->getAttribute(RequestFormat::class)->accept === 'html');
    }
}
