<?php

declare(strict_types=1);

namespace Maliboot\Lombok;

use Hyperf\Collection\Collection;
use Maliboot\Lombok\contract\DelegateInterface;
use Maliboot\Lombok\Log\Log;
use Psr\Log\LoggerInterface;
use ReflectionClass;

class Delegate implements DelegateInterface
{
    public static function log(string $name = Log::CALL_CLASS_NAME, string $group = Log::CALL_LOG_CONFIG): LoggerInterface
    {
        return Log::get($name, $group);
    }

    public static function toCollection(object $class): Collection
    {
        return Collection::make(self::toArray($class));
    }

    public static function toArray(object $class): array
    {
        $result = [];
        $classReflection = new ReflectionClass($class);
        foreach ($classReflection->getProperties() as $property) {
            $methodName = 'get' . ucfirst($property->getName());
            if ($property->isInitialized($class) && $classReflection->hasMethod($methodName)) {
                $result[$property->getName()] = $class->{$methodName}();
            }
        }
        return $result;
    }
}
