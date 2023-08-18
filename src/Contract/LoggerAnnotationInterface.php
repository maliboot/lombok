<?php

namespace MaliBoot\Lombok\Contract;

interface LoggerAnnotationInterface
{
    public function getLogName(): string;

    public function getLogGroup(): string;
}