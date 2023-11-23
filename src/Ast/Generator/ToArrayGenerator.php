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
        foreach ($this->getMyReflectionProperties() as $propertyName => $propertyData) {
            $filterNames = ['myDelegate', 'logger'];
            if (in_array($propertyName, $filterNames)) {
                continue;
            }
            $methodName = 'get' . ucfirst($propertyName);
            if (isset($this->{$propertyName}) && $propertyData['hasGetter']) {
                $result[$propertyName] = $this->{$methodName}();
                if ($isRecursion && ($result[$propertyName] instanceof \Hyperf\Contract\Arrayable || $result[$propertyName] instanceof \MaliBoot\Utils\Contract\Arrayable)) {
                    $result[$propertyName] = $result[$propertyName]->toArray();
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
