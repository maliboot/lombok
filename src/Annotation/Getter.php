<?php

namespace Maliboot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Maliboot\Lombok\contract\GetterAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Getter extends AbstractAnnotation implements GetterAnnotationInterface
{
}