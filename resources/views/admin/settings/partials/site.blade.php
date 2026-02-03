<div class="d-flex flex-column gap-4">
    @include('admin.settings.partials.site_general', ['settings' => $settings])
    @include('admin.settings.partials.siapkerja', ['settings' => $settings])
    @include('admin.settings.partials.email', ['settings' => $settings])
</div>
