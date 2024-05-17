<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use MaliBoot\Lombok\Contract\FieldAnnotationInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Field implements FieldAnnotationInterface
{
    /**
     * @param null|string $name 类属性名称(or别名)，默认为类属性变量名称。在装箱/拆箱时生效，lombok::of(array $data)/ofData(array $data)时，会取 $data[$name] 来赋值
     * @param null|string $type 类属性类型
     * @param null|string $desc 类属性描述
     * @param null|string $example 使用示例
     * @param bool $inner 是否内部属性，否则不再参与::of初始化
     */
    public function __construct(
        public ?string $name = null,
        public ?string $type = null,
        public ?string $desc = null,
        public ?string $example = null,
        public bool $inner = false,
    ) {}

    public function getOfFieldName(): ?string
    {
        return $this->name;
    }

    public function isOfInner(): bool
    {
        return $this->inner;
    }
}
