<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners\Errors;

use Crell\KernelBench\Errors\NotFound;
use Crell\KernelBench\Events\Events\HandleError;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Routing\RequestFormat;
use Crell\KernelBench\Services\Template;

readonly class NotFoundHtml
{
    public function __construct(
        private Template $template,
        private ResponseBuilder $responseBuilder,
    ) {}

    public function __invoke(HandleError $event): void
    {
        if ($this->accepts($event)) {
            $body = $this->template->render('page_not_found');
            $event->setResponse($this->responseBuilder->notFound($body));
        }
    }

    private function accepts(HandleError $event): bool
    {
        return ($event->error instanceof NotFound)
            && ($event->request->getAttribute(RequestFormat::class)->accept === 'html');
    }
}
