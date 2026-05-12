@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">{{ $title }}</h2>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>

    <div class="bg-white rounded shadow-sm p-4">
        <form action="{{ $action }}" method="POST" class="row g-3">
            @csrf
            @if($method === 'PUT')
                @method('PUT')
            @endif

            <div class="col-md-4">
                <label class="form-label">Nama Izin Akses</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $permission->name ?? '') }}" required>
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Label</label>
                <input type="text" name="label" class="form-control" value="{{ old('label', $permission->label ?? '') }}" list="permission-label-options">
                <datalist id="permission-label-options">
                    @foreach($labelOptions as $option)
                        <option value="{{ $option }}">
                    @endforeach
                </datalist>
                @error('label') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Modul</label>
                <input type="text" name="module" class="form-control" value="{{ old('module', $permission->module ?? '') }}" list="permission-module-options">
                <datalist id="permission-module-options">
                    @foreach($moduleOptions as $option)
                        <option value="{{ $option }}">
                    @endforeach
                </datalist>
                @error('module') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-12">
                <label class="form-label">Peran terkait</label>
                <div class="row">
                    @php
                        $selectedRoles = collect(old('roles', ($permission->exists ?? false) ? $permission->roles->pluck('id')->toArray() : []));
                    @endphp
                    @foreach($roles as $role)
                        <div class="col-md-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}" {{ $selectedRoles->contains($role->id) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role-{{ $role->id }}">
                                    {{ $role->label ?? $role->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('roles') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">{{ $method === 'POST' ? 'Simpan' : 'Perbarui' }}</button>
            </div>
        </form>
    </div>
@endsection
