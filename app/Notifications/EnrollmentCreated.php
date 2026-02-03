<?php

namespace App\Notifications;

use App\Models\CourseEnrollment;
use App\Support\EmailSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentCreated extends Notification implements ShouldQueue
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
            ->subject('Anda terdaftar di kelas ' . ($this->enrollment->course->title ?? ''))
            ->line('Anda telah didaftarkan ke kelas: ' . ($this->enrollment->course->title ?? '-'))
            ->action('Lihat Kelas', url('/my/classes'))
            ->line('Silakan cek tugas dan sesi kelas Anda.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'enrollment_created',
            'course_class_id' => $this->enrollment->course_class_id,
            'course_title' => $this->enrollment->course->title ?? '',
        ];
    }
}
