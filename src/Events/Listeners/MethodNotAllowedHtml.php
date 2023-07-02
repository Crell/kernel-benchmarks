<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners;

use Crell\KernelBench\Errors\MethodNotAllowed;
use Crell\KernelBench\Events\Events\ProcessActionResult;
use Crell\KernelBench\Services\ResponseBuilder;
use Crell\KernelBench\Services\Routing\RequestFormat;
use Crell\KernelBench\Services\Template;

readonly class MethodNotAllowedHtml
{
    public function __construct(
        private Template $template,
        private ResponseBuilder $responseBuilder,
    ) {}

    public function __invoke(ProcessActionResult $event): void
    {
        if (!$this->accepts($event)) {
            $body = $this->template->render('method_not_allowed');
            $event->setResponse($this->responseBuilder->createResponse(405, $body, 'text/html'));
        }
    }

    private function accepts(ProcessActionResult $event): bool
    {
        return $event->result instanceof MethodNotAllowed
            && $event->request->getAttribute(RequestFormat::class)->accept === 'html';
    }
}
