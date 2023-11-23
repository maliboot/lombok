<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\ToCollectionAnnotationInterface;

#[LombokGenerator]
class ToCollectionGenerator extends AbstractClassVisitor
{
    protected function getClassMemberName(): string
    {
        return 'toCollection';
    }

    protected function getAnnotationInterface(): string
    {
        return ToCollectionAnnotationInterface::class;
    }

    protected function getClassCodeSnippet(): string
    {
        return <<<'CODE'
<?php
class Template {
    public function toCollection(): \Hyperf\Collection\Collection 
    {
        return \Hyperf\Collection\Collection::make($this->all());
    }
}
CODE;
    }
}
