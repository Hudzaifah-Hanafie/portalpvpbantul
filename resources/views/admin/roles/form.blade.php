@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">{{ $title }}</h2>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>

    <div class="bg-white rounded shadow-sm p-4">
        <form action="{{ $action }}" method="POST" class="row g-3">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif

            <div class="col-md-6">
                <label class="form-label">Nama Peran (tanpa spasi)</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $role->name ?? '') }}" required>
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Label</label>
                <input type="text" name="label" class="form-control" value="{{ old('label', $role->label ?? '') }}">
                @error('label') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-12">
                <label class="form-label d-block">Izin Akses</label>
                <div class="row">
                    @php
                        $selectedPermissions = collect(old('permissions', ($role->exists ?? false) ? $role->permissions->pluck('id')->toArray() : []));
                        $grouped = $permissions->groupBy('module');
                    @endphp
                    @foreach($grouped as $module => $modulePermissions)
                        <div class="col-md-4 mb-3">
                            <div class="fw-semibold mb-2 text-uppercase small text-muted">{{ $module ?? 'Umum' }}</div>
                            @foreach($modulePermissions as $permission)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission-{{ $permission->id }}" {{ $selectedPermissions->contains($permission->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permission-{{ $permission->id }}">
                                        {{ $permission->label ?? $permission->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                @error('permissions') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">{{ $method === 'POST' ? 'Simpan' : 'Perbarui' }}</button>
            </div>
        </form>
    </div>
@endsection
