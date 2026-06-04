<?php

namespace App\Http\Controllers\Api;

use App\Application\Auth\CurrentUserResolver;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    protected function resolveUserId(): int
    {
        return app(CurrentUserResolver::class)->id();
    }

    protected function success(mixed $payload = null, int $status = 200): JsonResponse
    {
        return $this->respond($payload, $status);
    }

    protected function created(mixed $payload = null): JsonResponse
    {
        return $this->respond($payload, 201);
    }

    protected function respond(mixed $payload, int $status): JsonResponse
    {
        if ($payload instanceof Responsable) {
            return $payload->toResponse(request())->setStatusCode($status);
        }

        return response()->json($payload, $status);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function error(string $message, int $status = 400, ?array $errors = null): JsonResponse
    {
        $payload = ['message' => $message];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
