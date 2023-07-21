<?php

declare(strict_types=1);

namespace Maliboot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Maliboot\Lombok\contract\GetterAnnotationInterface;
use Maliboot\Lombok\contract\ToCollectionAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class ToCollection extends AbstractAnnotation implements ToCollectionAnnotationInterface, GetterAnnotationInterface
{
}
