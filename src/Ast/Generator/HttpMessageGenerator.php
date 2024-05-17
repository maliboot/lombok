<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\HttpMessageAnnotationInterface;
use MaliBoot\Lombok\Contract\HttpMessageDelegateInterface;
use ReflectionAttribute;

#[LombokGenerator]
class HttpMessageGenerator extends AbstractClassVisitor
{
    public function getFieldCodeSnippet(string $type, string $fieldName, string $delegate): string
    {
        $result = '';
        $typeGetFn = $type;

        if ($type === 'attribute') {
            $typeGetFn = 'getAttribute';
        }
        if ($type === 'header') {
            $typeGetFn = 'getHeaderLine';
        }

        if (class_exists($delegate) && is_subclass_of($delegate, HttpMessageDelegateInterface::class)) {
            if ($delegate[0] != '\\') {
                $delegate = '\\' . $delegate;
            }
            $result = sprintf(
                "\$foo = call_user_func_array('%s::compute', ['%s', \$%ss, \$this, '%s']);\$foo !== null && \$this->%s = \$foo;",
                $delegate,
                $delegate,
                $type,
                $fieldName,
                $fieldName,
            );
        } else {
            $result = sprintf(
                "\$foo = \$requestInstance->%s('%s', null);\$foo !== null && \$this->%s = \$foo;",
                $typeGetFn,
                $delegate,
                $fieldName
            );
        }

        return $result;
    }

    protected function getClassMemberType(): string
    {
        return parent::PROPERTY;
    }

    protected function getClassMemberName(): string
    {
        return '__HttpMessageGenerator';
    }

    protected function getAnnotationInterface(): string
    {
        return HttpMessageAnnotationInterface::class;
    }

    protected function enable(): bool
    {
        foreach ($this->reflectionClass->getProperties() as $property) {
            /** @var ReflectionAttribute $attribute */
            $reflectionAttributes = $property->getAttributes(HttpMessageAnnotationInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            if (! empty($reflectionAttributes)) {
                return true;
            }
        }
        return false;
    }

    protected function getClassCodeSnippet(): string
    {
        $attrCodeSnippet = [];
        $headerCodeSnippet = [];
        $cookieCodeSnippet = [];
        foreach ($this->reflectionClass->getProperties() as $property) {
            /** @var ReflectionAttribute $attribute */
            $reflectionAttributes = $property->getAttributes(HttpMessageAnnotationInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            if (empty($reflectionAttributes)) {
                continue;
            }
            /** @var HttpMessageAnnotationInterface $attribute */
            $attribute = $reflectionAttributes[0]->newInstance();
            switch ($attribute->type()) {
                case HttpMessageAnnotationInterface::COOKIE:
                    $cookieCodeSnippet[] = $this->getFieldCodeSnippet('cookie', $property->name, $attribute->delegate());
                    break;
                case HttpMessageAnnotationInterface::HEADER:
                    $headerCodeSnippet[] = $this->getFieldCodeSnippet('cookie', $property->name, $attribute->delegate());
                    break;
                default:
                    $attrCodeSnippet[] = $this->getFieldCodeSnippet('attribute', $property->name, $attribute->delegate());
            }
        }

        $fieldCodeSnippet = '';
        if (! empty($attrCodeSnippet)) {
            $fieldCodeSnippet .= '$attributes = $requestInstance->getAttributes();';
            $fieldCodeSnippet .= implode(';', $attrCodeSnippet);
        }
        if (! empty($cookieCodeSnippet)) {
            $fieldCodeSnippet .= '$cookies = $requestInstance->getCookieParams();';
            $fieldCodeSnippet .= implode(';', $cookieCodeSnippet);
        }
        if (! empty($headerCodeSnippet)) {
            $fieldCodeSnippet .= '$headers = $requestInstance->getHeaders();';
            $fieldCodeSnippet .= implode(';', $headerCodeSnippet);
        }

        $code = <<<'CODE'
<?php
class Template {
    public function __construct(){
        $container = \Hyperf\Context\ApplicationContext::getContainer();
        if ($container->has(\Hyperf\HttpServer\Contract\RequestInterface::class)){
            $requestInstance = $container->get(\Hyperf\HttpServer\Contract\RequestInterface::class);
            {{fieldCodeSnippet}}
        }
    }
}
CODE;
        return str_replace(['{{fieldCodeSnippet}}'], [$fieldCodeSnippet], $code);
    }
}
