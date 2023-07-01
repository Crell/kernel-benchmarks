<?php

declare(strict_types=1);

namespace Crell\KernelBench\Documents;

class Product
{
    public readonly string $id;

    public function __construct(
        public string $name,
        public string $color,
        public float $price,
    ) {}
}
