<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\FieldReflectionAnnotationInterface;

#[LombokGenerator]
class FieldReflectionGenerator extends AbstractClassVisitor
{
    protected function getClassMemberName(): string
    {
        return 'getMyReflectionProperties';
    }

    protected function getAnnotationInterface(): string
    {
        return FieldReflectionAnnotationInterface::class;
    }

    protected function getClassCodeSnippet(): string
    {
        $reflectionPropertyCodeList = [];
        foreach ($this->reflectionClass->getProperties() as $property) {
            $fieldName = $property->getName();
            $reflectionPropertyCodeList[$fieldName] = [
                'type' => $this->getPropertyType($property),
                'allowsNull' => boolval($property->getType()?->allowsNull()),
                'hasSetter' => $this->hasSetterMethod($property),
                'hasGetter' => $this->hasGetterMethod($property),
                'attributes' => array_reduce($property->getAttributes(), function ($carry, $item) {
                    $carry['\\' . $item->getName()] = $item->getArguments();
                    return $carry;
                }, []),
            ];
        }
        $code = <<<'CODE'
<?php
class Template {
    public function getMyReflectionProperties(): array
    {
        return {{MY_CODE}};
    }
}
CODE;
        return str_replace('{{MY_CODE}}', var_export($reflectionPropertyCodeList, true), $code);
    }
}
