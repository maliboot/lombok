<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use MaliBoot\Lombok\Contract\FieldArrayTypeAnnotationInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayType implements FieldArrayTypeAnnotationInterface
{
    /**
     * @param null|string $value 类属性为array时生效，指定value类型，如 int、string、bool、float、Foo::class...
     * @param null|string $key 类属性为array时生效，指定key类型，默认为int（索引数组），如 int、string、float、Foo::class...
     */
    public function __construct(
        public ?string $value = null,
        public ?string $key = 'int',
    ) {}

    public function arrayKeyType(): ?string
    {
        return $this->key;
    }

    public function arrayValueType(): ?string
    {
        return $this->value;
    }
}
