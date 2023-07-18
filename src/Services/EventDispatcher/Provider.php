<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services\EventDispatcher;

use Crell\Tukio\Entry\ListenerEntry;
use Crell\Tukio\ListenerAfter;
use Crell\Tukio\ListenerBefore;
use Crell\Tukio\ListenerPriority;
use Crell\Tukio\OrderedListenerProvider;
use Psr\Container\ContainerInterface;

class Provider extends OrderedListenerProvider
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * @todo All of this is a sign that Tukio needs to be cleaned up a bit.
     */
    public function addSelfCallingListener(string $class): string
    {
        $type = $this->getParameterType([$class, '__invoke']);
        $rClass = new \ReflectionClass($class);
        /** @var \ReflectionParameter $rParam */
        $rMethod = $rClass->getMethod('__invoke');
        if ($rMethod) {
            $attributes = $this->findAttributesOnMethod($rMethod);
            foreach ($attributes as $attrib) {
                $type ??= $attrib->type;
                if ($attrib instanceof ListenerBefore) {
                    return $this->addListenerServiceBefore($attrib->before, $class, '__invoke', $type, $class);
                } elseif ($attrib instanceof ListenerAfter) {
                    return $this->addListenerServiceAfter($attrib->after, $class, '__invoke', $type, $class);
                } elseif ($attrib instanceof ListenerPriority) {
                    return $this->addListenerService($class, '__invoke', $type, $attrib->priority, $class);
                } else {
                    return $this->addListenerService($class, '__invoke', $type, $class);
                }
            }
        }
        return $this->addListenerService(
            service: $class,
            method: '__invoke',
            type: $type,
            id: $class
        );
    }
}
