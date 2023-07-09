<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners\ProcessResult;

use Crell\KernelBench\Documents\Message;
use Crell\KernelBench\Events\Events\ProcessActionResult;
use Crell\KernelBench\Services\Router\RequestFormat;
use Crell\KernelBench\Services\Template;
use Crell\Tukio\ListenerBefore;

readonly class HtmlMessageResult
{
    public function __construct(
        private Template $template,
    ) {}

    #[ListenerBefore(HtmlStringResult::class)]
    public function __invoke(ProcessActionResult $event): void
    {
        if ($this->accepts($event)) {
            $event->result = $this->template->render('message');
        }
    }

    private function accepts(ProcessActionResult $event): bool
    {
        return $event->result instanceof Message
            && $event->request->getAttribute(RequestFormat::class)->accept === 'html';
    }
}
