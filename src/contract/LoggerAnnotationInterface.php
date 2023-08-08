<?php

namespace MaliBoot\Lombok\contract;

use Hyperf\Di\Annotation\AnnotationInterface;

interface LoggerAnnotationInterface extends AnnotationInterface
{
    public function getLogName(): string;

    public function getLogGroup(): string;
}