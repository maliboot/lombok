<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\DelegateAnnotationInterface;
use MaliBoot\Lombok\Exception\LombokException;
use Reflection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

#[LombokGenerator]
class DelegateGenerator extends AbstractClassVisitor
{
    use DefaultValueTrait;

    protected function getClassMemberName(): string
    {
        return 'Delegate';
    }

    protected function getAnnotationInterface(): string
    {
        return DelegateAnnotationInterface::class;
    }

    protected function getClassCodeSnippet(): string
    {
        $code = <<<'CODE'
<?php
class Template {
    {{TEMPLATE_CLASS_STMTS}}
    private {{DELEGATE_CLASS}} $myDelegate;
    {{CONST}}
    
    public function __construct(){
        $this->myDelegate = self::getDelegateInstance($this);
        {{CONSTRUCT_CODE}}
    }
    
    public function delegate() {
        return $this->myDelegate;
    }
    
    public static function getDelegateInstance(self $delegatedSource): {{DELEGATE_CLASS}} {
        {{DELEGATE_NEW_CODE}}
    }
    
    public function __call($name, $arguments)
    {
        if (method_exists($this->myDelegate, $name)) {
            return $this->myDelegate->{$name}(...$arguments);
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if (method_exists('{{DELEGATE_CLASS}}', $name)) {
            return self::getDelegateInstance(\Hyperf\Support\make(self::class))::{$name}(...$arguments);
        }
    }
    
    public function __get($name)
    {
        if (property_exists($this->myDelegate, $name)) {
            return $this->myDelegate->{$name};
        }
    }
    
    public function __set($name, $value)
    {
        if (property_exists($this->myDelegate, $name)) {
            $this->myDelegate->{$name} = $value;
        }
    }
}
CODE;
        $delegateClassName = $this->getFormatDelegateClassName();
        $delegateReflectionClass = new ReflectionClass($delegateClassName);
        if (! empty($delegateReflectionClass->getMethods(ReflectionMethod::IS_ABSTRACT))) {
            throw new LombokException(sprintf('[%s]委托异常: 委托类[%s]不可以有抽象方法', $this->reflectionClass->getName(), $delegateClassName));
        }
        // 常量委托
        $templateConstArr = [];
        foreach ($delegateReflectionClass->getReflectionConstants() as $reflectionConstant) {
            $templateConstArr[] = sprintf('%s const %s = %s;', Reflection::getModifierNames($reflectionConstant->getModifiers())[0], $reflectionConstant->getName(), $this->getValString($reflectionConstant->getValue()));
        }
        $templateConstants = implode("\n    ", $templateConstArr);
        return str_replace(
            ['{{DELEGATE_CLASS}}', '{{DELEGATE_NEW_CODE}}', '{{CONST}}', '{{CONSTRUCT_CODE}}', '{{TEMPLATE_CLASS_STMTS}}'],
            [
                $delegateClassName,
                $this->getDelegateNewCode($delegateReflectionClass),
                $templateConstants,
                $this->getTemplateClassConstructStmts(),
                $this->getTemplateClassStmts()],
            $code
        );
    }

    protected function getDelegateConstructParameters(ReflectionClass $delegateReflectionClass): array
    {
        $delegateConstructParameterArr = [];
        $delegateConstructor = $delegateReflectionClass->getConstructor();
        if ($delegateConstructor === null) {
            return $delegateConstructParameterArr;
        }

        foreach ($delegateConstructor->getParameters() as $parameter) {
            $parameterType = $parameter->getType();
            $parameterTypeName = $parameterType instanceof ReflectionNamedType ? $parameterType->getName() : '';

            $defaultKey = sprintf('%s $%s', $parameterTypeName, $parameter->getName());
            $defaultVal = null;
            if ($parameter->isDefaultValueAvailable()) {
                $defaultVal = $this->getValString($parameter->getDefaultValue());
                $defaultKey = $defaultKey . ' = ' . $defaultVal;
            } else {
                $parameterTypeClass = $parameter->getClass();
                if ($parameterTypeClass !== null) {
                    $defaultVal = "\\Hyperf\\Context\\ApplicationContext::getContainer()->get(\\{$parameterTypeClass->getName()}::class)";
                }
            }

            $delegateConstructParameterArr[$defaultKey] = $defaultVal;
        }
        return $delegateConstructParameterArr;
    }

    protected function getDelegateNewCode(ReflectionClass $delegateReflectionClass): string
    {
        $delegateClassName = $this->getFormatDelegateClassName();
        $delegateStmts = $this->getDelegateClassStmts();
        $delegateConstructParams = $this->getDelegateConstructParameters($delegateReflectionClass);
        $delegateConstructParamsSign = implode(',', array_keys($delegateConstructParams));
        $delegateConstructParamsVal = implode(',', array_values($delegateConstructParams));
        $delegateConstructParamsValTypeNames = array_map(function ($item) {
            return '$' . $item->getName();
        }, $delegateReflectionClass->getConstructor()->getParameters());
        $delegateConstructParamsValTypeNames = implode(',', $delegateConstructParamsValTypeNames);
        $extendWord = 'extends';
        $extendParent = "parent::__construct({$delegateConstructParamsValTypeNames});";
        if ($delegateReflectionClass->isInterface()) {
            $extendWord = 'implements';
        }

        $result = <<<CODE
return new class({$delegateConstructParamsVal}, \$delegatedSource) {$extendWord} {$delegateClassName} {
    private ?\\{$this->reflectionClass->getName()} \$myDelegatedSource = null;

    public function __construct({$delegateConstructParamsSign}, ?\\{$this->reflectionClass->getName()} \$delegatedSource = null)
    {
        {$extendParent}
        \$this->myDelegatedSource = \$delegatedSource;
    }
    
    public function delegatedSource(): \\{$this->reflectionClass->getName()}
    {
        return \$this->myDelegatedSource;
    }
    {$delegateStmts} 
};
CODE;
        dump($result);
        return $result;
    }

    protected function getDelegateClassStmts(): string
    {
        return '';
    }

    protected function getTemplateClassConstructStmts(): string
    {
        return '';
    }

    protected function getTemplateClassStmts(): string
    {
        return '';
    }

    protected function getDelegateClassName(): string
    {
        /** @var ReflectionAttribute $attribute */
        $reflectionAttribute = $this->reflectionClass->getAttributes(DelegateAnnotationInterface::class, ReflectionAttribute::IS_INSTANCEOF)[0];
        /** @var DelegateAnnotationInterface $attribute */
        $attribute = $reflectionAttribute->newInstance();
        return $attribute->getDelegateClassName();
    }

    private function getFormatDelegateClassName(): string
    {
        $delegateClassName = $this->getDelegateClassName();
        $delegateClassName[0] !== '\\' && $delegateClassName = '\\' . $delegateClassName;
        return $delegateClassName;
    }
}
