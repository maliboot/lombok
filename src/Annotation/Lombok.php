<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use MaliBoot\Lombok\Contract\LombokAnnotationInterface;
use MaliBoot\Lombok\Log\Log;
use MaliBoot\Lombok\Log\LoggerAnnotationTrait;

#[Attribute(Attribute::TARGET_CLASS)]
class Lombok extends AbstractAnnotation implements LombokAnnotationInterface
{
    use LoggerAnnotationTrait;

    /**
     * @param ?string $getterSetterDelegate GetterSetter委托类<div><p>默认为null，无委托</p><p>委托类需要实现<a href='psi_element://\MaliBoot\Lombok\Contract\GetterSetterDelegateInterface'>GetterSetterDelegateInterface</a></p></div>
     * @param string $logName 日志通道<div><p>默认：当前被调用类的名称</p><p>例子：类名改为了下划线，如<p>\Foo\User => Foo_User</p></p></div>
     * @param string $logGroup 日志配置，<div><p>默认：default【config.logger.default】</p></div>
     */
    public function __construct(
        public ?string $getterSetterDelegate = null,
        string $logName = Log::CALL_CLASS_NAME,
        string $logGroup = Log::CALL_LOG_CONFIG
    ) {
    }

    public function setterDelegate(): ?string
    {
        return $this->getterSetterDelegate;
    }

    public function getterDelegate(): ?string
    {
        return $this->getterSetterDelegate;
    }
}
