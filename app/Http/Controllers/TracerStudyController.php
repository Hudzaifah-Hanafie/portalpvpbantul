<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\AlumniTracer;
use App\Models\Alumni;
use App\Models\User;
use App\Http\Requests\AlumniTracerRequest;
use App\Mail\AlumniTracerSubmission;
use App\Support\EmailSettings;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Controllers\Concerns\HasMathCaptcha;

class TracerStudyController extends Controller
{
    use HasMathCaptcha;

    private const TRACER_CAPTCHA_KEY = 'tracer_form_captcha';
    private const CONTACT_CAPTCHA_KEY = 'contact_form_captcha'; 

    public function alumniTracerForm()
    {
        $programs = Program::published()->orderBy('judul')->get();
        $captcha = $this->prepareCaptcha(self::TRACER_CAPTCHA_KEY);

        return view('alumni.tracer', [
            'programs' => $programs,
            'captchaQuestion' => $captcha['question'],
        ]);
    }

    public function storeAlumniTracer(AlumniTracerRequest $request)
    {
        $this->validate($request, [
            'captcha_answer' => 'required|numeric',
        ]);

        $this->validateCaptcha($request, self::TRACER_CAPTCHA_KEY, 'captcha_answer');

        $email = $request->input('email');
        if ($email && AlumniTracer::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Email ini sudah pernah digunakan untuk tracer study.',
            ]);
        }

        $nationalId = $request->input('national_id');
        if ($nationalId && AlumniTracer::where('national_id', $nationalId)->exists()) {
            throw ValidationException::withMessages([
                'national_id' => 'Nomor identitas ini sudah tercatat, pastikan Anda tidak mengirim formulir ganda.',
            ]);
        }

        $programName = $request->input('program_name') ?: optional(Program::find($request->input('program_id')))->judul;
        $user = $email ? User::where('email', $email)->first() : null;

        $alumniTracer = AlumniTracer::create(array_merge(
            $request->validated(),
            [
                'program_name' => trim($programName ?? 'Belum terdaftar'),
                'platform_origin' => 'website',
                'consent_given' => true,
                'consent_at' => now(),
                'user_id' => $user?->id,
            ]
        ));

        if ($alumniTracer->email && EmailSettings::confirmationsEnabled()) {
            Mail::to($alumniTracer->email)->send(new AlumniTracerSubmission($alumniTracer));
        }

        return redirect()->route('alumni.tracer')->with('success', 'Terima kasih, data tracer telah tersimpan. Kami juga mengirimkan konfirmasi via email jika Anda memberikan alamat.');
    }

    public function alumniProfileForm()
    {
        $captcha = $this->prepareCaptcha(self::CONTACT_CAPTCHA_KEY);

        return view('alumni.profile', [
            'captchaQuestion' => $captcha['question'],
        ]);
    }

    public function storeAlumniProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:32',
            'field_of_study' => 'nullable|string|max:255',
            'graduation_year' => 'nullable|digits:4',
            'employment_status' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'captcha_answer' => 'required|numeric',
        ]);

        $this->validateCaptcha($request, self::CONTACT_CAPTCHA_KEY, 'captcha_answer');

        $data['email'] = Str::lower($data['email']);
        $existing = Alumni::where('email', $data['email'])->first();
        if ($existing) {
            return back()
                ->withErrors(['email' => 'Email ini sudah terdaftar. Untuk memperbarui data, silakan hubungi admin.'])
                ->withInput();
        }

        $data['is_active'] = true;
        unset($data['captcha_answer']);

        Alumni::create($data);

        return redirect()->route('alumni.profile.complete')->with('success', 'Profil alumni Anda telah diperbarui.');
    }
}
