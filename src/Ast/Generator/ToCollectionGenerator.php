<?php

declare(strict_types=1);

namespace Maliboot\Lombok\Ast\Generator;

use Hyperf\Di\Aop\Ast;
use Maliboot\Lombok\Ast\AbstractClassVisitor;
use Maliboot\Lombok\contract\ToCollectionAnnotationInterface;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use ReflectionAttribute;

class ToCollectionGenerator extends AbstractClassVisitor
{
    protected function enable(): bool
    {
        if ($this->reflectionClass->hasMethod('toCollection')) {
            return false;
        }

        $attributes = $this->reflectionClass->getAttributes(ToCollectionAnnotationInterface::class, ReflectionAttribute::IS_INSTANCEOF);
        if (! empty($attributes)) {
            return true;
        }
        return false;
    }

    protected function handle(): void
    {
        /** @var ReflectionAttribute $attribute */
        $reflectionAttribute = $this->reflectionClass->getAttributes(ToCollectionAnnotationInterface::class, ReflectionAttribute::IS_INSTANCEOF)[0];
        /** @var ToCollectionAnnotationInterface $attribute */
        $attribute = $reflectionAttribute->newInstance();
        $code = <<<'CODE'
<?php
class Template {
    public function toCollection(): \Hyperf\Collection\Collection 
    {
        return \Hyperf\Context\ApplicationContext::getContainer()->get(\Maliboot\Lombok\contract\DelegateInterface::class)::toCollection($this);
    }
}
CODE;
        $parser = new Ast();
        /** @var Class_ $tpl ... */
        $tpl = $parser->parse($code)[0];
        /** @var ClassMethod $tplMethod ... */
        $tplMethod = $parser->parse($code)[0]->stmts[0];

        // 避免重复的构建方法
        $hasInsert = false;
        $this->class_->stmts = array_reduce($this->class_->stmts, function (array $carry, Stmt $item) use ($tplMethod, &$hasInsert) {
            if ($item instanceof ClassMethod && $item->name->toString() === $tplMethod->name->toString()) {
                $item->stmts = [...$item->stmts, ...$tplMethod->stmts];
                $hasInsert = true;
            }
            $carry[] = $item;
            return $carry;
        }, []);
        if (! $hasInsert) {
            $this->class_->stmts = [...$this->class_->stmts, $tplMethod];
        }
    }
}
