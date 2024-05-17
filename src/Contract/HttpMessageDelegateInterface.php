<?php

declare(strict_types=1);

namespace MaliBoot\Lombok\Contract;

interface HttpMessageDelegateInterface
{
    /**
     * @param array $attributes psrHttpMessage中的attributes数组
     * @return mixed 计算后的 attribute value
     */
    public static function compute(string $key, array $attributes, object $instance, string $fieldName): mixed;
}
