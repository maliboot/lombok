<?php

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\GetterAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Getter extends AbstractAnnotation implements GetterAnnotationInterface
{
}