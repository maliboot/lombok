<?php

namespace Maliboot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Maliboot\Lombok\contract\SetterAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Setter extends AbstractAnnotation implements SetterAnnotationInterface
{
}