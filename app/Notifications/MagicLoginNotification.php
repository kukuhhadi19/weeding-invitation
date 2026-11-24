<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MagicLoginNotification extends Notification
{
    use Queueable;

    public function __construct(public string $url)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tautan Masuk ke Akun Anda - Wedding Invitation')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Kami menerima permintaan untuk masuk ke akun Anda tanpa kata sandi.')
            ->action('Masuk ke Dashboard', $this->url)
            ->line('Tautan ini hanya berlaku selama 15 menit dan hanya dapat digunakan satu kali.')
            ->line('Jika Anda tidak meminta tautan ini, silakan abaikan email ini.');
    }
}
