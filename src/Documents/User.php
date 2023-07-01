<?php

declare(strict_types=1);

namespace Crell\KernelBench\Documents;

class User
{
    public readonly string $id;

    public function __construct(
        public string $username,
        public string $displayName,
        public array $permissions = [],
    ) {}
}
