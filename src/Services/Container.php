<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private array $services = [];

    public function get(string $id): mixed
    {
        // @todo Something less silly.
        return $this->services[$id] ?? throw new class extends \Exception implements ContainerExceptionInterface {};
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    public function add(string $id, mixed $value): self
    {
        $this->services[$id] = $value;
        return $this;
    }
}
