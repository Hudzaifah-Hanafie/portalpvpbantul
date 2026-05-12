<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3>Pengaturan Halaman Publikasi</h3>
        <p class="text-muted mb-0">Kontrol hero section, alumni video, dan blok download.</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Form belum valid.</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.publication.settings.update') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Judul Hero</label>
                    <input type="text" name="hero_title" class="form-control @error('hero_title') is-invalid @enderror" value="{{ old('hero_title', $setting->hero_title) }}">
                    @error('hero_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Deskripsi Hero</label>
                    <textarea name="hero_description" rows="2" class="form-control @error('hero_description') is-invalid @enderror">{{ old('hero_description', $setting->hero_description) }}</textarea>
                    @error('hero_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Teks Tombol Hero</label>
                    <input type="text" name="hero_button_text" class="form-control @error('hero_button_text') is-invalid @enderror" value="{{ old('hero_button_text', $setting->hero_button_text) }}">
                    @error('hero_button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tautan Tombol Hero</label>
                    <input type="url" name="hero_button_link" class="form-control @error('hero_button_link') is-invalid @enderror" value="{{ old('hero_button_link', $setting->hero_button_link) }}">
                    @error('hero_button_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gambar Hero</label>
                    <input type="file" name="hero_image" class="form-control @error('hero_image') is-invalid @enderror" accept="image/jpeg,image/png">
                    <small class="text-muted">Format JPG/PNG maksimal 2MB.</small>
                    @error('hero_image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    @if($setting->hero_image)
                        <img src="{{ asset($setting->hero_image) }}" class="img-fluid rounded mt-2" style="max-height:140px;">
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Judul Pengenalan</label>
                    <input type="text" name="intro_title" class="form-control @error('intro_title') is-invalid @enderror" value="{{ old('intro_title', $setting->intro_title) }}">
                    @error('intro_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Deskripsi Pengenalan</label>
                    <textarea name="intro_description" rows="2" class="form-control @error('intro_description') is-invalid @enderror">{{ old('intro_description', $setting->intro_description) }}</textarea>
                    @error('intro_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Judul Alumni</label>
                    <input type="text" name="alumni_title" class="form-control @error('alumni_title') is-invalid @enderror" value="{{ old('alumni_title', $setting->alumni_title) }}">
                    @error('alumni_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Deskripsi Alumni</label>
                    <textarea name="alumni_description" rows="2" class="form-control @error('alumni_description') is-invalid @enderror">{{ old('alumni_description', $setting->alumni_description) }}</textarea>
                    @error('alumni_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">URL Video Alumni (YouTube)</label>
                    <input type="url" name="alumni_video_url" class="form-control @error('alumni_video_url') is-invalid @enderror" value="{{ old('alumni_video_url', $setting->alumni_video_url) }}">
                    <small class="text-muted">Ganti format watch?v= menjadi link YouTube valid; secara otomatis di-embed.</small>
                    @error('alumni_video_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Judul Materi Unduhan</label>
                    <input type="text" name="downloads_title" class="form-control @error('downloads_title') is-invalid @enderror" value="{{ old('downloads_title', $setting->downloads_title) }}">
                    @error('downloads_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">Deskripsi Materi Unduhan</label>
                    <textarea name="downloads_description" rows="2" class="form-control @error('downloads_description') is-invalid @enderror">{{ old('downloads_description', $setting->downloads_description) }}</textarea>
                    @error('downloads_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>
