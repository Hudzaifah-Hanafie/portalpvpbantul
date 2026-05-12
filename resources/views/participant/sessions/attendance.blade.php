@extends('layouts.participant')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 fade-up">
    <div>
        <h4 class="mb-1 section-title">Presensi Sesi</h4>
        <div class="section-subtitle">{{ $session->course->title ?? 'Pelatihan' }} • {{ $session->title ?? 'Sesi' }}</div>
    </div>
    <a href="{{ route('participant.classes') }}" class="btn btn-outline-light border-0 shadow-sm bg-white text-dark btn-sm">Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-modern">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-modern">Silakan cek kembali input Anda.</div>
@endif

@if($session->meeting_link)
    <div class="card stat-card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div>
                    <div class="fw-semibold">Tatap Muka Online</div>
                    <div class="text-muted small">Gunakan link resmi untuk mengikuti sesi daring.</div>
                </div>
                <a href="{{ $session->meeting_link }}" target="_blank" rel="noopener" class="btn btn-primary lms-cta">Buka Meet/Zoom</a>
            </div>
            <div class="ratio ratio-16x9 rounded overflow-hidden border">
                <iframe src="{{ $session->meeting_link }}" title="Tautan Tatap Muka" allow="camera; microphone; fullscreen; display-capture" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <div class="text-muted small mt-2">Jika embed tidak tampil, gunakan tombol “Buka Meet/Zoom”.</div>
        </div>
    </div>
@endif

<form action="{{ route('participant.sessions.attendance', $session) }}" method="POST" class="card stat-card p-4">
    @csrf

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Kode Presensi</label>
            <input type="text" name="attendance_code" class="form-control @error('attendance_code') is-invalid @enderror" required>
            @error('attendance_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                @foreach($statusOptions as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $attendance->status ?? 'hadir') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">Alasan (opsional)</label>
            <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason', $attendance?->reason) }}">
            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="mt-4">
        <label class="form-label">Tanda Tangan Digital</label>
        <div class="border rounded p-2 bg-light">
            <canvas id="signaturePad" width="640" height="220" style="width:100%; height:220px; background:#fff;"></canvas>
        </div>
        <input type="hidden" name="signature_data" id="signatureData">
        @error('signature_data') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        <div class="d-flex justify-content-between mt-2">
            <small class="text-muted">Gunakan jari/pena untuk menandatangani.</small>
            <button type="button" class="btn btn-outline-secondary lms-cta" id="clearSignature">Hapus</button>
        </div>
    </div>

    <div class="text-end mt-4">
        <button class="btn btn-primary lms-cta">Kirim Presensi</button>
    </div>
</form>
@endsection

@push('styles')
<style>
    #signaturePad { touch-action: none; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const canvas = document.getElementById('signaturePad');
        const ctx = canvas.getContext('2d');
        let drawing = false;

        const getPos = (e) => {
            const rect = canvas.getBoundingClientRect();
            const evt = e.touches ? e.touches[0] : e;
            return {
                x: (evt.clientX - rect.left) * (canvas.width / rect.width),
                y: (evt.clientY - rect.top) * (canvas.height / rect.height),
            };
        };

        const startDraw = (e) => {
            drawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        };
        const draw = (e) => {
            if (!drawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.strokeStyle = '#111827';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.stroke();
        };
        const endDraw = () => {
            drawing = false;
        };

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', endDraw);
        canvas.addEventListener('mouseleave', endDraw);
        canvas.addEventListener('touchstart', startDraw, { passive: true });
        canvas.addEventListener('touchmove', draw, { passive: true });
        canvas.addEventListener('touchend', endDraw);

        document.getElementById('clearSignature').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

        const form = canvas.closest('form');
        form.addEventListener('submit', (e) => {
            const dataUrl = canvas.toDataURL('image/png');
            const isBlank = dataUrl === document.createElement('canvas').toDataURL('image/png');
            if (isBlank) {
                e.preventDefault();
                alert('Mohon isi tanda tangan terlebih dahulu.');
                return;
            }
            document.getElementById('signatureData').value = dataUrl;
        });
    })();
</script>
@endpush
