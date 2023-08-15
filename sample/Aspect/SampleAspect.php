<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Sample\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use MaliBoot\Lombok\Sample\Annotation\Swap;

#[Aspect]
class SampleAspect extends AbstractAspect
{
    public array $annotations = [
        Swap::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // Do nothing, just to mark the class should be generated to the proxy classes.
        return $proceedingJoinPoint->process();
    }
}
