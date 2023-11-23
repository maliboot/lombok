<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassFieldVisitor;
use MaliBoot\Lombok\Contract\SetterAnnotationInterface;
use MaliBoot\Lombok\Contract\SetterDelegateInterface;
use MaliBoot\Lombok\Contract\WeakSetterInterface;
use ReflectionAttribute;
use Reflector;

#[LombokGenerator]
class SetterGenerator extends AbstractClassFieldVisitor
{
    use DefaultValueTrait;

    protected function getClassMemberName(): string
    {
        return 'set' . ucfirst($this->reflectionProperty->getName());
    }

    protected function getAnnotationInterface(): string
    {
        return SetterAnnotationInterface::class;
    }

    protected function isWeakSetter(): bool
    {
        if ($this->reflectionClass->isSubclassOf(WeakSetterInterface::class)) {
            return true;
        }

        return ! empty($this->reflectionClass->getAttributes(WeakSetterInterface::class, ReflectionAttribute::IS_INSTANCEOF));
    }

    protected function getClassCodeSnippet(): string
    {
        $code = <<<'CODE'
<?php
class Template {
    public function {{METHOD_NAME}}({{RETURN_TYPE}} ${{PROPERTY_NAME}}{{PROPERTY_DEFAULT}}): self {
        $result = ${{PROPERTY_NAME}};
        {{DELEGATE}}
        $this->{{PROPERTY_NAME}} = $result;
        return $this;
    }
}
CODE;
        $this->isWeakSetter() && $code = <<<'CODE'
<?php
class Template {
    public function {{METHOD_NAME}}(${{PROPERTY_NAME}}{{PROPERTY_DEFAULT}}): self {
        $result = ${{PROPERTY_NAME}};
        {{DELEGATE}}
        $this->{{PROPERTY_NAME}} = {{TYPE_CONVERTOR}}$result;
        return $this;
    }
}
CODE;
        $fieldName = $this->reflectionProperty->getName();
        $default = $this->reflectionProperty->hasDefaultValue() ? ' = ' . $this->getValString($this->reflectionProperty->getDefaultValue()) : '';
        $type = $this->getPropertyType($this->reflectionProperty);
        $typeArr = explode('|', $type);
        $firstType = isset($typeArr[0]) ? ltrim($typeArr[0], '?') : null;
        $delegates = [...$this->getSetterDelegateSet($this->reflectionClass, $fieldName, $type), ...$this->getSetterDelegateSet($this->reflectionProperty, $fieldName, $type)];
        return str_replace(
            ['{{METHOD_NAME}}', '{{RETURN_TYPE}}', '{{TYPE_CONVERTOR}}', '{{PROPERTY_NAME}}', '{{PROPERTY_DEFAULT}}', '{{DELEGATE}}'],
            [
                $this->getClassMemberName(),
                $type,
                $firstType ? "($firstType)" : '',
                $fieldName,
                $default,
                implode("\n", $delegates),
            ],
            $code,
        );
    }

    protected function getSetterDelegateSet(Reflector $reflector, string $key, string $type): array
    {
        $resultSet = [];
        foreach ($reflector->getAttributes($this->getAnnotationInterface(), ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            /** @var ReflectionAttribute|SetterAnnotationInterface $attribute */
            /** @var string $delegate */
            $delegate = $attribute->newInstance()->setterDelegate();
            if (is_subclass_of($delegate, SetterDelegateInterface::class)) {
                $resultSet[$delegate] = "\$result = \\{$delegate}::set('{$key}', \$result, '{$type}', \$this);";
            }
        }

        return $resultSet;
    }
}
