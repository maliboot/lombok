<?php

namespace MaliBoot\Lombok\Ast\Generator;

trait DefaultValueTrait
{
    private function getValString($val): string
    {
        if (is_string($val)) {
            return "'" . $val . "'";
        }

        if (is_bool($val)) {
            return $val ? 'true' : 'false';
        }

        return (string) $val;
    }
}