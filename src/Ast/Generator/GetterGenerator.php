<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassFieldVisitor;
use MaliBoot\Lombok\Contract\GetterAnnotationInterface;
use MaliBoot\Lombok\Contract\GetterDelegateInterface;
use ReflectionAttribute;
use Reflector;

#[LombokGenerator]
class GetterGenerator extends AbstractClassFieldVisitor
{
    protected function getClassMemberName(): string
    {
        return 'get' . ucfirst($this->reflectionProperty->getName());
    }

    protected function getAnnotationInterface(): string
    {
        return GetterAnnotationInterface::class;
    }

    protected function getClassCodeSnippet(): string
    {
        $code = <<<'CODE'
<?php
class Template {
    public function {{METHOD_NAME}}(): {{RETURN_TYPE}} {
        $result = $this->{{PROPERTY_NAME}};
        {{DELEGATE}}
        return $result;
    }
}
CODE;
        $fieldName = $this->reflectionProperty->getName();
        $type = $this->reflectionProperty->hasType() ? (string) $this->reflectionProperty->getType() : '';
        $delegates = [...$this->getGetterDelegateSet($this->reflectionClass, $fieldName, $type), ...$this->getGetterDelegateSet($this->reflectionProperty, $fieldName, $type)];

        return str_replace(
            ['{{METHOD_NAME}}', '{{RETURN_TYPE}}', '{{PROPERTY_NAME}}', '{{DELEGATE}}'],
            [
                $this->getClassMemberName(),
                $type,
                $fieldName,
                implode('', $delegates),
            ],
            $code,
        );
    }

    protected function getGetterDelegateSet(Reflector $reflector, string $key, string $type): array
    {
        $resultSet = [];
        foreach ($reflector->getAttributes($this->getAnnotationInterface(), ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            /** @var GetterAnnotationInterface|ReflectionAttribute $attribute */
            /** @var string $delegate */
            $delegate = $attribute->newInstance()->getterDelegate();
            if (is_subclass_of($delegate, GetterDelegateInterface::class)) {
                $resultSet[$delegate] = "\$result = \\{$delegate}::get('{$key}', \$result, '{$type}', \$this);";
            }
        }

        return $resultSet;
    }
}
