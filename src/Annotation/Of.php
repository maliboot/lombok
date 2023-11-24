<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use MaliBoot\Lombok\Contract\OfAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Of implements OfAnnotationInterface
{
    /**
     * @param null|string $name 类属性注解专用，lombok::of(array $data)/ofData(array $data)时，会取 $data[$name] 来赋值
     */
    public function __construct(
        public ?string $name = null,
    ) {}
}
