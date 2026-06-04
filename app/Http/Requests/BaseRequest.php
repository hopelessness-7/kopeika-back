<?php

namespace App\Http\Requests;

use App\Application\Auth\CurrentUserResolver;
use App\DTO\Contracts\DataTransferObject;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    abstract protected function dtoClass(): string;

    public function getDto(): DataTransferObject
    {
        $class = $this->dtoClass();

        return $class::fromArray($this->dtoPayload());
    }

    protected function dtoPayload(): array
    {
        return array_merge(
            $this->validated(),
            $this->defaultDtoAttributes(),
        );
    }

    protected function defaultDtoAttributes(): array
    {
        return [
            'user_id' => $this->resolveUserId(),
        ];
    }

    protected function resolveUserId(): int
    {
        return app(CurrentUserResolver::class)->id();
    }
}
