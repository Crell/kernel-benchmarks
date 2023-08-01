<?php

declare(strict_types=1);

namespace Crell\KernelBench\Services\EventDispatcher;

use Crell\Tukio\ListenerAfter;
use Crell\Tukio\ListenerAttribute;
use Crell\Tukio\ListenerBefore;
use Crell\Tukio\ListenerPriority;
use Crell\Tukio\ProviderBuilder;

class Builder extends ProviderBuilder
{
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
                    return $this->addListenerService($class, '__invoke', $type, id: $class);
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

    /**
     * @return array<ListenerAttribute>
     */
    protected function findAttributesOnMethod(\ReflectionMethod $rMethod): array
    {
        // This extra dance needed to keep the code working on PHP < 8.0. It can be removed once
        // 8.0 is made a requirement.
        $attributes = [];
        if (class_exists('ReflectionAttribute', false)) {
            $attributes = array_map(static fn (\ReflectionAttribute $attrib): object
            => $attrib->newInstance(), $rMethod->getAttributes(ListenerAttribute::class, \ReflectionAttribute::IS_INSTANCEOF));
        }

        return $attributes;
    }
}
