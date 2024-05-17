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
            
            $setterName = 'set' . ucfirst($fieldName);
            if (!isset($reflectionProperties[$fieldName])) {
                if (!property_exists($this, $fieldName)) {
                    continue;
                }
                if (method_exists($this, $setterName)) {
                    $this->{$setterName}($fieldValue);
                } else {
                    $this->{$setterName} = $fieldValue;
                }
                continue;
            }
            $fieldRef = $reflectionProperties[$fieldName];
            if ($fieldRef['isOfInner']) {
                continue;
            }
            if (! $fieldRef['allowsNull'] && $fieldValue === null) {
                continue;
            }
            
            $typeConvert = function (string $type, $val) {
                try {
                    if (str_contains($type, '\\')) {
                        $typeClazz = array_filter(explode('|', $type), fn () => str_contains($type, '\\'));
                        $typeClazz = ltrim($typeClazz[0], '?');
                        if (class_exists($typeClazz)) {
                            if (method_exists($typeClazz, 'ofData')) {
                                return (new $typeClazz())->ofData(is_array($val) ? $val : (array) $val);
                            }
                            return new $typeClazz($val);
                        }
                    }
                
                    if (str_contains($type, 'int')) {
                        return (int) $val;
                    }
                    if (str_contains($type, 'bool')) {
                        return (bool) $val;
                    }
                    if (str_contains($type, 'float')) {
                        return (float) $val;
                    }
                    if (str_contains($type, 'string')) {
                        return (string) $val;
                    }
                } catch (\Throwable $e) {
                    throw new \MaliBoot\Lombok\Exception\LombokException(sprintf(
                        '%s，%s强转%s失败', 
                        $e->getMessage(),
                        str_replace("\n", '', var_export($val, true)),
                        $type, 
                    ));
                }
            
                return $val;
            };
            
            try {
                $resultVal = $fieldValue;
                // try convert
                if (! $isStrict && $fieldValue !== null) {
                    $resultVal = $typeConvert($fieldRef['type'], $fieldValue);
                }
                if (str_contains($fieldRef['type'], 'array') && ($fieldRef['arrayKey'] || $fieldRef['arrayValue'])) {
                    $newFieldValue = [];
                    foreach ($fieldValue as $key => $val) {
                        $newKey = $fieldRef['arrayKey'] ? $typeConvert($fieldRef['arrayKey'], $key) : $key;
                        $newVal = $fieldRef['arrayValue'] ? $typeConvert($fieldRef['arrayValue'], $val) : $val;
                        $newFieldValue[$newKey] = $newVal;
                    }
                    $resultVal = $newFieldValue;
                }
                
                if ($fieldRef['hasSetter']) {
                    $this->$setterName($resultVal);
                } else {
                    $this->{$fieldName} = $resultVal;
                }
            } catch (\Throwable $e) {
                $arrayHint = [];
                $fieldRef['arrayKey'] && $arrayHint[] = $fieldRef['arrayKey'];
                $fieldRef['arrayValue'] && $arrayHint[] = $fieldRef['arrayValue'];
                $arrayHint = empty($arrayHint) ? '' : '<' . implode(',', $arrayHint) . '>';
                throw new \MaliBoot\Lombok\Exception\LombokException(sprintf(
                    'Lombok::of/ofData() 尝试赋值异常[%s]。变量类型为：%s::(%s%s)$%s，而实际赋值为：[%s]', 
                    $e->getMessage(),
                    self::class, 
                    $fieldRef['type'],
                    $arrayHint,
                    $fieldName, 
                    var_export($fieldValue, true)
                ));
            }
        }
        return $this;
    }
    
    public function ofData(array $fieldData, bool $isStrict = false): self {
        return $this->_ofData($fieldData, $isStrict);
    }
    
    public static function of(array $fieldData, bool $isStrict = false): self 
    {
        return (new static())->_ofData($fieldData, $isStrict);
    }
}
CODE;
    }
}
