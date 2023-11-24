<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\ClassReflectionAnnotationInterface;
use MaliBoot\Lombok\Contract\OfAnnotationInterface;
use MaliBoot\Lombok\Contract\ToArrayAnnotationInterface;
use Reflector;
use ReflectionAttribute;

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

    protected function getMapName(Reflector $reflector, string $interfaceFQN): ?string
    {
        $refs = $reflector->getAttributes($interfaceFQN, ReflectionAttribute::IS_INSTANCEOF);
        if (empty($refs)) {
            return null;
        }

        $args = $refs[0]->getArguments();
        if (isset($args[0])) {
            return $args[0];
        }
        if (isset($args['name'])) {
            return $args['name'];
        }
        return null;
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
                'ofMapName' => $this->getMapName($property, OfAnnotationInterface::class) ?? null,
                'toArrayMapName' => $this->getMapName($property, ToArrayAnnotationInterface::class) ?? null,
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
