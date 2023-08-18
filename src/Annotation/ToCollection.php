<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\ToCollectionAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class ToCollection extends AbstractAnnotation implements ToCollectionAnnotationInterface
{
    public function getterDelegate(): ?string
    {
        return null;
    }
}
