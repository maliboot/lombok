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
            if (empty($fieldName)) {
                continue;
            }
            if (str_contains($fieldName, '_')) {
                $fieldSuffix = $fieldName[strlen($fieldName) - 1] === '_' ? '_' : '';
                $fieldName = lcfirst(array_reduce(explode('_', $fieldName), fn($carry, $item) => $carry .ucfirst($item), '')) . $fieldSuffix;
            }
            if (!property_exists($this, $fieldName)) {
                continue;
            }
            
            $fieldReflection = new \ReflectionProperty($this, $fieldName);
            /** @var \ReflectionType $fieldType */
            $fieldType = $fieldReflection->getType();
            $fieldTypes = (string)$fieldType;
            if ($fieldType !== null && ! $fieldType->allowsNull() && $fieldValue === null) {
                continue;
            }
            
            $setterName = 'set' . ucfirst($fieldName);
            $resultVal = $fieldValue;
            if (is_array($fieldValue)) {
                $fieldTypeArr = explode('|', $fieldTypes);
                foreach ($fieldTypeArr as $fieldTypeStr) {
                    if (class_exists($fieldTypeStr) && method_exists($fieldTypeStr, 'ofData')) {
                        $resultVal = (new $fieldTypeStr)->ofData($fieldValue);
                        break;
                    }
                }
            }
            
            if (method_exists($this, $setterName)) {
                $this->$setterName($resultVal);
            } else {
                $this->{$fieldName} = $resultVal;
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
