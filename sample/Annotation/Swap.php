<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Sample\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Sample\Contract\SwapAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Swap extends AbstractAnnotation implements SwapAnnotationInterface
{
}
