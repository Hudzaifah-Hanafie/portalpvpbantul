@extends('layouts.admin')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
    @php
        $timezone = config('app.timezone');
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1">Log Aktivitas</h2>
            <p class="text-muted mb-0">Audit trail untuk aktivitas pengguna (zona waktu {{ $timezone }}).</p>
        </div>
        @can('deleteAny', App\Models\ActivityLog::class)
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                <i class="fas fa-trash-alt me-2"></i>Bersihkan Log
            </button>
        @endcan
    </div>

    @if($errors->has('confirmation'))
        <div class="alert alert-danger mb-4">
            {{ $errors->first('confirmation') }}
        </div>
    @endif

    <div class="bg-white rounded shadow-sm p-3 mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Pengguna</label>
                <select name="user_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? null) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Aksi</label>
                <select name="action" class="form-select">
                    <option value="">Semua</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ ($filters['action'] ?? null) == $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dari</label>
                <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai</label>
                <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                @if(array_filter($filters ?? []))
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-link btn-sm">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Aksi</th>
                    <th>Target</th>
                    <th>Deskripsi</th>
                    <th>IP</th>
                    <th>User Agent</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->timezone($timezone)->format('d M Y H:i:s') }}</td>
                        <td>{{ $log->user?->name ?? 'Sistem' }}</td>
                        <td><code>{{ $log->action }}</code></td>
                        <td>
                            @if($log->subject_type)
                                <span class="d-block">{{ class_basename($log->subject_type) }}</span>
                                <small class="text-muted">{{ $log->subject_id }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $log->description ?? '-' }}</div>
                            @if($log->metadata)
                                <small class="text-muted">{{ json_encode($log->metadata) }}</small>
                            @endif
                        </td>
                        <td>{{ $log->ip_address ?? '-' }}</td>
                        <td class="text-break">{{ Str::limit($log->user_agent ?? '-', 40) }}</td>
                        <td class="text-end">
                            @can('delete', $log)
                                <form action="{{ route('admin.activity-logs.destroy', $log) }}" method="POST" onsubmit="return confirm('Hapus log ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada aktivitas tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $logs->onEachSide(1)->links('vendor.pagination.bootstrap-5-sm') }}
    </div>

    <!-- Modal Konfirmasi Hapus Log -->
    <div class="modal fade" id="clearLogsModal" tabindex="-1" aria-labelledby="clearLogsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.activity-logs.clear') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title text-danger" id="clearLogsModalLabel">Konfirmasi Penghapusan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus <strong>SELURUH</strong> riwayat log aktivitas? Tindakan ini tidak dapat dibatalkan.</p>
                        <div class="mb-3">
                            <label for="confirmation" class="form-label">Ketik <span class="badge bg-light text-danger border border-danger">hapus data log</span> untuk konfirmasi:</label>
                            <input type="text" class="form-control" id="confirmation" name="confirmation" required autocomplete="off" placeholder="hapus data log">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus Semua Log</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->has('confirmation'))
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var myModal = new bootstrap.Modal(document.getElementById('clearLogsModal'));
                    myModal.show();
                });
            </script>
        @endpush
    @endif
@endsection