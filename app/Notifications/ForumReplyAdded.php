<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Support\EmailSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForumReplyAdded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $topicTitle,
        private ?string $courseTitle,
        private string $replierName
    ) {
    }

    public function via(object $notifiable): array
    {
        return EmailSettings::notificationsEnabled() ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Balasan Baru di Forum Kelas')
            ->greeting('Halo ' . ($notifiable->name ?? ''))
            ->line("{$this->replierName} membalas topik \"{$this->topicTitle}\".")
            ->line('Kelas: ' . ($this->courseTitle ?: '-'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'forum_reply',
            'topic_title' => $this->topicTitle,
            'course_title' => $this->courseTitle,
            'replier_name' => $this->replierName,
            'message' => "{$this->replierName} membalas topik {$this->topicTitle}.",
        ];
    }
}
