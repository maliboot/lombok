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
    private function _toArray(bool $isRecursion = true): array 
    {
        $result = [];
        $classReflection = new \ReflectionClass($this);
        foreach ($classReflection->getProperties() as $property) {
            $propertyName = $property->getName();
            $filterNames = ['myDelegate', 'logger'];
            if (in_array($propertyName, $filterNames)) {
                continue;
            }
            $methodName = 'get' . ucfirst($propertyName);
            if ($property->isInitialized($this) && $classReflection->hasMethod($methodName)) {
                $result[$propertyName] = $this->{$methodName}();
                if ($result[$propertyName] instanceof \Hyperf\Contract\Arrayable || $result[$propertyName] instanceof \MaliBoot\Utils\Contract\Arrayable) {
                    $isRecursion && $result[$propertyName] = $this->toArray($result[$propertyName])
                }
            }
        }
        return $result;
    }

    public function all(): array 
    {
        return $this->_toArray(false);
    }

    public function toArray(): array 
    {
        return $this->_toArray();
    }
}
CODE;
    }

    protected function getImpls(): array
    {
        return [\Hyperf\Contract\Arrayable::class];
    }
}
