<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services;

class Template
{
    public function render(string $template, array $params): string
    {
        return "formatted html: $template";
    }
}
