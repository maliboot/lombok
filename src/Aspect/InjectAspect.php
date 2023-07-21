<?php

namespace Maliboot\Lombok\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Maliboot\Lombok\Annotation\Getter;
use Maliboot\Lombok\Annotation\Logger;
use Maliboot\Lombok\Annotation\Lombok;
use Maliboot\Lombok\Annotation\Setter;
use Maliboot\Lombok\Annotation\SetterGetter;
use Maliboot\Lombok\Annotation\ToArray;
use Maliboot\Lombok\Annotation\ToCollection;

#[Aspect]
class InjectAspect extends AbstractAspect
{
    public array $annotations = [
        Setter::class,
        Getter::class,
        SetterGetter::class,
        Logger::class,
        ToArray::class,
        ToCollection::class,
        Lombok::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // Do nothing, just to mark the class should be generated to the proxy classes.
        return $proceedingJoinPoint->process();
    }
}