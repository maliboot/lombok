<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use MaliBoot\Lombok\Contract\ToArrayAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class ToArray implements ToArrayAnnotationInterface
{
    /**
     * @param null|string $name 类属性注解专用，array $data lombok::toArray()时，会自动设置 $data[$name]
     */
    public function __construct(
        public ?string $name = null,
    ) {}
}
