<?php

namespace MaliBoot\Lombok\Contract;

use Hyperf\Di\Annotation\AnnotationInterface;

interface LoggerAnnotationInterface extends AnnotationInterface
{
    public function getLogName(): string;

    public function getLogGroup(): string;
}