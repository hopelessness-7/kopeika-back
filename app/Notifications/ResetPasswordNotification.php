<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $token,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim(config('app.frontend_url'), '/').'/reset-password?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Сброс пароля — '.config('app.name'))
            ->line('Вы запросили сброс пароля для Kopeika.')
            ->action('Сбросить пароль', $url)
            ->line('Ссылка действует '.config('auth.passwords.users.expire').' минут.')
            ->line('Если вы не запрашивали сброс, просто проигнорируйте это письмо.');
    }
}
