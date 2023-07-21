<?php

declare(strict_types=1);

namespace Maliboot\Lombok\Ast;

use PhpParser\Node\Stmt\Class_;
use ReflectionClass;

abstract class AbstractClassVisitor
{
    public function __construct(
        protected ReflectionClass $reflectionClass,
        protected Class_ $class_
    ) {}

    public function execute(): void
    {
        $this->enable() && $this->handle();
    }

    abstract protected function enable(): bool;

    abstract protected function handle(): void;
}
