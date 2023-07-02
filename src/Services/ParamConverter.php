<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services;

use Crell\KernelBench\Documents\Product;

class ParamConverter
{
    public function convert(array $arguments, array $parameters): array
    {
        $newVars = [];
        foreach ($arguments as $name => $val) {
            // There may not be a type if the action simply doesn't want this route argument.
            $type = $parameters[$name] ?? null;
            if ($type && class_exists($type)) {
                // This is obviously hard-coded nonsense.
                $newVars[$name] = new Product(1, 'Widget', 'Green', 9.99);
            }
        }
        return $newVars;
    }
}
