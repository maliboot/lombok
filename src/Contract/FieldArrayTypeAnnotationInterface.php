<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Contract;

interface FieldArrayTypeAnnotationInterface extends ClassReflectionAnnotationInterface
{
    public function arrayKeyType(): ?string;

    public function arrayValueType(): ?string;
}
