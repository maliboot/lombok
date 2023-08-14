<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast;

use Hyperf\Di\Aop\Ast;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use ReflectionAttribute;
use ReflectionClass;

abstract class AbstractVisitor
{
    public const PROPERTY = 'property';

    public const CONSTANT = 'constant';

    public const METHOD = 'method';

    public function __construct(
        protected Class_ $class_,
        protected ReflectionClass $reflectionClass,
    ) {
    }

    public function execute(): void
    {
        $this->enable() && $this->rebuildClassStmts($this->getClassCodeSnippet());
    }

    protected function enable(): bool
    {
        if (call_user_func([$this->reflectionClass, 'has' . ucfirst($this->getClassMemberType())], $this->getClassMemberName())) {
            return false;
        }

        $attributes = $this->reflectionClass->getAttributes($this->getAnnotationInterface(), ReflectionAttribute::IS_INSTANCEOF);
        if (! empty($attributes)) {
            return true;
        }
        return false;
    }

    protected function getClassMemberType(): string
    {
        return self::METHOD;
    }

    protected function rebuildClassStmts(string $classCodeCodeSnippet): void
    {
        /** @var Class_ $tpl ... */
        $tpl = (new Ast())->parse($classCodeCodeSnippet)[0];

        $originTplStmts = [];
        foreach ($this->class_->stmts as $stmtKey_ => $stmt_) {
            $flag = null;
            if (is_a($stmt_, Property::class)) {
                $flag = $stmt_->props[0]->name->toString() . '_field';
            }
            if (is_a($stmt_, ClassMethod::class)) {
                $flag = $stmt_->name->toString() . '_method';
            }
            if (is_a($stmt_, ClassConst::class)) {
                $flag = $stmt_->consts[0]->name->toString() . '_const';
            }
            $flag !== null && $originTplStmts[$flag] = ['key' => $stmtKey_, 'value' => $stmt_];
        }
        $allowMagicMethods = [
            '__construct' => 1,
            '__call' => 1,
            '__callStatic' => 1,
            '__get' => 1,
            '__set' => 1,
        ];

        foreach ($tpl->stmts as $tplNode) {
            $tplNodeFlag = null;
            if (is_a($tplNode, Property::class)) {
                $tplNodeFlag = $tplNode->props[0]->name->toString() . '_field';
            }
            if (is_a($tplNode, ClassMethod::class)) {
                $tplNodeFlag = $tplNode->name->toString() . '_method';
            }

            if (is_a($tplNode, ClassConst::class)) {
                $tplNodeFlag = $tplNode->consts[0]->name->toString() . '_const';
            }

            // 过滤非类属性、方法节点
            if ($tplNodeFlag === null) {
                continue;
            }

            // 不存在重复的构建方法、属性时，直接构建
            if (! isset($originTplStmts[$tplNodeFlag])) {
                $this->class_->stmts = [...$this->class_->stmts, $tplNode];
                continue;
            }

            // 个别魔术方法允许合并
            if (is_a($tplNode, ClassMethod::class) && isset($allowMagicMethods[$tplNode->name->toString()])) {
                $this->class_->stmts[$originTplStmts[$tplNodeFlag]['key']]->stmts = [
                    ...$this->class_->stmts[$originTplStmts[$tplNodeFlag]['key']]->stmts,
                    ...$tplNode->stmts,
                ];
            }
        }
    }

    abstract protected function getClassMemberName(): string;

    abstract protected function getAnnotationInterface(): string;

    abstract protected function getClassCodeSnippet(): string;
}
