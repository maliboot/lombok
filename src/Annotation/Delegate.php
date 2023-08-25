<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\DelegateAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Delegate extends AbstractAnnotation implements DelegateAnnotationInterface
{
    /**
     * @param string $className 委托类的名称；注意：1、允许委托接口类、抽象类，但其内不能有抽象方法；2、只会委托本类不存在的常量、属性、方法
     */
    public function __construct(private string $className)
    {
    }

    public function getDelegateClassName(): string
    {
        return $this->className;
    }
}
