<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services\EventDispatcher;

use Crell\Tukio\OrderedListenerProvider;
use Psr\Container\ContainerInterface;

class Provider extends OrderedListenerProvider
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function addSelfCallingListener(string $class): string
    {
        $type = $this->getParameterType([$class, '__invoke']);
        return $this->addListenerService(
            service: $class,
            method: '__invoke',
            type: $type,
            id: $class
        );
    }
}
