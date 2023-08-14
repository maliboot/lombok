<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast;

use PhpParser\Node\Stmt\Class_;
use ReflectionAttribute;
use ReflectionProperty;

abstract class AbstractClassFieldVisitor extends AbstractVisitor
{
    public function __construct(
        protected Class_ $class_,
        protected ReflectionProperty $reflectionProperty,
    ) {
        parent::__construct($this->class_, $this->reflectionProperty->getDeclaringClass());
    }

    protected function enable(): bool
    {
        if (parent::enable()) {
            return true;
        }

        // 类属性注解
        $attributes = $this->reflectionProperty->getAttributes($this->getAnnotationInterface(), ReflectionAttribute::IS_INSTANCEOF);
        if (! empty($attributes)) {
            return true;
        }
        return false;
    }
}
