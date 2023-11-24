<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\ClassReflectionAnnotationInterface;

#[LombokGenerator]
class ClassReflectionGenerator extends AbstractClassVisitor
{
    protected function getClassMemberName(): string
    {
        return 'getMyReflectionProperties';
    }

    protected function getAnnotationInterface(): string
    {
        return ClassReflectionAnnotationInterface::class;
    }

    protected function getClassCodeSnippet(): string
    {
        $reflection = [];
        $reflectionPropertyCodeList = [];
        foreach ($this->reflectionClass->getProperties() as $property) {
            $fieldName = $property->getName();
            $fieldAttrs = array_reduce($property->getAttributes(), function ($carry, $item) {
                $carry['\\' . $item->getName()] = $item->getArguments();
                return $carry;
            }, []);
            $reflectionPropertyCodeList[$fieldName] = [
                'name' => $fieldName,
                'type' => $this->getPropertyType($property),
                'allowsNull' => boolval($property->getType()?->allowsNull()),
                'hasSetter' => $this->hasSetterMethod($property),
                'hasGetter' => $this->hasGetterMethod($property),
                'attributes' => $fieldAttrs,
                'ofMapName' => $fieldAttrs['\MaliBoot\Lombok\Annotation\Of']['name'] ?? null,
                'toArrayMapName' => $fieldAttrs['\MaliBoot\Lombok\Annotation\ToArray']['name'] ?? null,
            ];
        }
        $reflection['reflectionProperties'] = $reflectionPropertyCodeList;
        $code = <<<'CODE'
<?php
class Template {
    public static function getMyReflectionClass(): array
    {
        return {{MY_CODE}};
    }
}
CODE;
        return str_replace('{{MY_CODE}}', var_export($reflection, true), $code);
    }
}
