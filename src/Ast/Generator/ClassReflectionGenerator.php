<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\ClassReflectionAnnotationInterface;
use MaliBoot\Lombok\Contract\OfAnnotationInterface;
use MaliBoot\Lombok\Contract\ToArrayAnnotationInterface;
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
            $arrayHints = $this->getAttributeArgs($property, OfAnnotationInterface::class, ['arrayKey', 'arrayValue']);
            if ($arrayHints['arrayValue']) {
                ctype_upper($arrayHints['arrayValue'][0]) && $arrayHints['arrayValue'] = '\\' . $arrayHints['arrayValue'];
            }

            $reflectionPropertyCodeList[$fieldName] = [
                'name' => $fieldName,
                'type' => $this->getPropertyType($property),
                'allowsNull' => boolval($property->getType()?->allowsNull()),
                'hasSetter' => $this->hasSetterMethod($property),
                'hasGetter' => $this->hasGetterMethod($property),
                'attributes' => $fieldAttrs,
                'ofMapName' => $this->getAttributeArgVal($property, OfAnnotationInterface::class, 'name') ?? null,
                'toArrayMapName' => $this->getAttributeArgVal($property, ToArrayAnnotationInterface::class, 'name') ?? null,
                'arrayKey' => $arrayHints['arrayKey'],
                'arrayValue' => $arrayHints['arrayValue'],
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
