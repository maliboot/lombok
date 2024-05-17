<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\HttpMessageAnnotationInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class HttpMessage extends AbstractAnnotation implements HttpMessageAnnotationInterface
{
    /**
     * 类属性值自动注入，来源于<a href='psi_element://\Psr\Http\Message\ServerRequestInterface::getAttributes'>ServerRequestInterface::getAttributes</a>的某值
     * @param string $delegate 必填<br/>1、attributeKeyName属性名称<br/>2、或者委托类名称,需要实现<a href='psi_element://\MaliBoot\Lombok\Contract\HttpMessageDelegateInterface'>HttpMessageDelegateInterface</a><hr>
     * @param null|int $type 数据类型, 可选:<br/>1、<a href='psi_element://\MaliBoot\Lombok\Contract\HttpMessageAnnotationInterface::ATTRIBUTE'>HttpMessageAnnotationInterface::ATTRIBUTE</a>(默认)<br/>2、<a href='psi_element://\MaliBoot\Lombok\Contract\HttpMessageAnnotationInterface::COOKIE'>HttpMessageAnnotationInterface::COOKIE</a><br/>3、<a href='psi_element://\MaliBoot\Lombok\Contract\HttpMessageAnnotationInterface::HEADER'>HttpMessageAnnotationInterface::HEADER</a>
     */
    public function __construct(
        public string $delegate,
        public ?int $type = null,
    ) {}

    public function delegate(): string
    {
        return $this->delegate;
    }

    public function type(): int
    {
        return $this->type ?? HttpMessageAnnotationInterface::ATTRIBUTE;
    }
}
