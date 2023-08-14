<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassFieldVisitor;
use MaliBoot\Lombok\Contract\GetterAnnotationInterface;

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
        return $this->{{PROPERTY_NAME}};
    }
}
CODE;
        $fieldName = $this->reflectionProperty->getName();
        $type = $this->reflectionProperty->hasType() ? (string) $this->reflectionProperty->getType() : '';
        return str_replace(
            ['{{METHOD_NAME}}', '{{RETURN_TYPE}}', '{{PROPERTY_NAME}}'],
            [
                $this->getClassMemberName(),
                $type,
                $fieldName,
            ],
            $code,
        );
    }
}
