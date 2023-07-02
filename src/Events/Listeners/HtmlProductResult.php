<?php

declare(strict_types=1);

namespace Crell\KernelBench\Events\Listeners;

use Crell\KernelBench\Documents\Product;
use Crell\KernelBench\Events\Events\ProcessActionResult;
use Crell\KernelBench\Services\Routing\RequestFormat;
use Crell\KernelBench\Services\Template;

readonly class HtmlProductResult
{
    public function __construct(
        private Template $template,
    ) {}

    public function __invoke(ProcessActionResult $event): void
    {
        if (!$this->accepts($event)) {
            $event->result = $this->template->render('product');
        }
    }

    private function accepts(ProcessActionResult $event): bool
    {
        return $event->result instanceof Product
            && $event->request->getAttribute(RequestFormat::class)->accept === 'html';
    }
}
