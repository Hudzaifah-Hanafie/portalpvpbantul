<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Controllers\Concerns\HasMathCaptcha;

class ContactController extends Controller
{
    use HasMathCaptcha;
    
    private const CONTACT_CAPTCHA_KEY = 'contact_form_captcha';

    public function kontak()
    {
        $captcha = $this->prepareCaptcha(self::CONTACT_CAPTCHA_KEY);

        return view('kontak', [
            'captchaQuestion' => $captcha['question'],
        ]);
    }

    public function storeKontak(Request $request)
    {
        $this->validate($request, [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subjek' => 'required|string|max:255',
            'pesan' => 'required|string|max:2000',
            'captcha_answer' => 'required|numeric',
        ]);

        $this->validateCaptcha($request, self::CONTACT_CAPTCHA_KEY, 'captcha_answer');

        \App\Models\Pesan::create($request->only(['nama', 'email', 'subjek', 'pesan']));

        return back()->with('success', 'Terima kasih! Pesan Anda telah kami terima.');
    }
}
