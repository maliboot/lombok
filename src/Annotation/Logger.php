<?php

declare(strict_types=1);

namespace Maliboot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Maliboot\Lombok\contract\LoggerAnnotationInterface;
use Maliboot\Lombok\Log\Log;
use Maliboot\Lombok\Log\LoggerAnnotationTrait;

#[Attribute(Attribute::TARGET_CLASS)]
class Logger extends AbstractAnnotation implements LoggerAnnotationInterface
{
    use LoggerAnnotationTrait;

    /**
     * @param string $name 日志通道<div><p>默认：当前被调用类的名称</p><p>例子：类名改为了下划线，如<p>\Foo\User => Foo_User</p></p></div>
     * @param string $group 日志配置，<div><p>默认：default【config.logger.default】</p></div>
     */
    public function __construct(string $name = Log::CALL_CLASS_NAME, string $group = Log::CALL_LOG_CONFIG)
    {
        parent::__construct($name, $group);
    }
}
