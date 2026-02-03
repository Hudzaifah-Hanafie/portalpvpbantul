<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Support\EmailSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForumPostRemoved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $topicTitle,
        private ?string $courseTitle,
        private ?string $reason = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return EmailSettings::notificationsEnabled() ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Postingan Forum Dihapus')
            ->greeting('Halo ' . ($notifiable->name ?? ''))
            ->line("Postingan Anda pada topik \"{$this->topicTitle}\" telah dihapus oleh moderator.")
            ->line('Kelas: ' . ($this->courseTitle ?: '-'));

        if ($this->reason) {
            $mail->line('Alasan: ' . $this->reason);
        }

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'forum_post_removed',
            'topic_title' => $this->topicTitle,
            'course_title' => $this->courseTitle,
            'reason' => $this->reason,
            'message' => "Postingan Anda di \"{$this->topicTitle}\" telah dihapus." . ($this->reason ? " Alasan: {$this->reason}." : ''),
        ];
    }
}
