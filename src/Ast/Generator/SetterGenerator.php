<?php

declare(strict_types=1);

namespace Maliboot\Lombok\Ast\Generator;

use Maliboot\Lombok\Ast\AbstractClassVisitor;
use Maliboot\Lombok\contract\SetterAnnotationInterface;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use ReflectionAttribute;

class SetterGenerator extends AbstractClassVisitor
{
    protected function handle(): void
    {
        foreach ($this->class_->getProperties() as $property_) {
            $this->isStmtBuild($property_) && $this->buildStmt($property_);
        }
    }

    protected function enable(): bool
    {
        return true;
    }

    protected function isStmtBuild(Property $property_): bool
    {
        $fieldName = $property_->props[0]->name->name;
        // 不覆盖已存在的方法
        if ($this->reflectionClass->hasMethod('set' . ucfirst($fieldName))) {
            return false;
        }

        // 类注解
        $attributes = $this->reflectionClass->getAttributes(SetterAnnotationInterface::class, ReflectionAttribute::IS_INSTANCEOF);
        if (! empty($attributes)) {
            return true;
        }

        // 类属性注解
        $reflectionProperty = $this->reflectionClass->getProperty($fieldName);
        $attributes = $reflectionProperty->getAttributes(SetterAnnotationInterface::class, ReflectionAttribute::IS_INSTANCEOF);
        if (! empty($attributes)) {
            return true;
        }
        return false;
    }

    protected function buildStmt(Property $property_): void
    {
        $fieldName = $property_->props[0]->name->name;
        $fun = new ClassMethod('set' . ucfirst($fieldName));
        $fun->params[] = new Param(new Variable($fieldName), $property_->props[0]->default, $property_->type);
        $fun->returnType = new Name('self');
        $fun->stmts[] = new Expression(
            new Assign(
                new PropertyFetch(
                    new Variable('this'),
                    new Identifier($fieldName)
                ),
                new Variable($fieldName)
            )
        );
        $fun->stmts[] = new Return_(
            new Variable('this')
        );
        $this->class_->stmts = array_merge($this->class_->stmts, [$fun]);
    }
}
