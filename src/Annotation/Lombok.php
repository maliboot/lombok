<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\contract\GetterAnnotationInterface;
use MaliBoot\Lombok\contract\LoggerAnnotationInterface;
use MaliBoot\Lombok\contract\SetterAnnotationInterface;
use MaliBoot\Lombok\contract\ToArrayAnnotationInterface;
use MaliBoot\Lombok\contract\ToCollectionAnnotationInterface;
use MaliBoot\Lombok\Log\LoggerAnnotationTrait;

#[Attribute(Attribute::TARGET_CLASS)]
class Lombok extends AbstractAnnotation implements GetterAnnotationInterface, SetterAnnotationInterface, LoggerAnnotationInterface, ToArrayAnnotationInterface, ToCollectionAnnotationInterface
{
    use LoggerAnnotationTrait;
}
