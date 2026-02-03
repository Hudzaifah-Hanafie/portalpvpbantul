<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Pengaturan PPID</h4>
        <small class="text-muted">Atur hero, deskripsi profil, serta embed form PPID.</small>
    </div>
    <a href="{{ route('ppid') }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Halaman</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Validasi gagal.</strong> Pastikan teks tidak melebihi batas dan file mengikuti ketentuan (JPG/PNG maks 2 MB).
    </div>
@endif

<form action="{{ route('admin.ppid.settings.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded shadow-sm p-4" novalidate>
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
                        <img src="{{ asset($setting->hero_image) }}" class="img-fluid rounded shadow-sm" alt="Hero PPID" style="max-height:180px; object-fit:cover;">
                        <p class="text-muted small mb-0">Saat ini: {{ $setting->hero_image }}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <h5 class="fw-bold">Profil PPID</h5>
            <div class="mb-3">
                <label class="form-label">Judul Profil</label>
                <input type="text" name="profile_title" class="form-control @error('profile_title') is-invalid @enderror" value="{{ old('profile_title', $setting->profile_title) }}" maxlength="255">
                @error('profile_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi Profil</label>
                <textarea name="profile_description" class="form-control @error('profile_description') is-invalid @enderror" rows="6">{{ old('profile_description', $setting->profile_description) }}</textarea>
                @error('profile_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Judul Formulir</label>
                <input type="text" name="form_title" class="form-control @error('form_title') is-invalid @enderror" value="{{ old('form_title', $setting->form_title) }}" maxlength="255">
                @error('form_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi Formulir</label>
                <textarea name="form_description" class="form-control @error('form_description') is-invalid @enderror" rows="3">{{ old('form_description', $setting->form_description) }}</textarea>
                @error('form_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Embed Form (iframe)</label>
                <textarea name="form_embed" class="form-control @error('form_embed') is-invalid @enderror" rows="4" placeholder="Tempelkan kode iframe atau URL PPID">{{ old('form_embed', $setting->form_embed) }}</textarea>
                <small class="text-muted d-block">Tempelkan kode iframe dari Google Form/Simaster PPID atau URL form publik. Sistem akan otomatis membungkus URL menjadi iframe.</small>
                @error('form_embed') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">Simpan Pengaturan</button>
    </div>
</form>
