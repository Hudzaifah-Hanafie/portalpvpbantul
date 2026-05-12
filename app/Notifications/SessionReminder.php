<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use App\Support\EmailSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $sessionTitle,
        private string $classTitle,
        private string $startAt,
        private ?string $meetingLink = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return EmailSettings::notificationsEnabled() ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Pengingat Sesi Kelas')
            ->greeting('Halo ' . ($notifiable->name ?? ''))
            ->line("Sesi \"{$this->sessionTitle}\" di kelas {$this->classTitle} akan dimulai pada {$this->startAt}.");
        if ($this->meetingLink) {
            $mail->action('Gabung Sesi', $this->meetingLink);
        }
        $this->sendWebhook($notifiable, "Pengingat sesi {$this->sessionTitle} ({$this->classTitle}) pada {$this->startAt}" . ($this->meetingLink ? " - {$this->meetingLink}" : ''));
        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'session_reminder',
            'session_title' => $this->sessionTitle,
            'class_title' => $this->classTitle,
            'start_at' => $this->startAt,
            'meeting_link' => $this->meetingLink,
            'message' => "Sesi {$this->sessionTitle} akan dimulai pada {$this->startAt}.",
        ];
    }

    private function sendWebhook(object $notifiable, string $text): void
    {
        foreach (['sms', 'wa'] as $type) {
            $url = config("notifications.webhooks.{$type}");
            if (! $url) continue;
            try {
                Http::post($url, [
                    'to' => $notifiable->phone ?? $notifiable->email,
                    'message' => $text,
                ]);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
}
