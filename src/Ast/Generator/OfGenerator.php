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
    private function _ofData(array $fieldData, bool $isStrict = false): self {
        $reflectionProperties = self::getMyReflectionClass()['reflectionProperties'] ?? [];
        $mapFields = array_reduce($reflectionProperties, fn ($carry, $item) => $item['ofMapName'] ? [...$carry, $item['ofMapName'] => $item['name']] : $carry, []);
        foreach ($fieldData as $fieldName => $fieldValue) {
            if (isset($mapFields[$fieldName])) {
                $fieldName = $mapFields[$fieldName];
            }
            if (empty($fieldName) || ! is_string($fieldName)) {
                continue;
            }
            if (str_contains($fieldName, '_')) {
                $fieldSuffix = $fieldName[strlen($fieldName) - 1] === '_' ? '_' : '';
                $fieldName = lcfirst(array_reduce(explode('_', $fieldName), fn($carry, $item) => $carry .ucfirst($item), '')) . $fieldSuffix;
            }
            if (! isset($reflectionProperties[$fieldName])) {
                continue;
            }
            $fieldRef = $reflectionProperties[$fieldName];
            if (! $fieldRef['allowsNull'] && $fieldValue === null) {
                continue;
            }
            
            try {
                // try convert
                if (! $isStrict && $fieldValue !== null) {
                    if (is_numeric($fieldValue) && str_contains($fieldRef['type'], 'int')) {
                        $fieldValue = (int) $fieldValue;
                    } elseif (str_contains($fieldRef['type'], 'bool')) {
                        $fieldValue = (bool) $fieldValue;
                    } elseif (is_numeric($fieldValue) && str_contains($fieldRef['type'], 'float')) {
                        $fieldValue = (float) $fieldValue;
                    } elseif (str_contains($fieldRef['type'], 'string')) {
                        $fieldValue = (string)$fieldValue;
                    }
                }
                
                $setterName = 'set' . ucfirst($fieldName);
                $resultVal = $fieldValue;
                if (is_array($fieldValue)) {
                    $fieldTypeArr = explode('|', $fieldRef['type']);
                    foreach ($fieldTypeArr as $fieldTypeStr) {
                        if (class_exists($fieldTypeStr) && method_exists($fieldTypeStr, 'ofData')) {
                            $resultVal = (new $fieldTypeStr)->ofData($fieldValue);
                            break;
                        }
                    }
                }
                
                if ($fieldRef['hasSetter']) {
                    $this->$setterName($resultVal);
                } else {
                    $this->{$fieldName} = $resultVal;
                }
            } catch (\Throwable $e) {
                throw new \MaliBoot\Lombok\Exception\LombokException(sprintf(
                    'Lombok::of/ofData() 尝试赋值异常。变量类型为：%s::(%s)$%s，而实际赋值为：[%s]', 
                    self::class, 
                    $fieldRef['type'], 
                    $fieldName, 
                    var_export($fieldValue, true)
                ));
            }
        }
        return $this;
    }
    
    public function ofData(array $fieldData, bool $isStrict = false): self {
        $this->_ofData($fieldData, $isStrict);
    }
    
    public static function of(array $fieldData, bool $isStrict = false): self 
    {
        return (new static())->_ofData($fieldData, $isStrict);
    }
}
CODE;
    }
}
