<?php

namespace MaliBoot\Lombok\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use MaliBoot\Lombok\Annotation\Getter;
use MaliBoot\Lombok\Annotation\Logger;
use MaliBoot\Lombok\Annotation\Lombok;
use MaliBoot\Lombok\Annotation\Setter;
use MaliBoot\Lombok\Annotation\GetterSetter;
use MaliBoot\Lombok\Annotation\ToArray;
use MaliBoot\Lombok\Annotation\ToCollection;

#[Aspect]
class InjectAspect extends AbstractAspect
{
    public array $annotations = [
        Setter::class,
        Getter::class,
        GetterSetter::class,
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