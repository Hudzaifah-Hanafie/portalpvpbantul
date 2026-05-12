<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteSetting;
use App\Models\PpidSetting;
use App\Models\PpidHighlight;
use App\Models\PpidRequest;
use App\Http\Controllers\Concerns\HasMathCaptcha;

class PpidController extends Controller
{
    use HasMathCaptcha;
    
    private const PPID_CAPTCHA_KEY = 'ppid_form_captcha';

    public function ppid()
    {
        $settings = SiteSetting::pluck('value', 'key');
        $pageSetting = PpidSetting::first() ?? new PpidSetting([
            'hero_title' => 'Profil PPID',
            'hero_description' => 'Pejabat Pengelola Informasi dan Dokumentasi Satpel PVP Bantul.',
            'hero_button_text' => 'Lihat Selengkapnya',
            'hero_button_link' => '#form',
            'profile_title' => 'Profil PPID',
            'profile_description' => 'PPID bertugas memastikan pelayanan informasi publik berjalan sesuai prinsip transparansi dan akuntabilitas.',
            'form_title' => 'Permohonan Informasi Publik',
            'form_description' => 'Isi formulir berikut untuk mengajukan permohonan informasi.',
        ]);
        $highlights = PpidHighlight::where('is_active', true)->orderBy('urutan')->get();
        $captcha = $this->prepareCaptcha(self::PPID_CAPTCHA_KEY);

        return view('ppid', [
            'setting' => $pageSetting,
            'highlights' => $highlights,
            'settings' => $settings,
            'captchaQuestion' => $captcha['question'],
        ]);
    }

    public function storePpidRequest(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_identitas' => 'required|digits_between:8,20',
            'npwp' => 'nullable|string|max:255',
            'pekerjaan' => 'required|string|max:255',
            'jenis_pemohon' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'no_hp' => 'required|regex:/^[0-9+]{8,20}$/',
            'email' => 'required|email|max:255',
            'informasi_dimohon' => 'required|string',
            'tujuan_penggunaan' => 'nullable|string',
            'cara_memperoleh' => 'nullable|string',
            'tanda_tangan' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'captcha_answer' => 'required|numeric',
        ]);

        $this->validateCaptcha($request, self::PPID_CAPTCHA_KEY, 'captcha_answer');

        if ($request->hasFile('tanda_tangan')) {
            $data['tanda_tangan'] = $request->file('tanda_tangan')->store('ppid/signatures', 'local');
        }

        PpidRequest::create($data);

        return redirect()->route('ppid')->with('success', 'Permohonan informasi publik berhasil dikirim.');
    }
}
