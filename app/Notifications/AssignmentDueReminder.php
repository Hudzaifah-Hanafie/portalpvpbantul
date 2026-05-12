<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use App\Support\EmailSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentDueReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $assignmentTitle,
        private string $classTitle,
        private string $dueAt
    ) {
    }

    public function via(object $notifiable): array
    {
        return EmailSettings::notificationsEnabled() ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->sendWebhook($notifiable, "Pengingat tugas \"{$this->assignmentTitle}\" di {$this->classTitle} jatuh tempo {$this->dueAt}");
        return (new MailMessage)
            ->subject('Pengingat Tugas Mendekati Due Date')
            ->greeting('Halo ' . ($notifiable->name ?? ''))
            ->line("Tugas \"{$this->assignmentTitle}\" di kelas {$this->classTitle} akan jatuh tempo pada {$this->dueAt}.")
            ->line('Segera selesaikan sebelum batas waktu berakhir.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'assignment_due_reminder',
            'assignment_title' => $this->assignmentTitle,
            'class_title' => $this->classTitle,
            'due_at' => $this->dueAt,
            'message' => "Tugas \"{$this->assignmentTitle}\" akan jatuh tempo pada {$this->dueAt}.",
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
