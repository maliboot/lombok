<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassFieldVisitor;
use MaliBoot\Lombok\Contract\SetterAnnotationInterface;

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

    protected function getClassCodeSnippet(): string
    {
        $code = <<<'CODE'
<?php
class Template {
    public function {{METHOD_NAME}}({{RETURN_TYPE}} ${{PROPERTY_NAME}}{{PROPERTY_DEFAULT}}): self {
        $this->{{PROPERTY_NAME}} = ${{PROPERTY_NAME}};
        return $this;
    }
}
CODE;
        $fieldName = $this->reflectionProperty->getName();
        $default = $this->reflectionProperty->hasDefaultValue() ? ' = ' . $this->getValString($this->reflectionProperty->getDefaultValue()) : '';
        $type = $this->reflectionProperty->hasType() ? (string) $this->reflectionProperty->getType() : '';
        return str_replace(
            ['{{METHOD_NAME}}', '{{RETURN_TYPE}}', '{{PROPERTY_NAME}}', '{{PROPERTY_DEFAULT}}'],
            [
                $this->getClassMemberName(),
                $type,
                $fieldName,
                $default,
            ],
            $code,
        );
    }
}
