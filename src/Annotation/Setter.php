<?php

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\SetterAnnotationInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Setter extends AbstractAnnotation implements SetterAnnotationInterface
{
    /**
     * @param ?string $delegate Setter委托类<div><p>默认为null，无委托</p><p>委托类需要实现<a href='psi_element://\MaliBoot\Lombok\Contract\SetterDelegateInterface'>SetterDelegateInterface</a></p></div>
     */
    public function __construct(public ?string $delegate = null)
    {
        parent::__construct($delegate);
    }

    public function setterDelegate(): ?string
    {
        return $this->delegate;
    }
}