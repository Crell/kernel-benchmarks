<?php

declare(strict_types=1);

namespace Crell\KernelBench\Documents;

class Product
{
    public function __construct(
        public int $id,
        public string $name,
        public string $color,
        public float $price,
    ) {}
}
