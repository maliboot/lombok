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

        if (is_array($val)) {
            return var_export($val, true);
        }

        return (string) $val;
    }
}