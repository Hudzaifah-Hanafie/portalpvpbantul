<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Berita;
use App\Models\Program;
use App\Models\Galeri;      
use App\Models\Pengumuman;  
use App\Models\Profile;
use App\Models\OrgStructure;
use App\Models\Partner;
use App\Models\Instructor;
use App\Models\Benefit;
use App\Models\FlowStep;
use App\Models\Testimonial;
use App\Models\SiteSetting;
use App\Models\TrainingService;
use App\Models\TrainingSchedule;
use App\Models\Empowerment;
use App\Models\Productivity;
use App\Models\JobVacancy;
use App\Models\CertificationContent;
use App\Models\CertificationScheme;
use App\Models\PublicationSetting;
use App\Models\PublicationCategory;
use App\Models\FaqSetting;
use App\Models\FaqCategory;
use App\Models\ContactSetting;
use App\Models\ContactChannel;
use App\Models\PpidSetting;
use App\Models\PpidHighlight;
use App\Models\PublicServiceSetting;
use App\Models\PublicServiceFlow;
use App\Models\PpidRequest;
use App\Models\AlumniTracer;
use App\Models\Alumni;
use App\Models\User;
use App\Http\Requests\AlumniTracerRequest;
use App\Mail\AlumniTracerSubmission;
use App\Mail\AlumniTracerVerified;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Mengambil 3 Berita Terbaru
        $beritaTerbaru = Berita::where('status', Berita::STATUS_PUBLISHED)->latest()->take(3)->get();

        // 2. Mengambil Semua Program Pelatihan
        $programs = Program::all();

        // 3. Mengambil 5 Pengumuman Terbaru yang terbit
        $latestAnnouncements = Pengumuman::where('status', Pengumuman::STATUS_PUBLISHED)
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // 4. Mengambil 8 Foto Galeri Terbaru
        $galeris = Galeri::where('status', 'published')->latest()->take(8)->get();

        $partners = Partner::where('is_active', true)->where('status', 'published')->orderBy('urutan')->get();
        $instructors = Instructor::where('is_active', true)->where('status', 'published')->orderBy('urutan')->get();
        $benefits = Benefit::where('is_active', true)->where('status', 'published')->orderBy('urutan')->get();
        $flowSteps = FlowStep::where('is_active', true)->orderBy('urutan')->get();
        $testimonials = Testimonial::where('is_active', true)->where('status', 'published')->orderBy('urutan')->get();
        $trainingServices = TrainingService::where('is_active', true)->where('status', 'published')->orderBy('urutan')->get();
        $settings = SiteSetting::pluck('value', 'key');

        // Mengirim semua data ke view 'home'
        return view('home', compact('beritaTerbaru', 'programs', 'latestAnnouncements', 'galeris', 'partners', 'instructors', 'benefits', 'flowSteps', 'testimonials', 'settings', 'trainingServices'));
    }

    public function katalogPelatihan()
    {
        $programs = Program::where('status', 'published')->latest()->paginate(12);
        return view('pelatihan.katalog', compact('programs'));
    }

    public function jadwalPelatihan()
    {
        $schedules = \App\Models\TrainingSchedule::where('is_active', true)
            ->orderBy('tahun', 'desc')
            ->orderByRaw("CASE 
                WHEN bulan='Januari' THEN 1 WHEN bulan='Februari' THEN 2 WHEN bulan='Maret' THEN 3 WHEN bulan='April' THEN 4 WHEN bulan='Mei' THEN 5 WHEN bulan='Juni' THEN 6 WHEN bulan='Juli' THEN 7 WHEN bulan='Agustus' THEN 8 WHEN bulan='September' THEN 9 WHEN bulan='Oktober' THEN 10 WHEN bulan='November' THEN 11 WHEN bulan='Desember' THEN 12 ELSE 99 END")
            ->orderBy('mulai')
            ->get();
        return view('pelatihan.jadwal', compact('schedules'));
    }

    public function pemberdayaan()
    {
        $empowerments = Empowerment::where('is_active', true)->where('status', 'published')->orderBy('urutan')->get();
        return view('pelatihan.pemberdayaan', compact('empowerments'));
    }

    public function produktivitas()
    {
        $productivities = Productivity::where('is_active', true)->orderBy('urutan')->get();
        return view('pelatihan.produktivitas', compact('productivities'));
    }

    public function beritaTerkini()
    {
        $categories = Berita::categories();
        $newsCollections = [];
        $orderColumn = Schema::hasColumn('beritas', 'published_at') ? 'published_at' : 'created_at';
        if (Schema::hasColumn('beritas', 'kategori')) {
            foreach ($categories as $key => $label) {
                $newsCollections[$key] = Berita::where('status', Berita::STATUS_PUBLISHED)
                    ->where('kategori', $key)
                    ->latest($orderColumn)
                    ->paginate(4, ['*'], $key . '_page');
            }
        } else {
            $newsCollections['berita'] = Berita::where('status', Berita::STATUS_PUBLISHED)
                ->latest($orderColumn)
                ->paginate(4, ['*'], 'berita_page');
        }
        $hero = Berita::where('status', Berita::STATUS_PUBLISHED)->latest($orderColumn)->first();
        $settings = SiteSetting::pluck('value', 'key');

        return view('berita.terkini', compact('categories', 'newsCollections', 'hero', 'settings'));
    }

    public function lowonganKerja()
    {
        $vacanciesQuery = Schema::hasTable('job_vacancies')
            ? JobVacancy::where('is_active', true)->where('status', 'published')->latest()
            : null;

        $vacancies = $vacanciesQuery?->paginate(9) ?? collect([]);
        $latestVacancy = $vacanciesQuery?->first();
        $settings = SiteSetting::pluck('value', 'key');
        return view('berita.lowongan', compact('vacancies', 'latestVacancy', 'settings'));
    }

    public function lowonganDetail(JobVacancy $lowongan)
    {
        if (! $lowongan->is_active || $lowongan->status !== 'published') {
            abort(404);
        }

        $relatedVacancies = JobVacancy::where('is_active', true)
            ->where('status', 'published')
            ->where('id', '!=', $lowongan->id)
            ->latest()
            ->take(3)
            ->get();
        $settings = SiteSetting::pluck('value', 'key');

        return view('berita.lowongan-detail', [
            'vacancy' => $lowongan,
            'relatedVacancies' => $relatedVacancies,
            'settings' => $settings,
        ]);
    }

    //

    public function sertifikasi()
    {
        $sections = CertificationContent::where('is_active', true)->where('status', 'published')->orderBy('urutan')->get()->groupBy('section');
        $schemes = CertificationScheme::where('is_active', true)->where('status', 'published')->orderBy('urutan')->get()->groupBy('category');
        $settings = SiteSetting::pluck('value', 'key');
        return view('sertifikasi.index', compact('sections', 'schemes', 'settings'));
    }

    public function resourceInfografis()
    {
        $settings = SiteSetting::pluck('value', 'key');
        $years = \App\Models\InfographicYear::with(['metrics' => function($q) {
                $q->where('is_active', true)->where('status', 'published')->orderBy('urutan');
            }, 'cards' => function($q) {
                $q->where('is_active', true)->where('status', 'published')->orderBy('urutan');
            }, 'embeds' => function($q) {
                $q->where('is_active', true)->where('status', 'published')->orderBy('urutan');
            }])
            ->where('is_active', true)
            ->where('status', 'published')
            ->orderBy('urutan')
            ->get();

        return view('resource.infografis', compact('years', 'settings'));
    }

    public function resourcePublikasi()
    {
        $setting = PublicationSetting::first() ?? new PublicationSetting();
        $categories = PublicationCategory::with(['items' => function ($query) {
                $query->where('is_active', true)->where('status', 'published')->orderBy('urutan');
            }])
            ->where('is_active', true)
            ->where('status', 'published')
            ->orderBy('urutan')
            ->get();

        return view('resource.publikasi', compact('setting', 'categories'));
    }

    public function resourcePelayanan()
    {
        $settings = SiteSetting::pluck('value', 'key');
        $pageSetting = PublicServiceSetting::first() ?? new PublicServiceSetting([
            'hero_title' => 'Pelayanan Publik',
            'hero_subtitle' => 'Pelayanan siap bantu seluruh layanan publik yang ramah, melayani sepenuh hati, dan responsif.',
            'hero_description' => 'Pelayanan unggulan disiapkan untuk mendukung masyarakat dalam mengakses layanan pelatihan, sertifikasi, serta layanan konsultatif lainnya.',
            'hero_button_text' => 'Unduh Maklumat',
            'hero_button_link' => '#maklumat',
        ]);

        $flows = PublicServiceFlow::where('is_active', true)
            ->where('status', 'published')
            ->orderBy('category')
            ->orderBy('urutan')
            ->get()
            ->groupBy('category');

        return view('resource.pelayanan', [
            'setting' => $pageSetting,
            'flows' => $flows,
            'settings' => $settings,
        ]);
    }

    public function resourceFaq()
    {
        $settings = SiteSetting::pluck('value', 'key');
        $faqSetting = FaqSetting::first() ?? new FaqSetting([
            'hero_title' => 'Frequently Asked Questions',
            'hero_subtitle' => 'Pertanyaan Populer',
            'hero_description' => 'Kami merangkum jawaban untuk pertanyaan yang paling sering diajukan terkait layanan pelatihan dan sertifikasi.',
            'contact_button_text' => 'Hubungi Kami',
            'contact_button_link' => route('kontak'),
        ]);

        $categories = FaqCategory::with(['items' => function ($query) {
                $query->where('is_active', true)->where('status', 'published')->orderBy('urutan');
            }])
            ->where('is_active', true)
            ->where('status', 'published')
            ->orderBy('urutan')
            ->get();

        return view('resource.faq', [
            'setting' => $faqSetting,
            'categories' => $categories,
            'settings' => $settings,
        ]);
    }

    public function resourceHubungi()
    {
        $settings = SiteSetting::pluck('value', 'key');
        $pageSetting = ContactSetting::first() ?? new ContactSetting([
            'hero_title' => 'Hubungi Kami',
            'hero_subtitle' => 'Hubungi kami dan kami siap membantu Anda!',
            'hero_description' => 'Tinggalkan pesan atau dapatkan informasi kontak resmi kami untuk kebutuhan kolaborasi dan layanan publik.',
            'hero_button_text' => 'Lihat Selengkapnya',
            'hero_button_link' => '#kontak',
            'map_title' => 'Temukan Kami',
            'map_description' => 'Kunjungi kami di Satpel PVP Bantul atau hubungi tim layanan untuk penjadwalan kunjungan.',
            'info_section_title' => 'Layanan Informasi Untuk Anda',
            'info_section_description' => 'Saluran informasi terpercaya untuk memenuhi kebutuhan Anda.',
            'cta_title' => 'Anda Siap Tingkatkan Skill dengan Kami?',
            'cta_description' => 'Pilih topik pelatihan sesuai minatmu dan segera pelajari materinya untuk menguasai keahlian yang kamu butuhkan.',
            'cta_primary_text' => 'Cek Topik & Jadwal Kelasnya',
            'cta_primary_link' => route('pelatihan.katalog'),
            'cta_secondary_text' => 'Tanya via WhatsApp',
            'cta_secondary_link' => 'https://wa.me/6281234567890',
        ]);

        $channels = ContactChannel::where('is_active', true)->where('status', 'published')->orderBy('urutan')->get();
        $captcha = $this->prepareCaptcha(self::CONTACT_CAPTCHA_KEY);

        return view('resource.hubungi', [
            'setting' => $pageSetting,
            'channels' => $channels,
            'captchaQuestion' => $captcha['question'],
            'settings' => $settings,
        ]);
    }

    // --- Fungsi Detail Lainnya (Biarkan tetap ada) ---
    
    public function showBerita($slug)
    {
        $berita = Berita::where('slug', $slug)->where('status', Berita::STATUS_PUBLISHED)->firstOrFail();
        $beritaLain = Berita::where('id', '!=', $berita->id)->where('status', Berita::STATUS_PUBLISHED)->latest()->take(5)->get();
        return view('detail_berita', compact('berita', 'beritaLain'));
    }

    public function showProgram($id)
    {
        $program = Program::where('status', 'published')->findOrFail($id);
        return view('detail_program', compact('program'));
    }

    public function kontak()
    {
        $captcha = $this->prepareCaptcha(self::CONTACT_CAPTCHA_KEY);

        return view('kontak', [
            'captchaQuestion' => $captcha['question'],
        ]);
    }

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
        $highlights = PpidHighlight::where('is_active', true)->where('status', 'published')->orderBy('urutan')->get();
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
            // simpan di storage privat agar tidak bisa diakses langsung
            $data['tanda_tangan'] = $request->file('tanda_tangan')->store('ppid/signatures');
        }

        PpidRequest::create($data);

        return redirect()->route('ppid')->with('success', 'Permohonan informasi publik berhasil dikirim.');
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

        $data['is_active'] = true;
        unset($data['captcha_answer']);

        Alumni::updateOrCreate(
            ['email' => $data['email']],
            $data
        );

        return redirect()->route('alumni.profile.complete')->with('success', 'Profil alumni Anda telah diperbarui.');
    }

    public function search(Request $request)
    {
        // 1. Ambil kata kunci dari input form (name="q")
        $keyword = $request->input('q');

        // Jika keyword kosong, kembalikan ke home atau halaman kosong
        if (!$keyword) {
            return redirect()->route('home');
        }

        // 2. Cari di Tabel BERITA (Judul ATAU Konten yang mengandung keyword)
        $beritaResults = Berita::where('status', Berita::STATUS_PUBLISHED)
                            ->where(function($query) use ($keyword) {
                                $query->where('judul', 'LIKE', "%{$keyword}%")
                                      ->orWhere('konten', 'LIKE', "%{$keyword}%");
                            })
                            ->latest()
                            ->get();

        // 3. Cari di Tabel PROGRAM (Judul ATAU Deskripsi yang mengandung keyword)
        $programResults = Program::where('status', 'published')
                            ->where(function($query) use ($keyword) {
                                $query->where('judul', 'LIKE', "%{$keyword}%")
                                      ->orWhere('deskripsi', 'LIKE', "%{$keyword}%");
                            })
                            ->get();

        // 4. Cari di Tabel PENGUMUMAN (Opsional, agar makin lengkap)
        $pengumumanResults = Pengumuman::where('status', Pengumuman::STATUS_PUBLISHED)
                            ->where(function($query) use ($keyword) {
                                $query->where('judul', 'LIKE', "%{$keyword}%")
                                      ->orWhere('isi', 'LIKE', "%{$keyword}%");
                            })
                            ->latest()
                            ->get();

        // 5. Kirim semua hasil ke View
        return view('search_results', compact('keyword', 'beritaResults', 'programResults', 'pengumumanResults'));
    }

    // Jangan lupa: use App\Models\Profile;

    public function sejarah()
    {
        // Ambil data dimana key = 'sejarah'
        $data = Profile::where('key', 'sejarah')->firstOrFail();
        return view('profil.sejarah', compact('data'));
    }

    public function visiMisi()
    {
        $data = Profile::where('key', 'visi_misi')->firstOrFail();
        return view('profil.visi_misi', compact('data'));
    }

    public function struktur()
    {
        $data = Profile::where('key', 'struktur')->firstOrFail();
        $structures = OrgStructure::with('children.children')->whereNull('parent_id')->orderBy('urutan')->get();
        return view('profil.struktur', compact('data', 'structures'));
    }

    public function profilInstansi()
    {
        $profilInstansi = Profile::firstOrCreate(
            ['key' => 'profil_instansi'],
            [
                'judul' => 'Profil Satpel PVP Bantul',
                'konten' => '<p>Satpel PVP Bantul menjadi pusat pelatihan vokasi dengan fasilitas lengkap dan dukungan instruktur profesional. Halaman ini akan diperbarui oleh admin melalui menu Profil.</p>',
            ]
        );
        $selayang = Profile::firstOrCreate(
            ['key' => 'profil_selayang'],
            [
                'judul' => 'Selayang Pandang',
                'konten' => '<p>Tambahkan selayang pandang Satpel PVP Bantul melalui panel admin.</p>',
            ]
        );
        $denah = Profile::firstOrCreate(
            ['key' => 'profil_denah'],
            [
                'judul' => 'Denah Lokasi',
                'konten' => '<p>Upload denah lokasi dengan mengisi konten profil.</p>',
            ]
        );
        $sejarah = Profile::firstOrCreate(
            ['key' => 'sejarah'],
            [
                'judul' => 'Sejarah Satpel PVP Bantul',
                'konten' => '<p>Tambahkan narasi sejarah lembaga melalui panel admin.</p>',
            ]
        );
        $strukturProfile = Profile::firstOrCreate(
            ['key' => 'struktur'],
            [
                'judul' => 'Struktur Organisasi',
                'konten' => '<p>Mewujudkan tata kelola Satpel PVP Bantul yang profesional & kolaboratif.</p>',
            ]
        );
        $visiMisi = Profile::where('key', 'visi_misi')->first();
        $structures = OrgStructure::with('children.children')->whereNull('parent_id')->orderBy('urutan')->get();
        $galeris = Galeri::where('status', 'published')->latest()->take(6)->get();
        $settings = SiteSetting::pluck('value', 'key');

        return view('profil.instansi', compact('profilInstansi', 'selayang', 'visiMisi', 'structures', 'galeris', 'denah', 'settings', 'sejarah', 'strukturProfile'));
    }

    public function profilInstruktur()
    {
        $instructors = Instructor::where('is_active', true)->where('status', 'published')->orderBy('urutan')->get();
        $settings = SiteSetting::pluck('value', 'key');
        return view('profil.instruktur', compact('instructors', 'settings'));
    }

    public function alumniTracerForm()
    {
        $programs = Program::where('status', 'published')->orderBy('judul')->get();
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

        $programName = $request->input('program_name') ?: optional(Program::where('status', 'published')->find($request->input('program_id')))->judul;
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

        if ($alumniTracer->email) {
            Mail::to($alumniTracer->email)->send(new AlumniTracerSubmission($alumniTracer));
        }

        return redirect()->route('alumni.tracer')->with('success', 'Terima kasih, data tracer telah tersimpan. Kami juga mengirimkan konfirmasi via email jika Anda memberikan alamat.');
    }

    private const CONTACT_CAPTCHA_KEY = 'contact_form_captcha';
    private const TRACER_CAPTCHA_KEY = 'tracer_form_captcha';
    private const PPID_CAPTCHA_KEY = 'ppid_form_captcha';

    private function prepareCaptcha(string $sessionKey, bool $forceNew = false): array
    {
        if ($forceNew || !session()->has($sessionKey)) {
            session([$sessionKey => $this->buildCaptchaPayload()]);
        }

        return session($sessionKey);
    }

    private function buildCaptchaPayload(): array
    {
        $first = random_int(3, 9);
        $second = random_int(1, 9);
        $operator = random_int(0, 1) === 1 ? '+' : '-';

        if ($operator === '-' && $second > $first) {
            [$first, $second] = [$second, $first];
        }

        $answer = $operator === '+' ? $first + $second : $first - $second;

        return [
            'question' => sprintf('%d %s %d = ?', $first, $operator, $second),
            'answer' => $answer,
        ];
    }

    private function validateCaptcha(Request $request, string $sessionKey, string $fieldName = 'captcha_answer'): void
    {
        $captcha = $this->prepareCaptcha($sessionKey);
        $answer = trim((string) $request->input($fieldName));

        if ($answer === '' || (int) $answer !== (int) $captcha['answer']) {
            $this->prepareCaptcha($sessionKey, true);

            throw ValidationException::withMessages([
                $fieldName => 'Jawaban keamanan tidak sesuai. Silakan coba lagi.',
            ]);
        }

        session()->forget($sessionKey);
    }

    public function pengumumanIndex(Request $request)
    {
        $search = trim($request->input('q', ''));

        $announcements = Pengumuman::where('status', Pengumuman::STATUS_PUBLISHED)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('judul', 'ILIKE', "%{$search}%")
                        ->orWhere('isi', 'ILIKE', "%{$search}%");
                });
            })
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at')
            ->paginate(9)
            ->withQueryString();

        $recent = Pengumuman::where('status', Pengumuman::STATUS_PUBLISHED)
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('pengumuman.index', compact('announcements', 'recent', 'search'));
    }

    public function pengumumanShow(string $slug)
    {
        $announcement = Pengumuman::where('slug', $slug)
            ->where('status', Pengumuman::STATUS_PUBLISHED)
            ->firstOrFail();

        $recent = Pengumuman::where('status', Pengumuman::STATUS_PUBLISHED)
            ->whereKeyNot($announcement->id)
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('pengumuman.show', compact('announcement', 'recent'));
    }

}
