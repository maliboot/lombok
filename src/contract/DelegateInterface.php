<?php

namespace MaliBoot\Lombok\contract;

use Hyperf\Collection\Collection;
use MaliBoot\Lombok\Log\Log;
use Psr\Log\LoggerInterface;

interface DelegateInterface
{
    /**
     * 获取日志实例.
     * @param string $name ...
     * @param string $group ...
     * @return LoggerInterface ...
     */
    public static function log(string $name = Log::CALL_CLASS_NAME, string $group = Log::CALL_LOG_CONFIG): LoggerInterface;

    public static function toCollection(object $class): Collection;

    public static function toArray(object $class): array;
}