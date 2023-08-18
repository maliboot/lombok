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
            $setterName = 'set' . ucfirst($fieldName);
            $setterExist = method_exists($this, $setterName);
            if ($setterExist) {
                $this->$setterName($fieldValue);
            } else {
                $this->{$fieldName} = $fieldValue;
            }
            
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
                        $typeValIns = (new $fieldTypeStr)->ofData($fieldValue);
                        if ($setterExist) {
                            $this->$setterName($typeValIns);
                        } else {
                            $this->{$fieldName} = $typeValIns;
                        }
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
