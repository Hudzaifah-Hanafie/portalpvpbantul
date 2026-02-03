<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Support\EmailSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class ForumUserMuted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $courseTitle,
        private Carbon $until,
        private ?string $topicTitle = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return EmailSettings::notificationsEnabled() ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pembatasan Sementara Forum Kelas')
            ->greeting('Halo ' . ($notifiable->name ?? ''))
            ->line('Anda dibatasi untuk membuat topik/balasan di forum kelas.')
            ->line('Kelas: ' . $this->courseTitle)
            ->line('Berlaku sampai: ' . $this->until->format('d M Y H:i'))
            ->line($this->topicTitle ? "Topik terkait: {$this->topicTitle}" : '');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'forum_user_muted',
            'course_title' => $this->courseTitle,
            'until' => $this->until->toDateTimeString(),
            'topic_title' => $this->topicTitle,
            'message' => "Anda dibatasi di forum {$this->courseTitle} hingga {$this->until->format('d M Y H:i')}.",
        ];
    }
}
