<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Pengaturan Hubungi Kami</h4>
        <small class="text-muted">Atur hero, peta, dan CTA agar sesuai dengan template halaman kontak.</small>
    </div>
    <a href="{{ route('resource.hubungi') }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Halaman</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Validasi gagal.</strong> Pastikan teks tidak melebihi batas dan file sesuai ketentuan (JPG/PNG maks 2 MB).
    </div>
@endif

<form action="{{ route('admin.contact.settings.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded shadow-sm p-4" novalidate>
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
                    <label class="form-label">Tautan Tombol</label>
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
                        <img src="{{ asset($setting->hero_image) }}" class="img-fluid rounded shadow-sm" style="max-height:180px;object-fit:cover;" alt="Hero Hubungi Kami">
                        <p class="text-muted small mb-0">Saat ini: {{ $setting->hero_image }}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <h5 class="fw-bold">Peta & Informasi</h5>
            <div class="mb-3">
                <label class="form-label">Judul Peta</label>
                <input type="text" name="map_title" class="form-control @error('map_title') is-invalid @enderror" value="{{ old('map_title', $setting->map_title) }}" maxlength="255">
                @error('map_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi Peta</label>
                <textarea name="map_description" class="form-control @error('map_description') is-invalid @enderror" rows="3">{{ old('map_description', $setting->map_description) }}</textarea>
                @error('map_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Embed Map (iframe)</label>
                <textarea name="map_embed" class="form-control @error('map_embed') is-invalid @enderror" rows="4" placeholder="Tempelkan kode iframe atau tautan Google Maps">{{ old('map_embed', $setting->map_embed) }}</textarea>
                @error('map_embed') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Judul Layanan Info</label>
                <input type="text" name="info_section_title" class="form-control @error('info_section_title') is-invalid @enderror" value="{{ old('info_section_title', $setting->info_section_title) }}" maxlength="255">
                @error('info_section_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi Layanan Info</label>
                <textarea name="info_section_description" class="form-control @error('info_section_description') is-invalid @enderror" rows="3">{{ old('info_section_description', $setting->info_section_description) }}</textarea>
                @error('info_section_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="row">
        <div class="col-lg-8">
            <h5 class="fw-bold">Call To Action</h5>
            <div class="mb-3">
                <label class="form-label">Judul CTA</label>
                <input type="text" name="cta_title" class="form-control @error('cta_title') is-invalid @enderror" value="{{ old('cta_title', $setting->cta_title) }}" maxlength="255">
                @error('cta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi CTA</label>
                <textarea name="cta_description" class="form-control @error('cta_description') is-invalid @enderror" rows="3">{{ old('cta_description', $setting->cta_description) }}</textarea>
                @error('cta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tombol Utama</label>
                    <input type="text" name="cta_primary_text" class="form-control mb-2 @error('cta_primary_text') is-invalid @enderror" value="{{ old('cta_primary_text', $setting->cta_primary_text) }}" maxlength="120">
                    <input type="text" name="cta_primary_link" class="form-control @error('cta_primary_link') is-invalid @enderror" value="{{ old('cta_primary_link', $setting->cta_primary_link) }}" maxlength="255">
                    @error('cta_primary_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @error('cta_primary_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tombol Kedua</label>
                    <input type="text" name="cta_secondary_text" class="form-control mb-2 @error('cta_secondary_text') is-invalid @enderror" value="{{ old('cta_secondary_text', $setting->cta_secondary_text) }}" maxlength="120">
                    <input type="text" name="cta_secondary_link" class="form-control @error('cta_secondary_link') is-invalid @enderror" value="{{ old('cta_secondary_link', $setting->cta_secondary_link) }}" maxlength="255">
                    @error('cta_secondary_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @error('cta_secondary_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary px-4">Simpan Pengaturan</button>
    </div>
</form>
