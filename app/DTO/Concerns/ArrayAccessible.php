<?php

namespace App\DTO\Concerns;

trait ArrayAccessible
{
    public function toArray(): array
    {
        $result = [];

        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof \BackedEnum) {
                $result[$key] = $value->value;

                continue;
            }

            if ($value instanceof \UnitEnum) {
                $result[$key] = $value->name;

                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
