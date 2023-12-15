<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Contract;

interface FieldNameToArrayAnnotationInterface extends ClassReflectionAnnotationInterface
{
    public function getToArrayFieldName(): ?string;
}
