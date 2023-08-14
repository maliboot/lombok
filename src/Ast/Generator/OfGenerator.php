<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\OfAnnotationInterface;

#[LombokGenerator]
class OfGenerator extends AbstractClassVisitor
{

    protected function getClassMemberName(): string
    {
        return 'of';
    }

    protected function getAnnotationInterface(): string
    {
        return OfAnnotationInterface::class;
    }

    protected function getClassCodeSnippet(): string
    {
        return <<<'CODE'
<?php
class Template {
    public function ofData(array $fieldData): self {
        foreach ($fieldData as $fieldName => $fieldValue) {
            if (!isset($this->{$fieldName})) {
                continue;
            }
            $this->{$fieldName} = $fieldValue;
            
            if (is_array($fieldValue)) {
                $fieldReflection = new \ReflectionProperty($this, $fieldName);
                /** @var \ReflectionType $fieldType */
                $fieldType = $fieldReflection->getType();
                if ($fieldType == null) {
                    continue;
                }
                
                $fieldTypeArr = explode('|', (string)$fieldType);
                foreach ($fieldTypeArr as $fieldTypeStr) {
                    if (method_exists($fieldTypeStr, 'ofData')) {
                        $this->{$fieldName} = (new $fieldTypeStr)->ofData($fieldValue);
                        break;
                    }
                }
            }
        }
        return $this;
    }
    
    public static function of(array $fieldData): self 
    {
        return (new static())->ofData($fieldData);
    }
}
CODE;
    }
}
