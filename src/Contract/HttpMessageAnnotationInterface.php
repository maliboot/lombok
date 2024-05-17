<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Contract;

interface HttpMessageAnnotationInterface
{
    public const ATTRIBUTE = 0;

    public const COOKIE = 1;

    public const HEADER = 2;

    /**
     * @return int 数据类型, 可选:<br/>1、<a href='psi_element://\MaliBoot\Lombok\Contract\HttpMessageAnnotationInterface::ATTRIBUTE'>HttpMessageAnnotationInterface::ATTRIBUTE</a>(默认)<br/>2、<a href='psi_element://\MaliBoot\Lombok\Contract\HttpMessageAnnotationInterface::COOKIE'>HttpMessageAnnotationInterface::COOKIE</a><br/>3、<a href='psi_element://\MaliBoot\Lombok\Contract\HttpMessageAnnotationInterface::HEADER'>HttpMessageAnnotationInterface::HEADER</a>
     */
    public function type(): int;

    /**
     * 类属性值自动注入，来源于<a href='psi_element://\Psr\Http\Message\ServerRequestInterface::getAttributes'>ServerRequestInterface::getAttributes</a>的某值
     * @return string attributeKeyName or 委托类名称, 如果为类则需要继承<a href='psi_element://\MaliBoot\Lombok\Contract\PsrHttpAttributeDelegateInterface'>PsrHttpAttributeDelegateInterface</a>
     */
    public function delegate(): string;
}
