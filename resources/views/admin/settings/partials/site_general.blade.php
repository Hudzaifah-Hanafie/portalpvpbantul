<div class="card shadow-sm border-0 col-lg-11 mx-auto mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">Pengaturan Beranda & Footer</h5>
            <small class="text-muted">Atur bagian utama beranda, gambar, tombol ajakan, dan footer.</small>
        </div>
        @if(session('success')) <span class="badge bg-success bg-opacity-75 text-dark">Tersimpan</span> @endif
    </div>
    <div class="card-body">
        <form action="{{ route('admin.settings.site.update') }}" method="POST" enctype="multipart/form-data" class="vstack gap-4">
            @csrf
            @method('PUT')
            <div class="alert alert-info py-2 px-3 d-flex align-items-center gap-2 mb-0">
                <i class="fa-solid fa-circle-info text-primary"></i>
                <div class="small mb-0">Batas unggah gambar: maks 2MB per file (JPG/PNG/WebP). Gunakan kompresi bila ukuran lebih besar.</div>
            </div>
            <div class="alert alert-warning d-none mt-2 py-2 px-3 small" id="uploadSizeAlert"></div>


            <div class="border rounded-3 p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Bagian Utama Beranda</h6>
                        <small class="text-muted">Judul pembuka, deskripsi singkat, tombol ajakan, dan gambar utama.</small>
                    </div>
                    @if(!empty($settings['home_hero_image']))
                        <img src="{{ $settings['home_hero_image'] }}" alt="Hero" class="rounded-3 border" style="height:60px; object-fit:cover;">
                    @endif
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Judul pembuka</label>
                        <input type="text" name="home_hero_title" class="form-control" value="{{ $settings['home_hero_title'] ?? '' }}" placeholder="Tingkatkan Potensi Dirimu...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Deskripsi singkat</label>
                        <input type="text" name="home_hero_subtitle" class="form-control" value="{{ $settings['home_hero_subtitle'] ?? '' }}" placeholder="Program pelatihan vokasi...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gambar utama (URL)</label>
                        <input type="text" name="home_hero_image" class="form-control" value="{{ $settings['home_hero_image'] ?? '' }}" placeholder="https://.../hero.jpg">
                        <small class="text-muted">Atau unggah file di bawah.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unggah gambar utama (maks 2MB)</label>
                        <input type="file" name="home_hero_image_upload" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tombol utama (teks)</label>
                        <input type="text" name="home_hero_cta_primary_text" class="form-control" value="{{ $settings['home_hero_cta_primary_text'] ?? '' }}" placeholder="Daftar Pelatihan">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tombol utama (tautan)</label>
                        <input type="text" name="home_hero_cta_primary_link" class="form-control" value="{{ $settings['home_hero_cta_primary_link'] ?? '' }}" placeholder="https://...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tombol kedua (teks)</label>
                        <input type="text" name="home_hero_cta_secondary_text" class="form-control" value="{{ $settings['home_hero_cta_secondary_text'] ?? '' }}" placeholder="Baca Selengkapnya">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tombol kedua (tautan)</label>
                        <input type="text" name="home_hero_cta_secondary_link" class="form-control" value="{{ $settings['home_hero_cta_secondary_link'] ?? '' }}" placeholder="{{ route('program') }}">
                    </div>
                </div>
            </div>

            <div class="border rounded-3 p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Section Benefit (gradasi biru)</h6>
                        <small class="text-muted">Judul dan ilustrasi di section benefit.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('admin.benefit.index') }}" class="btn btn-sm btn-outline-primary">Kelola daftar benefit</a>
                        @if(!empty($settings['home_benefit_image']))
                            <img src="{{ $settings['home_benefit_image'] }}" alt="Benefit" class="rounded-3 border" style="height:60px; object-fit:cover;">
                        @endif
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Judul</label>
                        <input type="text" name="home_benefit_title" class="form-control" value="{{ $settings['home_benefit_title'] ?? '' }}" placeholder="Kenapa Harus Ikut Pelatihan ...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gambar (URL)</label>
                        <input type="text" name="home_benefit_image" class="form-control" value="{{ $settings['home_benefit_image'] ?? '' }}" placeholder="https://.../benefit.jpg">
                        <small class="text-muted">Atau unggah file di bawah.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unggah Gambar (maks 2MB)</label>
                        <input type="file" name="home_benefit_image_upload" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="border rounded-3 p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Section Program</h6>
                        <small class="text-muted">Heading untuk blok daftar program.</small>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Judul</label>
                        <input type="text" name="home_program_title" class="form-control" value="{{ $settings['home_program_title'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sub Judul</label>
                        <input type="text" name="home_program_subtitle" class="form-control" value="{{ $settings['home_program_subtitle'] ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="border rounded-3 p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Section Kenapa (Why)</h6>
                        <small class="text-muted">Judul dan ilustrasi blok kenapa memilih Satpel.</small>
                    </div>
                    @if(!empty($settings['home_why_image']))
                        <img src="{{ $settings['home_why_image'] }}" alt="Why" class="rounded-3 border" style="height:60px; object-fit:cover;">
                    @endif
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Judul</label>
                        <input type="text" name="home_why_title" class="form-control" value="{{ $settings['home_why_title'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gambar (URL)</label>
                        <input type="text" name="home_why_image" class="form-control" value="{{ $settings['home_why_image'] ?? '' }}" placeholder="https://.../why.jpg">
                        <small class="text-muted">Atau unggah file di bawah.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unggah Gambar (maks 2MB)</label>
                        <input type="file" name="home_why_image_upload" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="border rounded-3 p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Section Alur Pelatihan</h6>
                        <small class="text-muted">Judul dan ilustrasi alur pendaftaran.</small>
                    </div>
                    @if(!empty($settings['home_flow_image']))
                        <img src="{{ $settings['home_flow_image'] }}" alt="Flow" class="rounded-3 border" style="height:60px; object-fit:cover;">
                    @endif
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Judul</label>
                        <input type="text" name="home_flow_title" class="form-control" value="{{ $settings['home_flow_title'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gambar (URL)</label>
                        <input type="text" name="home_flow_image" class="form-control" value="{{ $settings['home_flow_image'] ?? '' }}" placeholder="https://.../flow.jpg">
                        <small class="text-muted">Atau unggah file di bawah.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Unggah Gambar (maks 2MB)</label>
                        <input type="file" name="home_flow_image_upload" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="border rounded-3 p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Judul/Subjudul Section Lain</h6>
                        <small class="text-muted">Berita, testimoni, partner, instruktur, dan galeri.</small>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Berita - Judul</label>
                        <input type="text" name="home_news_title" class="form-control" value="{{ $settings['home_news_title'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Berita - Sub Judul</label>
                        <input type="text" name="home_news_subtitle" class="form-control" value="{{ $settings['home_news_subtitle'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Testimoni - Judul</label>
                        <input type="text" name="home_testimonial_title" class="form-control" value="{{ $settings['home_testimonial_title'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Testimoni - Sub Judul</label>
                        <input type="text" name="home_testimonial_subtitle" class="form-control" value="{{ $settings['home_testimonial_subtitle'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Partner - Judul</label>
                        <input type="text" name="home_partner_title" class="form-control" value="{{ $settings['home_partner_title'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Partner - Sub Judul</label>
                        <input type="text" name="home_partner_subtitle" class="form-control" value="{{ $settings['home_partner_subtitle'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instruktur - Judul</label>
                        <input type="text" name="home_instructor_title" class="form-control" value="{{ $settings['home_instructor_title'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instruktur - Sub Judul</label>
                        <input type="text" name="home_instructor_subtitle" class="form-control" value="{{ $settings['home_instructor_subtitle'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Galeri - Judul</label>
                        <input type="text" name="home_gallery_title" class="form-control" value="{{ $settings['home_gallery_title'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Galeri - Sub Judul</label>
                        <input type="text" name="home_gallery_subtitle" class="form-control" value="{{ $settings['home_gallery_subtitle'] ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="border rounded-3 p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">CTA "Tunggu Apalagi"</h6>
                        <small class="text-muted">CTA di bagian bawah beranda.</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" name="cta_title" class="form-control" value="{{ $settings['cta_title'] ?? '' }}" placeholder="Tunggu Apalagi? Yuk Langsung Daftar Kelasnya">
                </div>
                <div class="mb-3">
                    <label class="form-label">Sub Judul</label>
                    <textarea name="cta_subtitle" rows="2" class="form-control">{{ $settings['cta_subtitle'] ?? '' }}</textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tombol 1 Teks</label>
                        <input type="text" name="cta_button_1_text" class="form-control" value="{{ $settings['cta_button_1_text'] ?? '' }}" placeholder="Daftar Pelatihan">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tautan Tombol 1</label>
                        <input type="text" name="cta_button_1_link" class="form-control" value="{{ $settings['cta_button_1_link'] ?? '' }}" placeholder="https://...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tombol 2 Teks</label>
                        <input type="text" name="cta_button_2_text" class="form-control" value="{{ $settings['cta_button_2_text'] ?? '' }}" placeholder="Hubungi Kami">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tautan Tombol 2</label>
                        <input type="text" name="cta_button_2_link" class="form-control" value="{{ $settings['cta_button_2_link'] ?? '' }}" placeholder="mailto:...">
                    </div>
                </div>
            </div>

            <div class="border rounded-3 p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Footer / Kantor Pusat</h6>
                        <small class="text-muted">Alamat, kontak, sosial, dan embed peta.</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="footer_address" rows="2" class="form-control">{{ $settings['footer_address'] ?? '' }}</textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="text" name="footer_email" class="form-control" value="{{ $settings['footer_email'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telp</label>
                        <input type="text" name="footer_phone" class="form-control" value="{{ $settings['footer_phone'] ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telp Alternatif</label>
                        <input type="text" name="footer_phone_alt" class="form-control" value="{{ $settings['footer_phone_alt'] ?? '' }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Jam Operasional</label>
                        <textarea name="footer_operasional" rows="2" class="form-control" placeholder="Jam Operasional: ...">{{ $settings['footer_operasional'] ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instagram</label>
                        <input type="text" name="footer_instagram" class="form-control" value="{{ $settings['footer_instagram'] ?? '' }}" placeholder="https://instagram.com/...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Facebook</label>
                        <input type="text" name="footer_facebook" class="form-control" value="{{ $settings['footer_facebook'] ?? '' }}" placeholder="https://facebook.com/...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Twitter / X</label>
                        <input type="text" name="footer_twitter" class="form-control" value="{{ $settings['footer_twitter'] ?? '' }}" placeholder="https://twitter.com/...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">YouTube</label>
                        <input type="text" name="footer_youtube" class="form-control" value="{{ $settings['footer_youtube'] ?? '' }}" placeholder="https://youtube.com/...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tautan SP4N / Lapor</label>
                        <input type="text" name="footer_sp4n" class="form-control" value="{{ $settings['footer_sp4n'] ?? '' }}">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">Embed Map (iframe)</label>
                    <textarea name="footer_embed_map" rows="3" class="form-control" placeholder="<iframe ...>">{{ $settings['footer_embed_map'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success px-4">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    (function () {
        const MAX_BYTES = 2 * 1024 * 1024;
        const form = document.querySelector('form[enctype="multipart/form-data"]');
        if (!form) return;

        const showAlert = (message) => {
            const alertBox = document.getElementById('uploadSizeAlert');
            if (!alertBox) return alert(message);
            alertBox.textContent = message;
            alertBox.classList.remove('d-none');
            alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => alertBox.classList.add('d-none'), 5000);
        };

        form.addEventListener('change', (e) => {
            const input = e.target;
            if (input.type !== 'file' || !input.files?.length) return;

            const oversized = Array.from(input.files).find((file) => file.size > MAX_BYTES);
            if (!oversized) return;

            showAlert(`Ukuran file melebihi 2MB: ${oversized.name}`);
            input.value = '';
        });
    })();
</script>
@endpush
