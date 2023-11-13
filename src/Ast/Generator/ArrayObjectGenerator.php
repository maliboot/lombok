<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\ArrayObjectAnnotationInterface;
use Serializable;

#[LombokGenerator]
class ArrayObjectGenerator extends AbstractClassVisitor
{
    protected function getClassMemberName(): string
    {
        return 'offsetExists';
    }

    protected function getAnnotationInterface(): string
    {
        return ArrayObjectAnnotationInterface::class;
    }

    protected function getClassCodeSnippet(): string
    {
        return <<<'CODE'
<?php
class Template {
    public function __serialize(): array
    {
        if (method_exists($this, 'toArray')) {
            return $this->toArray();
        }

        return [];
    }

    public function __unserialize(array $data): void
    {
        if (method_exists($this, 'ofData')) {
            $this->ofData($data);
        }
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->__serialize());
    }

    public function offsetExists($offset): bool
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset): mixed
    {
        $getterMethod = 'get' . ucfirst($offset);
        if (method_exists($this, $getterMethod)) {
            return $this->{$getterMethod}();
        }

        return $this->{$offset} ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $setterMethod = 'set' . ucfirst($offset);
        if (method_exists($this, $setterMethod)) {
            $this->{$setterMethod}($value);
            return;
        }
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset): void
    {
        $this->offsetSet($offset, null);
    }

    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    public function unserialize($serialized): void
    {
        $this->__unserialize(unserialize($serialized));
    }

    public function count(): int
    {
        return count($this->__serialize());
    }
}
CODE;
    }

    protected function getImpls(): array
    {
        return [
            IteratorAggregate::class,
            ArrayAccess::class,
            Serializable::class,
            Countable::class,
        ];
    }
}
