@extends('layouts.admin')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Buat Pengumuman Baru</h5>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Gagal menyimpan.</strong> Periksa kembali isian berikut:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.pengumuman.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Judul Pengumuman</label>
                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul') }}" placeholder="Contoh: Hasil Seleksi Tahap 1" maxlength="160" required>
                @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Isi Pengumuman</label>
                <textarea name="isi" rows="5" class="form-control @error('isi') is-invalid @enderror" placeholder="Tulis detail pengumuman..." required>{{ old('isi') }}</textarea>
                @error('isi') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach(\App\Models\Pengumuman::statuses() as $key => $label)
                        @if($key === \App\Models\Pengumuman::STATUS_PUBLISHED && !auth()->user()->hasPermission('approve-content'))
                            @continue
                        @endif
                        <option value="{{ $key }}" {{ old('status', \App\Models\Pengumuman::STATUS_DRAFT) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                    @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">File Lampiran (PDF/DOC) - <span class="text-muted fw-normal">Opsional</span></label>
                <input type="file" name="file_download" class="form-control @error('file_download') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                @error('file_download') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                <small class="text-muted">Unggah file hasil seleksi atau jadwal jika ada.</small>
            </div>

            @include('admin.partials.seo-tools', ['model' => null, 'baseUrl' => url('/pengumuman'), 'excerptField' => '[name=isi]'])

            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Terbitkan</button>
            <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
