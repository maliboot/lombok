<?php

declare(strict_types=1);

namespace Maliboot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Maliboot\Lombok\contract\GetterAnnotationInterface;
use Maliboot\Lombok\contract\SetterAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class GetterSetter extends AbstractAnnotation implements GetterAnnotationInterface, SetterAnnotationInterface
{
}
