<?php

declare(strict_types=1);

namespace Crell\KernelBench\EventsUnified\Listeners\ProcessResult;

use Crell\KernelBench\Documents\Product;
use Crell\KernelBench\EventsUnified\Events\ProcessActionResult;
use Crell\KernelBench\Services\Router\RequestFormat;
use Crell\KernelBench\Services\Template;
use Crell\Tukio\ListenerBefore;

readonly class HtmlProductResult
{
    public function __construct(
        private Template $template,
    ) {}

    #[ListenerBefore(HtmlStringResult::class)]
    public function __invoke(ProcessActionResult $event): void
    {
        if ($this->accepts($event)) {
            $event->result = $this->template->render('product');
        }
    }

    private function accepts(ProcessActionResult $event): bool
    {
        return $event->result instanceof Product
            && $event->request->getAttribute(RequestFormat::class)->accept === 'html';
    }
}
