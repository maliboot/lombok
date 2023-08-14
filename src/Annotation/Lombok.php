<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\GetterAnnotationInterface;
use MaliBoot\Lombok\Contract\LoggerAnnotationInterface;
use MaliBoot\Lombok\Contract\SetterAnnotationInterface;
use MaliBoot\Lombok\Contract\ToArrayAnnotationInterface;
use MaliBoot\Lombok\Contract\ToCollectionAnnotationInterface;
use MaliBoot\Lombok\Log\LoggerAnnotationTrait;

#[Attribute(Attribute::TARGET_CLASS)]
class Lombok extends AbstractAnnotation implements GetterAnnotationInterface, SetterAnnotationInterface, LoggerAnnotationInterface, ToArrayAnnotationInterface, ToCollectionAnnotationInterface
{
    use LoggerAnnotationTrait;
}
