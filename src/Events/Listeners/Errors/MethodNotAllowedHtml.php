<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners\Errors;

use Crell\KernelBench\Errors\MethodNotAllowed;
use Crell\KernelBench\Events\Events\HandleError;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Routing\RequestFormat;
use Crell\KernelBench\Services\Template;

readonly class MethodNotAllowedHtml
{
    public function __construct(
        private Template $template,
        private ResponseBuilder $responseBuilder,
    ) {}

    public function __invoke(HandleError $event): void
    {
        if ($this->accepts($event)) {
            $body = $this->template->render('method_not_allowed');
            $event->setResponse($this->responseBuilder->createResponse(405, $body, 'text/html'));
        }
    }

    private function accepts(HandleError $event): bool
    {
        return $event->error instanceof MethodNotAllowed
            && $event->request->getAttribute(RequestFormat::class)->accept === 'html';
    }
}
