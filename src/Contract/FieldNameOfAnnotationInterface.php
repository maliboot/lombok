<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Contract;

interface FieldNameOfAnnotationInterface extends ClassReflectionAnnotationInterface
{
    public function getOfFieldName(): ?string;

    public function isOfInner(): bool;
}
