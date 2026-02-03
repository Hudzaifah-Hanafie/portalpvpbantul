<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use App\Support\EmailSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCourseAnnouncement extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $classTitle,
        private string $announcementTitle
    ) {
    }

    public function via(object $notifiable): array
    {
        return EmailSettings::notificationsEnabled() ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Pengumuman Kelas Baru')
            ->greeting('Halo ' . ($notifiable->name ?? ''))
            ->line("Ada pengumuman baru di kelas {$this->classTitle}:")
            ->line($this->announcementTitle);
        $this->sendWebhook($notifiable, "Pengumuman baru di {$this->classTitle}: {$this->announcementTitle}");
        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'course_announcement',
            'class_title' => $this->classTitle,
            'announcement_title' => $this->announcementTitle,
            'message' => "Pengumuman baru di {$this->classTitle}: {$this->announcementTitle}",
        ];
    }

    private function sendWebhook(object $notifiable, string $text): void
    {
        foreach (['SMS_WEBHOOK_URL', 'WA_WEBHOOK_URL'] as $env) {
            $url = env($env);
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
