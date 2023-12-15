<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\ClassReflectionAnnotationInterface;
use MaliBoot\Lombok\Contract\FieldArrayTypeAnnotationInterface;
use MaliBoot\Lombok\Contract\FieldNameOfAnnotationInterface;
use MaliBoot\Lombok\Contract\FieldNameToArrayAnnotationInterface;
use ReflectionAttribute;
use Reflector;

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

    protected function getAttributeArgVal(Reflector $reflector, string $interfaceFQN, string $name): ?string
    {
        return $this->getAttributeArgs($reflector, $interfaceFQN, [$name])[$name];
    }

    protected function getAttributeArgs(Reflector $reflector, string $interfaceFQN, array $names): array
    {
        $refs = $reflector->getAttributes($interfaceFQN, ReflectionAttribute::IS_INSTANCEOF);
        if (empty($refs)) {
            return array_reduce($names, fn ($carry, $item) => [$item => null, ...$carry], []);
        }

        $args = (array) $refs[0]->newInstance();

        $result = [];
        foreach ($names as $name) {
            if (isset($args[$name])) {
                $result[$name] = $args[$name];
            } else {
                $result[$name] = null;
            }
        }

        return $result;
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
            $arrayHints = $this->getAttributeFnValues($property, FieldArrayTypeAnnotationInterface::class, ['arrayKeyType', 'arrayValueType']);
            if ($arrayHints['arrayValueType']) {
                ctype_upper($arrayHints['arrayValueType'][0]) && $arrayHints['arrayValueType'] = '\\' . $arrayHints['arrayValueType'];
            }

            $reflectionPropertyCodeList[$fieldName] = [
                'name' => $fieldName,
                'type' => $this->getPropertyType($property),
                'allowsNull' => boolval($property->getType()?->allowsNull()),
                'hasSetter' => $this->hasSetterMethod($property),
                'hasGetter' => $this->hasGetterMethod($property),
                'attributes' => $fieldAttrs,
                'ofMapName' => $this->getAttributeFnVal($property, FieldNameOfAnnotationInterface::class, 'getOfFieldName') ?? null,
                'toArrayMapName' => $this->getAttributeFnVal($property, FieldNameToArrayAnnotationInterface::class, 'getToArrayFieldName') ?? null,
                'arrayKey' => $arrayHints['arrayKeyType'],
                'arrayValue' => $arrayHints['arrayValueType'],
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
