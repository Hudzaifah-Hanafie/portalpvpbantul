@extends('layouts.admin')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Ubah Pengumuman</h5>
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

        <div class="alert alert-light border mb-4">
            @php
                $statusLabels = \App\Models\Pengumuman::statuses();
                $statusColors = [
                    \App\Models\Pengumuman::STATUS_DRAFT => 'secondary',
                    \App\Models\Pengumuman::STATUS_PENDING => 'warning',
                    \App\Models\Pengumuman::STATUS_PUBLISHED => 'success',
                ];
            @endphp
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Status: <span class="badge text-bg-{{ $statusColors[$pengumuman->status] ?? 'secondary' }}">{{ $statusLabels[$pengumuman->status] ?? ucfirst($pengumuman->status) }}</span>
                </div>
                <div class="d-flex gap-2">
                    @if($pengumuman->status === \App\Models\Pengumuman::STATUS_DRAFT)
                        <form action="{{ route('admin.pengumuman.submit', $pengumuman) }}" method="POST" onsubmit="return confirm('Ajukan pengumuman ini untuk disetujui?')">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm btn-outline-primary">Ajukan</button>
                        </form>
                    @endif
                    @if($pengumuman->status === \App\Models\Pengumuman::STATUS_PENDING && auth()->user()->hasPermission('approve-content'))
                        <form action="{{ route('admin.pengumuman.approve', $pengumuman) }}" method="POST" onsubmit="return confirm('Setujui pengumuman ini?')">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm btn-success">Setujui</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <form action="{{ route('admin.pengumuman.update', $pengumuman->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label class="form-label">Judul Pengumuman</label>
                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $pengumuman->judul) }}" maxlength="160" required>
                @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Isi Pengumuman</label>
                <textarea name="isi" rows="5" class="form-control @error('isi') is-invalid @enderror" required>{{ old('isi', $pengumuman->isi) }}</textarea>
                @error('isi') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror">
                    @foreach(\App\Models\Pengumuman::statuses() as $key => $label)
                        @if($key === \App\Models\Pengumuman::STATUS_PUBLISHED && !auth()->user()->hasPermission('approve-content'))
                            @continue
                        @endif
                        <option value="{{ $key }}" {{ old('status', $pengumuman->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                    @error('status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">File Lampiran</label>
                @if($pengumuman->file_download)
                    <div class="mb-2">
                        <a href="{{ asset($pengumuman->file_download) }}" target="_blank" class="text-primary"><i class="fas fa-file"></i> Lihat File Saat Ini</a>
                    </div>
                @endif
                <input type="file" name="file_download" class="form-control @error('file_download') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                @error('file_download') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                <small class="text-muted">Biarkan kosong jika file tidak ingin diubah.</small>
            </div>

            @include('admin.partials.seo-tools', ['model' => $pengumuman, 'baseUrl' => url('/pengumuman'), 'excerptField' => '[name=isi]'])

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Perbarui</button>
            <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
