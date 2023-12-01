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
    
    public function getMyDelegate(): {{DELEGATE_CLASS}} {
        return $this->myDelegate;
    }
    
    public function setMyDelegate({{DELEGATE_CLASS}} $delegate): self {
        $this->myDelegate = $delegate;
        return $this;
    }
    
    public static function getDelegateInstance(self $delegatedSource): {{DELEGATE_CLASS}} {
        // $delegatedSource可用于第三方模板扩展
        {{DELEGATE_NEW_CODE}}
    }
    
    public function __call($name, $arguments)
    {
        if (method_exists($this->myDelegate, $name) || method_exists($this->myDelegate, '__call')) {
            return $this->myDelegate->{$name}(...$arguments);
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if (method_exists('{{DELEGATE_CLASS}}', $name) || method_exists('{{DELEGATE_CLASS}}', '__callStatic')) {
            return self::getDelegateInstance(\Hyperf\Support\make(self::class))::{$name}(...$arguments);
        }
    }
    
    public function __get($name)
    {
        if (property_exists($this->myDelegate, $name) || method_exists($this->myDelegate, '__get')) {
            return $this->myDelegate->{$name};
        }
    }
    
    public function __set($name, $value)
    {
        if (property_exists($this->myDelegate, $name) || method_exists($this->myDelegate, '__set')) {
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
        $templateClassName = "\\{$this->reflectionClass->getName()}";
        $delegateClassName = $this->getFormatDelegateClassName();
        $delegateStmts = $this->getDelegateClassStmts();
        $delegateInsStmts = $this->getDelegateInsStmts();
        $extendWord = 'extends';
        if ($delegateReflectionClass->isInterface()) {
            $extendWord = 'implements';
        }

        return <<<CODE
\$delegateIns = new class() {$extendWord} {$delegateClassName} {
    private string \$myDelegatedSource = {$templateClassName}::class;
    
    public function delegatedSource(): string
    {
        return \$this->myDelegatedSource;
    }
    {$delegateStmts} 
};
{$delegateInsStmts}
return \$delegateIns;
CODE;
    }

    protected function getDelegateClassStmts(): string
    {
        return '';
    }

    protected function getDelegateInsStmts(): string
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
