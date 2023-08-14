<?php

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class LombokGenerator extends AbstractAnnotation
{
}