<?php

namespace App\DTO\Auth;

use App\DTO\Concerns\ArrayAccessible;
use App\DTO\Concerns\MapsFromArray;
use App\DTO\Contracts\DataTransferObject;

readonly class RegisterData implements DataTransferObject
{
    use ArrayAccessible;
    use MapsFromArray;

    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: (string) $data['name'],
            email: strtolower((string) $data['email']),
            password: (string) $data['password'],
        );
    }
}
