<?php

namespace MaliBoot\Lombok\Contract;

/**
 * 类属性getter委托.
 */
interface GetterDelegateInterface
{
    /**
     * @param string $name 字段名称
     * @param mixed $value 字段值
     * @param string $type 字段类型
     * @param object $classInstance 字段所在类的实例
     * @return mixed 字段值
     */
    public static function get(string $name, mixed $value, string $type, object $classInstance): mixed;
}