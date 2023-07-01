<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services\Actions;

use Crell\KernelBench\Documents\Product;

readonly class ProductGet
{
    public function __invoke(Product $product): Product
    {
        return $product;
    }
}
