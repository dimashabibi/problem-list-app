<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Password Anda Telah Diubah')
            ->greeting('Halo!')
            ->line('Password akun Anda telah berhasil diubah.')
            ->line('Jika Anda tidak melakukan perubahan ini, segera hubungi administrator.')
            ->line('Demi keamanan, pastikan password Anda kuat dan tidak digunakan di situs lain.')
            ->salam('Hormat kami,');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'password_changed',
            'timestamp' => now()->toDateTimeString()
        ];
    }
}