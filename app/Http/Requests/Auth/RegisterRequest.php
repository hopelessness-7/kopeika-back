<?php

namespace App\Http\Requests\Auth;

use App\DTO\Auth\RegisterData;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends BaseRequest
{
    protected function dtoClass(): string
    {
        return RegisterData::class;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    protected function defaultDtoAttributes(): array
    {
        return [];
    }
}
