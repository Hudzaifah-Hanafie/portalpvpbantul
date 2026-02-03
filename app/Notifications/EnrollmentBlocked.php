<?php

namespace App\Notifications;

use App\Models\CourseEnrollment;
use App\Support\EmailSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentBlocked extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private CourseEnrollment $enrollment)
    {
    }

    public function via($notifiable): array
    {
        return EmailSettings::notificationsEnabled() ? ['mail', 'database'] : ['database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Status kelas Anda diperbarui')
            ->line('Akses Anda ke kelas ' . ($this->enrollment->course->title ?? '') . ' telah dibatasi.')
            ->line('Silakan hubungi admin atau instruktur jika membutuhkan bantuan.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'enrollment_blocked',
            'course_class_id' => $this->enrollment->course_class_id,
            'course_title' => $this->enrollment->course->title ?? '',
        ];
    }
}
