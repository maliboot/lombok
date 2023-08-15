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
        return 'getMyDelegate';
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
    {{OTHER_EXTEND_CODE}}
    private {{DELEGATE_CLASS}} $_delegate;
    {{CONST}}
    
    public function __construct(){
        {{DELEGATE_CONSTRUCT_CODE}}
        {{CONSTRUCT_EXTEND_CODE}}
    }
    
    public function getMyDelegate() {
        return $this->_delegate;
    }
    
    public function __call($name, $arguments)
    {
        if (method_exists($this->_delegate, $name)) {
            return $this->_delegate->{$name}(...$arguments);
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if (method_exists('{{DELEGATE_CLASS}}', $name)) {
            return {{DELEGATE_CLASS}}::{$name}(...$arguments);
        }
    }
    
    public function __get($name)
    {
        if (property_exists($this->_delegate, $name)) {
            return $this->_delegate->{$name};
        }
    }
    
    public function __set($name, $value)
    {
        if (property_exists($this->_delegate, $name)) {
            $this->_delegate->{$name} = $value;
        }
    }
}
CODE;
        $delegateInstanceCodeSnippet = $this->getAbstractInsCodeSnippet();
        $delegateClassName = $this->getDelegateClassName();
        $delegateClassName[0] !== '\\' && $delegateClassName = '\\' . $delegateClassName;

        $delegateConstructCode = "\$this->_delegate = \\Hyperf\\Support\\make({$delegateClassName}::class);";
        $delegateReflectionClass = new ReflectionClass($delegateClassName);
        if (! empty($delegateReflectionClass->getMethods(ReflectionMethod::IS_ABSTRACT))) {
            throw new LombokException(sprintf('[%s]委托异常: 委托类[%s]不可以有抽象方法', $this->reflectionClass->getName(), $delegateClassName));
        }

        // 常量委托
        $delegateConstArr = [];
        foreach ($delegateReflectionClass->getReflectionConstants() as $reflectionConstant) {
            $delegateConstArr[] = sprintf(
                '%s const %s = %s;',
                Reflection::getModifierNames($reflectionConstant->getModifiers())[0],
                $reflectionConstant->getName(),
                $this->getValString($reflectionConstant->getValue())
            );
        }
        $delegateConstStr = implode("\n    ", $delegateConstArr);

        // 接口委托
        if ($delegateReflectionClass->isInterface()) {
            $delegateConstructCode = "\$this->_delegate = new class() implements {$delegateClassName} { {$delegateInstanceCodeSnippet} };";
        }

        // 抽象类委托
        if ($delegateReflectionClass->isAbstract()) {
            $delegateConstructParameterArr = [];
            $delegateConstructor = $delegateReflectionClass->getConstructor();
            if ($delegateConstructor !== null) {
                foreach ($delegateConstructor->getParameters() as $parameter) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $delegateConstructParameterArr[] = $this->getValString($parameter->getDefaultValue());
                        continue;
                    }

                    $parameterType = $parameter->getType();
                    if ($parameterType instanceof ReflectionNamedType) {
                        $parameterTypeName = $parameterType->getName();
                        $parameterTypeName[0] !== '\\' && $parameterTypeName = '\\' . $parameterTypeName;
                        $delegateConstructParameterArr[] = "\\Hyperf\\Context\\ApplicationContext::getContainer()->get({$parameterTypeName}::class)";
                    }
                }
            }
            $delegateConstructParameter = implode(',', $delegateConstructParameterArr);
            $delegateConstructCode = "\$this->_delegate = new class({$delegateConstructParameter}) extends {$delegateClassName} { {$delegateInstanceCodeSnippet} };";
        }

        $constructCode = $this->getConstructCodeSnippet();
        $otherCode = $this->getOtherContentCodeSnippet();
        return str_replace(
            ['{{DELEGATE_CLASS}}', '{{DELEGATE_CONSTRUCT_CODE}}', '{{CONST}}', '{{CONSTRUCT_EXTEND_CODE}}', '{{OTHER_EXTEND_CODE}}'],
            [$delegateClassName, $delegateConstructCode, $delegateConstStr, $constructCode, $otherCode],
            $code
        );
    }

    protected function getInsCodeSnippet(): string
    {
        return '';
    }

    protected function getAbstractInsCodeSnippet(): string
    {
        return '';
    }

    protected function getConstructCodeSnippet(): string
    {
        return '';
    }

    protected function getOtherContentCodeSnippet(): string
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
}
