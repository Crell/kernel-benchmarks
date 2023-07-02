<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services\Routing;

readonly class RequestFormat
{
    public function __construct(
        public string $accept,
        public string $content,
    ) {}
}
