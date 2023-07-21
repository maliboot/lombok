<?php

declare(strict_types=1);

namespace Maliboot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Maliboot\Lombok\contract\GetterAnnotationInterface;
use Maliboot\Lombok\contract\ToArrayAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class ToArray extends AbstractAnnotation implements ToArrayAnnotationInterface, GetterAnnotationInterface
{
}
