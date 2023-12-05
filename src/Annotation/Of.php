<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use JetBrains\PhpStorm\ExpectedValues;
use MaliBoot\Lombok\Contract\OfAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Of implements OfAnnotationInterface
{
    /**
     * @param null|string $name 类属性注解，lombok::of(array $data)/ofData(array $data)时，会取 $data[$name] 来赋值
     * @param null|string $arrayKey 类属性注解，array类型时生效，指定key类型，如 int、string、float、Foo::class...
     * @param null|string $arrayValue 类属性注解，array类型时生效，指定value类型，如 int、string、bool、float、Foo::class...
     */
    public function __construct(
        public ?string $name = null,
        public ?string $arrayKey = null,
        public ?string $arrayValue = null,
    ) {}
}
