<?php

namespace App\Application\Services\Push;

use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushSenderService
{
    public function send(PushSubscription $sub, string $title, string $body): bool
    {
        try {
            $webPush = $this->client();

            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'publicKey' => $sub->public_key,
                'authToken' => $sub->auth_token,
                'contentEncoding' => $sub->content_encoding ?? 'aesgcm',
            ]);

            $webPush->sendOneNotification($subscription, json_encode([
                "title" => $title,
                "body" => $body,
                "data" => ["url" => "/"],
            ]));

            $result = false;

            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    $result = true;
                } elseif ($report->isSubscriptionExpired()) {
                    $sub->delete();
                } else {
                    \Log::warning('webpush.send_rejected', [
                        'reason' => $report->getReason(),
                    ]);
                }
            }

            return $result;
        } catch (\Throwable $e) {
            \Log::warning('webpush.send_failed', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * @throws \ErrorException
     */
    private function client(): WebPush
    {
        return new WebPush([
            'VAPID' => [
                'subject' => config('webpush.vapid.subject'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ]
        ]);
    }
}
