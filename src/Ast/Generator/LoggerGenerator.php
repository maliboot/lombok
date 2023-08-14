<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\LoggerAnnotationInterface;
use MaliBoot\Lombok\Log\Log;
use ReflectionAttribute;

#[LombokGenerator]
class LoggerGenerator extends AbstractClassVisitor
{
    protected function getClassMemberType(): string
    {
        return parent::PROPERTY;
    }

    protected function getClassMemberName(): string
    {
        return 'logger';
    }

    protected function getAnnotationInterface(): string
    {
        return LoggerAnnotationInterface::class;
    }

    protected function getClassCodeSnippet(): string
    {
        /** @var ReflectionAttribute $attribute */
        $reflectionAttribute = $this->reflectionClass->getAttributes(LoggerAnnotationInterface::class, ReflectionAttribute::IS_INSTANCEOF)[0];
        /** @var LoggerAnnotationInterface $attribute */
        $attribute = $reflectionAttribute->newInstance();
        $code = <<<'CODE'
<?php
class Template {
    public \Psr\Log\LoggerInterface $logger;
    public function __construct(){
        $this->logger = \MaliBoot\Lombok\Log\Log::get('{{$name}}', '{{$group}}');
    }
}
CODE;
        return str_replace(['{{$name}}', '{{$group}}'], $this->getDefaultLogParams($attribute), $code);
    }

    protected function getDefaultLogParams(LoggerAnnotationInterface $logger): array
    {
        $name = $logger->getLogName();
        if ($name === Log::CALL_CLASS_NAME) {
            $name = Log::FormatDefaultName($this->reflectionClass->getName());
        }

        $group = $logger->getLogGroup();
        return [$name, $group];
    }
}
