<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\GetterAnnotationInterface;
use MaliBoot\Lombok\Contract\ToArrayAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class ToArray extends AbstractAnnotation implements ToArrayAnnotationInterface
{
}
