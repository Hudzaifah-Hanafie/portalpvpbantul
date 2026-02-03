<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Support\EmailSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForumReportResolved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $topicTitle,
        private ?string $courseTitle,
        private string $actionTaken = 'Ditandai selesai'
    ) {
    }

    public function via(object $notifiable): array
    {
        return EmailSettings::notificationsEnabled() ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Laporan Forum Diproses')
            ->greeting('Halo ' . ($notifiable->name ?? ''))
            ->line("Laporan Anda untuk topik \"{$this->topicTitle}\" sudah diproses.")
            ->line('Kelas: ' . ($this->courseTitle ?: '-'))
            ->line('Tindakan: ' . $this->actionTaken);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'forum_report_resolved',
            'topic_title' => $this->topicTitle,
            'course_title' => $this->courseTitle,
            'action_taken' => $this->actionTaken,
            'message' => "Laporan Anda untuk \"{$this->topicTitle}\" sudah diproses. Tindakan: {$this->actionTaken}.",
        ];
    }
}
