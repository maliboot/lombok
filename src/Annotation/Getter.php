<?php

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\GetterAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Getter extends AbstractAnnotation implements GetterAnnotationInterface
{
    /**
     * @param ?string $delegate Getter委托类<div><p>默认为null，无委托</p><p>委托类需要实现<a href='psi_element://\MaliBoot\Lombok\Contract\GetterDelegateInterface'>GetterDelegateInterface</a></p></div>
     */
    public function __construct(public ?string $delegate = null)
    {
        parent::__construct($delegate);
    }

    public function getterDelegate(): ?string
    {
        return $this->delegate;
    }
}