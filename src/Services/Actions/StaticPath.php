<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services\Actions;

use Crell\KernelBench\Documents\Message;

readonly class StaticPath
{
    public function __invoke(): mixed
    {
        return new Message('Test message');
    }
}
