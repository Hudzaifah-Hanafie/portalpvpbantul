<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Pengaturan FAQ</h4>
        <small class="text-muted">Atur hero, highlight informasi, dan CTA agar sesuai dengan template FAQ publik.</small>
    </div>
    <a href="{{ route('resource.faq') }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Halaman</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Validasi gagal.</strong> Pastikan teks tidak melebihi batas dan file mengikuti ketentuan (JPG/PNG ≤ 2 MB).
    </div>
@endif

<form action="{{ route('admin.faq.settings.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded shadow-sm p-4" novalidate>
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-6">
            <h5 class="fw-bold">Hero Section</h5>
            <div class="mb-3">
                <label class="form-label">Judul</label>
                <input type="text" name="hero_title" class="form-control @error('hero_title') is-invalid @enderror" value="{{ old('hero_title', $setting->hero_title) }}" maxlength="255">
                @error('hero_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Subjudul</label>
                <input type="text" name="hero_subtitle" class="form-control @error('hero_subtitle') is-invalid @enderror" value="{{ old('hero_subtitle', $setting->hero_subtitle) }}" maxlength="255">
                @error('hero_subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="hero_description" class="form-control @error('hero_description') is-invalid @enderror" rows="4">{{ old('hero_description', $setting->hero_description) }}</textarea>
                @error('hero_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Teks Tombol</label>
                    <input type="text" name="hero_button_text" class="form-control @error('hero_button_text') is-invalid @enderror" value="{{ old('hero_button_text', $setting->hero_button_text) }}" maxlength="120">
                    @error('hero_button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Link Tombol</label>
                    <input type="text" name="hero_button_link" class="form-control @error('hero_button_link') is-invalid @enderror" value="{{ old('hero_button_link', $setting->hero_button_link) }}" maxlength="255">
                    @error('hero_button_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Gambar Hero</label>
                <input type="file" name="hero_image" class="form-control @error('hero_image') is-invalid @enderror" accept=".jpg,.jpeg,.png">
                <small class="text-muted d-block">Format JPG/PNG dengan ukuran maksimal 2 MB.</small>
                @error('hero_image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                @if($setting->hero_image)
                    <div class="mt-2">
                        <img src="{{ asset($setting->hero_image) }}" alt="Hero FAQ" class="img-fluid rounded shadow-sm" style="max-height:180px;object-fit:cover;">
                        <p class="text-muted small mb-0">Saat ini: {{ $setting->hero_image }}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <h5 class="fw-bold">Intro & Info</h5>
            <div class="mb-3">
                <label class="form-label">Judul Intro</label>
                <input type="text" name="intro_title" class="form-control @error('intro_title') is-invalid @enderror" value="{{ old('intro_title', $setting->intro_title) }}" maxlength="255">
                @error('intro_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi Intro</label>
                <textarea name="intro_description" class="form-control @error('intro_description') is-invalid @enderror" rows="4">{{ old('intro_description', $setting->intro_description) }}</textarea>
                @error('intro_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Judul Info</label>
                <input type="text" name="info_title" class="form-control @error('info_title') is-invalid @enderror" value="{{ old('info_title', $setting->info_title) }}" maxlength="255">
                @error('info_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi Info</label>
                <textarea name="info_description" class="form-control @error('info_description') is-invalid @enderror" rows="3">{{ old('info_description', $setting->info_description) }}</textarea>
                @error('info_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Statistik Utama (Label)</label>
                    <input type="text" name="info_stat_primary_label" class="form-control @error('info_stat_primary_label') is-invalid @enderror" value="{{ old('info_stat_primary_label', $setting->info_stat_primary_label) }}" maxlength="120">
                    <input type="text" name="info_stat_primary_value" class="form-control mt-2 @error('info_stat_primary_value') is-invalid @enderror" placeholder="Nilai" value="{{ old('info_stat_primary_value', $setting->info_stat_primary_value) }}" maxlength="120">
                    @error('info_stat_primary_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @error('info_stat_primary_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Statistik Kedua (Label)</label>
                    <input type="text" name="info_stat_secondary_label" class="form-control @error('info_stat_secondary_label') is-invalid @enderror" value="{{ old('info_stat_secondary_label', $setting->info_stat_secondary_label) }}" maxlength="120">
                    <input type="text" name="info_stat_secondary_value" class="form-control mt-2 @error('info_stat_secondary_value') is-invalid @enderror" placeholder="Nilai" value="{{ old('info_stat_secondary_value', $setting->info_stat_secondary_value) }}" maxlength="120">
                    @error('info_stat_secondary_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @error('info_stat_secondary_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="row g-4">
        <div class="col-lg-6">
            <h5 class="fw-bold">Kontak / CTA</h5>
            <div class="mb-3">
                <label class="form-label">Judul</label>
                <input type="text" name="contact_title" class="form-control @error('contact_title') is-invalid @enderror" value="{{ old('contact_title', $setting->contact_title) }}" maxlength="255">
                @error('contact_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="contact_description" class="form-control @error('contact_description') is-invalid @enderror" rows="4">{{ old('contact_description', $setting->contact_description) }}</textarea>
                @error('contact_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Tombol</label>
                <input type="text" name="contact_button_text" class="form-control mb-2 @error('contact_button_text') is-invalid @enderror" value="{{ old('contact_button_text', $setting->contact_button_text) }}" maxlength="120">
                <input type="text" name="contact_button_link" class="form-control @error('contact_button_link') is-invalid @enderror" value="{{ old('contact_button_link', $setting->contact_button_link) }}" maxlength="255">
                @error('contact_button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @error('contact_button_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">Simpan Pengaturan</button>
    </div>
</form>
