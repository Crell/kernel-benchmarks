<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsException\Listeners\Exceptions;

use Crell\KernelBench\EventsException\Exceptions\PermissionDenied;
use Crell\KernelBench\EventsException\Events\HandleException;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Router\RequestFormat;
use Crell\KernelBench\Services\Template;

readonly class PermissionDeniedHtml
{
    public function __construct(
        private Template $template,
        private ResponseBuilder $responseBuilder,
    ) {}

    public function __invoke(HandleException $event): void
    {
        if ($this->accepts($event)) {
            $body = $this->template->render('permission_denied');
            $event->setResponse($this->responseBuilder->createResponse(403, $body, 'text/html'));
        }
    }

    private function accepts(HandleException $event): bool
    {
        return $event->error instanceof PermissionDenied
            && $event->request->getAttribute(RequestFormat::class)->accept === 'html';
    }
}
