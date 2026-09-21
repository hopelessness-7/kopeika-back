<?php

namespace App\Http\Controllers\Api;

use App\Application\Services\Push\PushSubscriptionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class PushSubscriptionController extends Controller
{
    public function __construct(
        private readonly PushSubscriptionService $subscriptions,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'keys' => ['sometimes', 'array'],
            'keys.p256dh' => ['required_with:keys', 'string'],
            'keys.auth' => ['required_with:keys', 'string'],
            'public_key' => ['nullable', 'string'],
            'auth_token' => ['nullable', 'string'],
            'content_encoding' => ['nullable', 'string', 'max:32'],
        ]);

        return response()->json($this->subscriptions->store($validated), 201);
    }

    public function destroy(Request $request): Response
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
        ]);

        $this->subscriptions->destroy($validated['endpoint']);

        return response()->noContent();
    }
}
