<?php

namespace App\DTO\Contracts;

interface DataTransferObject
{
    public static function fromArray(array $data): static;
}
