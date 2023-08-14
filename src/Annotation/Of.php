<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\OfAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class Of extends AbstractAnnotation implements OfAnnotationInterface
{
}
