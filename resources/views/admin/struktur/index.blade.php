@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Struktur Organisasi</h3>
    <a href="{{ route('admin.struktur.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Tambah Jabatan
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        @if($roots->isEmpty())
            <p class="text-muted mb-0">Belum ada data struktur. Tambahkan jabatan pertama.</p>
        @else
            <ul class="list-unstyled ms-3">
                @foreach($roots as $node)
                    @include('admin.struktur.node', ['node' => $node])
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
