<?php

declare(strict_types=1);

namespace Maliboot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Maliboot\Lombok\contract\GetterAnnotationInterface;
use Maliboot\Lombok\contract\LoggerAnnotationInterface;
use Maliboot\Lombok\contract\SetterAnnotationInterface;
use Maliboot\Lombok\contract\ToArrayAnnotationInterface;
use Maliboot\Lombok\contract\ToCollectionAnnotationInterface;
use Maliboot\Lombok\Log\LoggerAnnotationTrait;

#[Attribute(Attribute::TARGET_CLASS)]
class Lombok extends AbstractAnnotation implements GetterAnnotationInterface, SetterAnnotationInterface, LoggerAnnotationInterface, ToArrayAnnotationInterface, ToCollectionAnnotationInterface
{
    use LoggerAnnotationTrait;
}
