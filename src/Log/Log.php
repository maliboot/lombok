<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Log;

use Hyperf\Context\ApplicationContext;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class Log
{
    /**
     * 被调用类的名称.
     */
    public const CALL_CLASS_NAME = 'CALL_CLASS_NAME';

    /**
     * 日志配置，默认config.logger.default.
     */
    public const CALL_LOG_CONFIG = 'default';

    public static function get(string $name = self::CALL_CLASS_NAME, string $group = self::CALL_LOG_CONFIG): LoggerInterface
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name, $group);
    }

    public static function FormatDefaultName(string $name): string
    {
        return str_replace('\\', '_', $name);
    }
}
