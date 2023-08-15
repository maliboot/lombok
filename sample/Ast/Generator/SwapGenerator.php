<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Sample\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Sample\Contract\SwapAnnotationInterface;

// 1、此处需要添加\MaliBoot\Lombok\Annotation\LombokGenerator注解
// 2、需要继承 \MaliBoot\Lombok\Ast\AbstractClassVisitor 或者 \MaliBoot\Lombok\Ast\AbstractClassFieldVisitor
// 2.1、\MaliBoot\Lombok\Ast\AbstractClassVisitor 会内置目标类的反射变量，以支持模版相关的变量替换用处
// 2.2、\MaliBoot\Lombok\Ast\AbstractClassFieldVisitor 会内置目标类所有属性的反射变量，以支持模版相关的变量替换用处
#[LombokGenerator]
class SwapGenerator extends AbstractClassVisitor
{
    // 自定义的方法OR属性名称
    protected function getClassMemberName(): string
    {
        return 'swap';
    }

    // 自定义组合注解功能时的接口凭据
    protected function getAnnotationInterface(): string
    {
        return SwapAnnotationInterface::class;
    }

    // 自定义的方法OR属性模板
    // 可自定义变量替换：提供有 AbstractClassVisitor::$reflectionClass 和 AbstractClassFieldVisitor::$reflectionProperty 配合使用
    protected function getClassCodeSnippet(): string
    {
        // 这里返回swap的方法的代码模板。类名称可以自定义，只是为看着整齐
        return <<<'CODE'
<?php
class Collection {
    public function swap (int &$a, int &$b): void {
        $tmp = $a;
        $a = $b;
        $b = $tmp;
    }
}
CODE;
    }
}
