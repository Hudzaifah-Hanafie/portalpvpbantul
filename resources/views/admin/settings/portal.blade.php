@extends('layouts.admin')

@section('content')
@php
    $activeTab = request('tab', 'site');
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Pengaturan Portal</h4>
        <small class="text-muted">Semua konfigurasi dirangkum dalam satu halaman, dipisah dalam tab.</small>
    </div>
    <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-secondary btn-sm">Lihat Website</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<ul class="nav nav-tabs flex-wrap">
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'site' ? 'active' : '' }}" href="{{ route('admin.settings.portal', ['tab' => 'site']) }}">Beranda & Umum</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'sso' ? 'active' : '' }}" href="{{ route('admin.settings.portal', ['tab' => 'sso']) }}">SSO SIAP Kerja</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'email' ? 'active' : '' }}" href="{{ route('admin.settings.portal', ['tab' => 'email']) }}">Email</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'contact' ? 'active' : '' }}" href="{{ route('admin.settings.portal', ['tab' => 'contact']) }}">Hubungi Kami</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'faq' ? 'active' : '' }}" href="{{ route('admin.settings.portal', ['tab' => 'faq']) }}">FAQ</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'publication' ? 'active' : '' }}" href="{{ route('admin.settings.portal', ['tab' => 'publication']) }}">Publikasi</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'public-service' ? 'active' : '' }}" href="{{ route('admin.settings.portal', ['tab' => 'public-service']) }}">Pelayanan Publik</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'ppid' ? 'active' : '' }}" href="{{ route('admin.settings.portal', ['tab' => 'ppid']) }}">PPID</a>
    </li>
</ul>

<div class="tab-content pt-3">
    <div class="tab-pane fade {{ $activeTab === 'site' ? 'show active' : '' }}" id="tab-site">
        @include('admin.settings.partials.site_general', ['settings' => $settings])
    </div>
    <div class="tab-pane fade {{ $activeTab === 'sso' ? 'show active' : '' }}" id="tab-sso">
        @include('admin.settings.partials.siapkerja', ['settings' => $settings])
    </div>
    <div class="tab-pane fade {{ $activeTab === 'email' ? 'show active' : '' }}" id="tab-email">
        @include('admin.settings.partials.email', ['settings' => $settings])
    </div>
    <div class="tab-pane fade {{ $activeTab === 'contact' ? 'show active' : '' }}" id="tab-contact">
        @include('admin.settings.partials.contact', ['setting' => $contactSetting])
    </div>
    <div class="tab-pane fade {{ $activeTab === 'faq' ? 'show active' : '' }}" id="tab-faq">
        @include('admin.settings.partials.faq', ['setting' => $faqSetting])
    </div>
    <div class="tab-pane fade {{ $activeTab === 'publication' ? 'show active' : '' }}" id="tab-publication">
        @include('admin.settings.partials.publication', ['setting' => $publicationSetting])
    </div>
    <div class="tab-pane fade {{ $activeTab === 'public-service' ? 'show active' : '' }}" id="tab-public-service">
        @include('admin.settings.partials.public_service', ['setting' => $publicServiceSetting])
    </div>
    <div class="tab-pane fade {{ $activeTab === 'ppid' ? 'show active' : '' }}" id="tab-ppid">
        @include('admin.settings.partials.ppid', ['setting' => $ppidSetting])
    </div>
</div>
@endsection
