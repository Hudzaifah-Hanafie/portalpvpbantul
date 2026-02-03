<div class="card shadow-sm border-0 col-lg-11 mx-auto mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">Konfigurasi SIAP Kerja</h5>
            <small class="text-muted">Kredensial SSO dan sinkronisasi pusat.</small>
        </div>
        @if(session('success')) <span class="badge bg-success bg-opacity-75 text-dark">Tersimpan</span> @endif
    </div>
    <div class="card-body">
        <form action="{{ route('admin.settings.site.update') }}" method="POST" class="vstack gap-4">
            @csrf
            @method('PUT')
            <div class="border rounded-3 p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">Konfigurasi SIAP Kerja / Skillhub</h6>
                        <small class="text-muted">Creds untuk SSO dan integrasi data. Kosongkan field yang tidak ingin diubah.</small>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="fw-semibold">SSO (Login User)</div>
                    <small class="text-muted">Gunakan kredensial aplikasi untuk Authorization Code Flow.</small>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Client ID</label>
                        @php
                            $clientIdValue = old('siapkerja_client_id');
                            if ($clientIdValue === null) {
                                $clientIdValue = !empty($settings['siapkerja_client_id'] ?? null)
                                    ? $settings['siapkerja_client_id']
                                    : config('services.siapkerja.client_id');
                            }
                        @endphp
                        <input type="text" name="siapkerja_client_id" class="form-control" value="{{ $clientIdValue }}" placeholder="SIAPKERJA_CLIENT_ID">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Client Secret</label>
                        @php
                            $clientSecretValue = old('siapkerja_client_secret');
                            if ($clientSecretValue === null) {
                                $clientSecretValue = !empty($settings['siapkerja_client_secret'] ?? null)
                                    ? $settings['siapkerja_client_secret']
                                    : config('services.siapkerja.client_secret');
                            }
                        @endphp
                        <input type="password" name="siapkerja_client_secret" class="form-control" value="{{ $clientSecretValue }}" placeholder="SIAPKERJA_CLIENT_SECRET">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Redirect URI</label>
                        @php
                            $redirectValue = old('siapkerja_redirect');
                            if ($redirectValue === null) {
                                $redirectValue = !empty($settings['siapkerja_redirect'] ?? null)
                                    ? $settings['siapkerja_redirect']
                                    : config('services.siapkerja.redirect');
                            }
                        @endphp
                        <input type="text" name="siapkerja_redirect" class="form-control" value="{{ $redirectValue }}" placeholder="https://domainmu.com/sso/siapkerja/callback">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Scope</label>
                        @php
                            $scopeValue = old('siapkerja_scope');
                            if ($scopeValue === null) {
                                $scopeValue = !empty($settings['siapkerja_scope'] ?? null)
                                    ? $settings['siapkerja_scope']
                                    : config('services.siapkerja.scope');
                            }
                        @endphp
                        <input type="text" name="siapkerja_scope" class="form-control" value="{{ $scopeValue }}" placeholder="basic email">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">API Base URL</label>
                        @php
                            $apiBaseValue = old('siapkerja_api_base');
                            if ($apiBaseValue === null) {
                                $apiBaseValue = !empty($settings['siapkerja_api_base'] ?? null)
                                    ? $settings['siapkerja_api_base']
                                    : config('services.siapkerja.api_base');
                            }
                        @endphp
                        <input type="text" name="siapkerja_api_base" class="form-control" value="{{ $apiBaseValue }}" placeholder="https://skillhub.kemnaker.go.id/api">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Token URL</label>
                        @php
                            $tokenUrlValue = old('siapkerja_token_url');
                            if ($tokenUrlValue === null) {
                                $tokenUrlValue = !empty($settings['siapkerja_token_url'] ?? null)
                                    ? $settings['siapkerja_token_url']
                                    : config('services.siapkerja.token_url');
                            }
                        @endphp
                        <input type="text" name="siapkerja_token_url" class="form-control" value="{{ $tokenUrlValue }}" placeholder="https://account.kemnaker.go.id/api/v1/tokens">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profile URL</label>
                        @php
                            $profileUrlValue = old('siapkerja_profile_url');
                            if ($profileUrlValue === null) {
                                $profileUrlValue = !empty($settings['siapkerja_profile_url'] ?? null)
                                    ? $settings['siapkerja_profile_url']
                                    : config('services.siapkerja.profile_url');
                            }
                        @endphp
                        <input type="text" name="siapkerja_profile_url" class="form-control" value="{{ $profileUrlValue }}" placeholder="https://account.kemnaker.go.id/api/v1/users/me">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profile API Base URL</label>
                        @php
                            $profileApiBaseValue = old('siapkerja_profile_api_base');
                            if ($profileApiBaseValue === null) {
                                $profileApiBaseValue = !empty($settings['siapkerja_profile_api_base'] ?? null)
                                    ? $settings['siapkerja_profile_api_base']
                                    : config('services.siapkerja.profile_api_base');
                            }
                        @endphp
                        <input type="text" name="siapkerja_profile_api_base" class="form-control" value="{{ $profileApiBaseValue }}" placeholder="https://api.kemnaker.go.id/profile">
                    </div>
                </div>
                <hr class="my-4">
                <div class="mb-3">
                    <div class="fw-semibold">Data Warehouse (Host-to-Host)</div>
                    <small class="text-muted">Gunakan token dari data.kemnaker.go.id. Header: <span class="fw-semibold">Authorization: Bearer &lt;token&gt;</span>.</small>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Service Base URL</label>
                        @php
                            $serviceBaseValue = old('siapkerja_service_base');
                            if ($serviceBaseValue === null) {
                                $serviceBaseValue = !empty($settings['siapkerja_service_base'] ?? null)
                                    ? $settings['siapkerja_service_base']
                                    : config('services.siapkerja.service_base');
                            }
                        @endphp
                        <input type="text" name="siapkerja_service_base" class="form-control" value="{{ $serviceBaseValue }}" placeholder="https://data.kemnaker.go.id/api/v1/services">
                        <small class="text-muted">Contoh: https://data.kemnaker.go.id/api/v1/services/{Service-ID}</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service Token (Bearer)</label>
                        @php
                            $serviceTokenValue = old('siapkerja_service_token');
                            if ($serviceTokenValue === null) {
                                $serviceTokenValue = !empty($settings['siapkerja_service_token'] ?? null)
                                    ? $settings['siapkerja_service_token']
                                    : config('services.siapkerja.service_token');
                            }
                        @endphp
                        <input type="password" name="siapkerja_service_token" class="form-control" value="{{ $serviceTokenValue }}" placeholder="Token integrasi data.kemnaker.go.id">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service ID Informasi Pelatihan</label>
                        @php
                            $serviceProgramValue = old('siapkerja_service_program_id');
                            if ($serviceProgramValue === null) {
                                $serviceProgramValue = $settings['siapkerja_service_program_id'] ?? config('services.siapkerja.service_program_id');
                            }
                        @endphp
                        <input type="text" name="siapkerja_service_program_id" class="form-control" value="{{ $serviceProgramValue }}" placeholder="Contoh: 38fe1841-5b66-4278-9b18-6058c8b1d651">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service ID Jadwal/Batch</label>
                        @php
                            $serviceScheduleValue = old('siapkerja_service_schedule_id');
                            if ($serviceScheduleValue === null) {
                                $serviceScheduleValue = $settings['siapkerja_service_schedule_id'] ?? config('services.siapkerja.service_schedule_id');
                            }
                        @endphp
                        <input type="text" name="siapkerja_service_schedule_id" class="form-control" value="{{ $serviceScheduleValue }}" placeholder="Service-ID jadwal/batch">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service ID Instruktur</label>
                        @php
                            $serviceInstructorValue = old('siapkerja_service_instructor_id');
                            if ($serviceInstructorValue === null) {
                                $serviceInstructorValue = $settings['siapkerja_service_instructor_id'] ?? config('services.siapkerja.service_instructor_id');
                            }
                        @endphp
                        <input type="text" name="siapkerja_service_instructor_id" class="form-control" value="{{ $serviceInstructorValue }}" placeholder="Service-ID instruktur">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service ID Peserta</label>
                        @php
                            $serviceParticipantValue = old('siapkerja_service_participant_id');
                            if ($serviceParticipantValue === null) {
                                $serviceParticipantValue = $settings['siapkerja_service_participant_id'] ?? config('services.siapkerja.service_participant_id');
                            }
                        @endphp
                        <input type="text" name="siapkerja_service_participant_id" class="form-control" value="{{ $serviceParticipantValue }}" placeholder="Service-ID peserta">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service ID Pencaker</label>
                        @php
                            $servicePencakerValue = old('siapkerja_service_pencaker_id');
                            if ($servicePencakerValue === null) {
                                $servicePencakerValue = $settings['siapkerja_service_pencaker_id'] ?? config('services.siapkerja.service_pencaker_id');
                            }
                        @endphp
                        <input type="text" name="siapkerja_service_pencaker_id" class="form-control" value="{{ $servicePencakerValue }}" placeholder="Service-ID pencaker">
                    </div>
                    <div class="col-12">
                        <div class="fw-semibold mt-2">Filter Query (opsional)</div>
                        <small class="text-muted">Dikirim ke semua service. Untuk Informasi Pelatihan, isi salah satu <span class="fw-semibold">prov_code</span> atau <span class="fw-semibold">city_code</span>.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">prov_code</label>
                        @php
                            $serviceProvCodeValue = old('siapkerja_service_prov_code');
                            if ($serviceProvCodeValue === null) {
                                $serviceProvCodeValue = $settings['siapkerja_service_prov_code'] ?? config('services.siapkerja.service_prov_code');
                            }
                        @endphp
                        <input type="text" name="siapkerja_service_prov_code" class="form-control" value="{{ $serviceProvCodeValue }}" placeholder="UUID provinsi">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">city_code</label>
                        @php
                            $serviceCityCodeValue = old('siapkerja_service_city_code');
                            if ($serviceCityCodeValue === null) {
                                $serviceCityCodeValue = $settings['siapkerja_service_city_code'] ?? config('services.siapkerja.service_city_code');
                            }
                        @endphp
                        <input type="text" name="siapkerja_service_city_code" class="form-control" value="{{ $serviceCityCodeValue }}" placeholder="UUID kabupaten/kota">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">vocational</label>
                        @php
                            $serviceVocationalValue = old('siapkerja_service_vocational');
                            if ($serviceVocationalValue === null) {
                                $serviceVocationalValue = $settings['siapkerja_service_vocational'] ?? config('services.siapkerja.service_vocational');
                            }
                        @endphp
                        <input type="text" name="siapkerja_service_vocational" class="form-control" value="{{ $serviceVocationalValue }}" placeholder="Nama kejuruan">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">sub_vocational</label>
                        @php
                            $serviceSubVocationalValue = old('siapkerja_service_sub_vocational');
                            if ($serviceSubVocationalValue === null) {
                                $serviceSubVocationalValue = $settings['siapkerja_service_sub_vocational'] ?? config('services.siapkerja.service_sub_vocational');
                            }
                        @endphp
                        <input type="text" name="siapkerja_service_sub_vocational" class="form-control" value="{{ $serviceSubVocationalValue }}" placeholder="Nama sub kejuruan">
                    </div>
                </div>
            </div>
            <div class="text-end mt-3">
                <button class="btn btn-primary px-4">Simpan</button>
            </div>
        </form>
    </div>
</div>
