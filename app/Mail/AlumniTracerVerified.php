<?php

namespace App\Mail;

use App\Models\AlumniTracer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AlumniTracerVerified extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AlumniTracer $tracer)
    {
    }

    public function build()
    {
        return $this->subject('Tracer study Anda telah diverifikasi')
            ->view('emails.alumni_tracer_verified');
    }
}