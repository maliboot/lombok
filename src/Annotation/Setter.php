<?php

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\SetterAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Setter extends AbstractAnnotation implements SetterAnnotationInterface
{
}