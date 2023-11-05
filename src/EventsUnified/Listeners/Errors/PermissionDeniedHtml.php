<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\Errors;

use Crell\KernelBench\Errors\PermissionDenied;
use Crell\KernelBench\EventsUnified\Events\HandleError;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Router\RequestFormat;
use Crell\KernelBench\Services\Template;

readonly class PermissionDeniedHtml
{
    public function __construct(
        private Template $template,
        private ResponseBuilder $responseBuilder,
    ) {}

    public function __invoke(HandleError $event): void
    {
        if ($this->accepts($event)) {
            $body = $this->template->render('permission_denied');
            $event->setResponse($this->responseBuilder->createResponse(403, $body, 'text/html'));
        }
    }

    private function accepts(HandleError $event): bool
    {
        return $event->error instanceof PermissionDenied
            && $event->request->getAttribute(RequestFormat::class)->accept === 'html';
    }
}
