<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\GetterAnnotationInterface;
use MaliBoot\Lombok\Contract\SetterAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class GetterSetter extends AbstractAnnotation implements GetterAnnotationInterface, SetterAnnotationInterface
{
    /**
     * @param ?string $delegate GetterSetter委托类<div><p>默认为null，无委托</p><p>委托类需要实现<a href='psi_element://\MaliBoot\Lombok\Contract\GetterSetterDelegateInterface'>GetterSetterDelegateInterface</a></p></div>
     */
    public function __construct(public ?string $delegate = null)
    {
    }

    public function setterDelegate(): ?string
    {
        return $this->delegate;
    }

    public function getterDelegate(): ?string
    {
        return $this->delegate;
    }
}
