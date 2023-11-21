<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Ast\Generator;

use MaliBoot\Lombok\Annotation\LombokGenerator;
use MaliBoot\Lombok\Ast\AbstractClassVisitor;
use MaliBoot\Lombok\Contract\ToStringAnnotationInterface;

#[LombokGenerator]
class ToStringGenerator extends AbstractClassVisitor
{
    protected function getClassMemberName(): string
    {
        return '__toString';
    }

    protected function getAnnotationInterface(): string
    {
        return ToStringAnnotationInterface::class;
    }

    protected function getClassCodeSnippet(): string
    {
        return <<<'CODE'
<?php
class Context {
    public function __toString(): string
    {
        $result = $this->toArray();
        if (empty($result)) {
            $result = new \stdClass();
        }

        return \Hyperf\Codec\Json::encode($result);
    }
}
CODE;
    }

    protected function getImpls(): array
    {
        return [
            \Hyperf\Contract\Jsonable::class,
        ];
    }
}
