<?php

namespace App\DTO\Concerns;

use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonInterface;

trait MapsFromArray
{
    protected static function string(array $data, string $key, ?string $default = null): ?string
    {
        if (! array_key_exists($key, $data) || $data[$key] === null) {
            return $default;
        }

        return (string) $data[$key];
    }

    protected static function int(array $data, string $key, ?int $default = null): ?int
    {
        if (! array_key_exists($key, $data) || $data[$key] === null) {
            return $default;
        }

        return (int) $data[$key];
    }

    protected static function bool(array $data, string $key, bool $default = false): bool
    {
        if (! array_key_exists($key, $data)) {
            return $default;
        }

        return filter_var($data[$key], FILTER_VALIDATE_BOOL);
    }

    /**
     * @template T of BackedEnum
     *
     * @param  class-string<T>  $enumClass
     * @param  array<string, mixed>  $data
     * @return T
     */
    protected static function enum(array $data, string $key, string $enumClass, BackedEnum $default): BackedEnum
    {
        if (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
            return $default;
        }

        $value = $data[$key];

        if ($value instanceof BackedEnum) {
            return $value;
        }

        return $enumClass::from($value);
    }

    protected static function carbon(array $data, string $key): ?CarbonInterface
    {
        if (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
            return null;
        }

        return Carbon::parse($data[$key]);
    }
}
