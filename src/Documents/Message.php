<?php

declare(strict_types=1);

namespace Crell\KernelBench\Documents;

class Message
{
    public readonly string $id;

    public function __construct(
        public string $message = '',
        public int $size = 14,
    ) {}
}
