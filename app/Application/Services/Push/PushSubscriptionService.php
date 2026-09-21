<?php

namespace App\Application\Services\Push;

use App\Application\Auth\CurrentUserResolver;
use App\Application\Services\BaseService;
use App\Models\PushSubscription;

final class PushSubscriptionService extends BaseService
{
    public function store(array $payload): array
    {
        $userId = $this->currentUserId();

        $subscription = PushSubscription::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'endpoint' => $payload['endpoint'],
            ],
            [
                'public_key' => $payload['keys']['p256dh'] ?? $payload['public_key'] ?? null,
                'auth_token' => $payload['keys']['auth'] ?? $payload['auth_token'] ?? null,
                'content_encoding' => $payload['content_encoding'] ?? 'aesgcm',
            ],
        );

        return [
            'id' => $subscription->id,
            'endpoint' => $subscription->endpoint,
        ];
    }

    public function destroy(string $endpoint): void
    {
        PushSubscription::query()
            ->where('user_id', $this->currentUserId())
            ->where('endpoint', $endpoint)
            ->delete();
    }
}
