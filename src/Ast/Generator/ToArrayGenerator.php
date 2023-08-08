<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use Hyperf\Di\Aop\Ast;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\contract\ToArrayAnnotationInterface;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use ReflectionAttribute;

class ToArrayGenerator extends AbstractClassVisitor
{
    protected function enable(): bool
    {
        if ($this->reflectionClass->hasMethod('toArray')) {
            return false;
        }

        $attributes = $this->reflectionClass->getAttributes(ToArrayAnnotationInterface::class, ReflectionAttribute::IS_INSTANCEOF);
        if (! empty($attributes)) {
            return true;
        }
        return false;
    }

    protected function handle(): void
    {
        /** @var ReflectionAttribute $attribute */
        $reflectionAttribute = $this->reflectionClass->getAttributes(ToArrayAnnotationInterface::class, ReflectionAttribute::IS_INSTANCEOF)[0];
        /** @var ToArrayAnnotationInterface $attribute */
        $attribute = $reflectionAttribute->newInstance();
        $code = <<<'CODE'
<?php
class Template {
    public function toArray(): array 
    {
        return \Hyperf\Context\ApplicationContext::getContainer()->get(\MaliBoot\Lombok\contract\DelegateInterface::class)::toArray($this);
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
