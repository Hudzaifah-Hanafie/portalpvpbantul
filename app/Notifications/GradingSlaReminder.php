<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Support\EmailSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GradingSlaReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $assignmentTitle,
        private string $classTitle,
        private string $submittedAt,
        private int $slaHours
    ) {
    }

    public function via(object $notifiable): array
    {
        return EmailSettings::notificationsEnabled() ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengingat Penilaian Submission')
            ->greeting('Halo ' . ($notifiable->name ?? ''))
            ->line("Ada submission menunggu penilaian untuk tugas \"{$this->assignmentTitle}\" (kelas {$this->classTitle}).")
            ->line("Dikirim pada: {$this->submittedAt}. SLA penilaian: {$this->slaHours} jam.")
            ->line('Mohon lakukan penilaian agar SLA terpenuhi.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'grading_sla_reminder',
            'assignment_title' => $this->assignmentTitle,
            'class_title' => $this->classTitle,
            'submitted_at' => $this->submittedAt,
            'sla_hours' => $this->slaHours,
            'message' => "Submission tugas {$this->assignmentTitle} menunggu penilaian sejak {$this->submittedAt}.",
        ];
    }
}
