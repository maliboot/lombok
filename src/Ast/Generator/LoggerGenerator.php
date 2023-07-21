<?php

declare(strict_types=1);

namespace Maliboot\Lombok\Ast\Generator;

use Hyperf\Di\Aop\Ast;
use Maliboot\Lombok\Ast\AbstractClassVisitor;
use Maliboot\Lombok\contract\LoggerAnnotationInterface;
use Maliboot\Lombok\Log\Log;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use ReflectionAttribute;

class LoggerGenerator extends AbstractClassVisitor
{
    protected function enable(): bool
    {
        if ($this->reflectionClass->hasProperty('logger')) {
            return false;
        }

        $attributes = $this->reflectionClass->getAttributes(LoggerAnnotationInterface::class, ReflectionAttribute::IS_INSTANCEOF);
        if (! empty($attributes)) {
            return true;
        }
        return false;
    }

    protected function handle(): void
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
        $this->logger = \Hyperf\Context\ApplicationContext::getContainer()->get(\Maliboot\Lombok\contract\DelegateInterface::class)::log('{{$name}}', '{{$group}}');
    }
}
CODE;
        $code = str_replace(['{{$name}}', '{{$group}}'], $this->getDefaultLogParams($attribute), $code);
        $parser = new Ast();
        /** @var Class_ $tpl ... */
        $tpl = $parser->parse($code)[0];
        /** @var ClassMethod $tplMethod ... */
        [$tplProperty, $tplMethod] = $tpl->stmts;

        // 避免重复的构建方法
        $this->class_->stmts = array_reduce($this->class_->stmts, function (array $carry, Stmt $item) use ($tplMethod) {
            if ($item instanceof ClassMethod && $item->name->toString() === $tplMethod->name->toString()) {
                $item->stmts = [...$item->stmts, ...$tplMethod->stmts];
            }
            $carry[] = $item;
            return $carry;
        }, [$tplProperty]);
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
