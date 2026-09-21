<?php

namespace App\Console\Commands;

use App\Application\Finance\ObligationSchedule;
use App\Domain\Contracts\Repositories\ObligationRepositoryInterface;
use App\Domain\Enums\NotificationMode;
use App\Models\PushSubscription;
use App\Models\UserSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendRemindersCommand extends Command
{
    protected $signature = 'notifications:send-reminders';

    protected $description = 'Send push reminders for payments, check-ins, and zone warnings';

    public function handle(
        ObligationRepositoryInterface $obligations,
        ObligationSchedule $schedule,
    ): int {
        $today = now()->startOfDay();
        $sent = 0;

        $settings = UserSetting::query()->with('user')->get();

        foreach ($settings as $setting) {
            if ($setting->notification_mode === NotificationMode::Quiet) {
                continue;
            }

            $userId = $setting->user_id;
            $messages = [];

            if ($setting->notification_mode !== NotificationMode::PaymentsOnly) {
                if ($this->isCheckInDue($setting, $today)) {
                    $messages[] = 'Пора уточнить баланс — это займёт 30 секунд';
                }
            }

            $userObligations = $obligations->listActiveForUser($userId);
            $nextPayment = $schedule->findNextUnpaid($userObligations, $today, []);

            if ($nextPayment !== null) {
                $daysUntil = max(0, (int) $today->diffInDays($nextPayment['due_date'], false));
                if ($daysUntil <= 3) {
                    $messages[] = sprintf(
                        'Платёж «%s» через %d дн. — %s ₽',
                        $nextPayment['obligation']->title,
                        $daysUntil,
                        number_format((float) $nextPayment['amount'], 0, '.', ' '),
                    );
                }
            }

            foreach ($messages as $body) {
                if ($this->dispatchPush($userId, $body)) {
                    $sent++;
                }
            }
        }

        $this->info("Queued {$sent} reminder(s).");

        return self::SUCCESS;
    }

    private function isCheckInDue(UserSetting $settings, $today): bool
    {
        if ($settings->last_check_in_at === null) {
            return true;
        }

        return $settings->last_check_in_at->copy()->startOfDay()->diffInDays($today) >= 7;
    }

    private function dispatchPush(int $userId, string $body): bool
    {
        $subscriptions = PushSubscription::query()->where('user_id', $userId)->get();

        if ($subscriptions->isEmpty()) {
            return false;
        }

        foreach ($subscriptions as $subscription) {
            Log::info('push.reminder', [
                'user_id' => $userId,
                'endpoint' => $subscription->endpoint,
                'body' => $body,
            ]);
        }

        return true;
    }
}
