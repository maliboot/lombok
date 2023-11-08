<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\ToArrayAnnotationInterface;

#[LombokGenerator]
class ToArrayGenerator extends AbstractClassVisitor
{
    protected function getClassMemberName(): string
    {
        return 'toArray';
    }

    protected function getAnnotationInterface(): string
    {
        return ToArrayAnnotationInterface::class;
    }

    protected function getClassCodeSnippet(): string
    {
        return <<<'CODE'
<?php
class Template {
    public function toArray(): array 
    {
        $result = [];
        $classReflection = new \ReflectionClass($this);
        foreach ($classReflection->getProperties() as $property) {
            $propertyName = $property->getName();
            $filterNames = ['myDelegate'];
            if (in_array($propertyName, $filterNames)) {
                continue;
            }
            $methodName = 'get' . ucfirst($propertyName);
            if ($property->isInitialized($this) && $classReflection->hasMethod($methodName)) {
                $result[$property->getName()] = $this->{$methodName}();
            }
        }
        return $result;
    }
}
CODE;
    }
}
