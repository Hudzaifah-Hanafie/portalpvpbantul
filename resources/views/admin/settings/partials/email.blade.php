<div class="card shadow-sm border-0 col-lg-11 mx-auto mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">Pengiriman Email</h5>
            <small class="text-muted">Konfigurasi SMTP, konfirmasi, dan notifikasi.</small>
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
                        <h6 class="fw-bold mb-1">Pengiriman Email (Konfirmasi & Notifikasi)</h6>
                        <small class="text-muted">Atur SMTP dan aktif/nonaktifkan email konfirmasi serta notifikasi.</small>
                    </div>
                </div>
                @php
                    $confirmEnabled = old('email_confirmations_enabled', $settings['email_confirmations_enabled'] ?? '1');
                    $notifyEnabled = old('email_notifications_enabled', $settings['email_notifications_enabled'] ?? '1');
                @endphp
                <input type="hidden" name="email_confirmations_enabled" value="0">
                <input type="hidden" name="email_notifications_enabled" value="0">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Mailer</label>
                        <select name="mail_mailer" class="form-select">
                            @php
                                $mailerValue = old('mail_mailer', $settings['mail_mailer'] ?? config('mail.default'));
                            @endphp
                            @foreach(['smtp' => 'SMTP', 'sendmail' => 'Sendmail', 'log' => 'Log (dev)'] as $value => $label)
                                <option value="{{ $value }}" @selected($mailerValue === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Gunakan SMTP untuk produksi; Log untuk pengujian lokal.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Host</label>
                        <input type="text" name="mail_host" class="form-control" value="{{ old('mail_host', $settings['mail_host'] ?? config('mail.mailers.smtp.host')) }}" placeholder="smtp.domain.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Port</label>
                        <input type="number" name="mail_port" class="form-control" value="{{ old('mail_port', $settings['mail_port'] ?? config('mail.mailers.smtp.port')) }}" placeholder="587">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="mail_username" class="form-control" value="{{ old('mail_username', $settings['mail_username'] ?? config('mail.mailers.smtp.username')) }}" placeholder="user@domain.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="mail_password" class="form-control" value="" placeholder="Biarkan kosong jika tidak ingin mengubah">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Enkripsi</label>
                        @php
                            $encValue = old('mail_encryption', $settings['mail_encryption'] ?? 'tls');
                        @endphp
                        <select name="mail_encryption" class="form-select">
                            <option value="tls" @selected($encValue === 'tls')>TLS</option>
                            <option value="ssl" @selected($encValue === 'ssl')>SSL</option>
                            <option value="none" @selected($encValue === 'none')>Tanpa Enkripsi</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Email</label>
                        <input type="text" name="mail_from_address" class="form-control" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? config('mail.from.address')) }}" placeholder="no-reply@domain.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Name</label>
                        <input type="text" name="mail_from_name" class="form-control" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? config('mail.from.name')) }}" placeholder="Satpel PVP Bantul">
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="email_confirmations_enabled" value="1" id="emailConfirmations" @checked($confirmEnabled)>
                            <label class="form-check-label" for="emailConfirmations">Kirim email konfirmasi (undangan, tracer submission)</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="email_notifications_enabled" value="1" id="emailNotifications" @checked($notifyEnabled)>
                            <label class="form-check-label" for="emailNotifications">Kirim email notifikasi (pengumuman, reminder, moderasi)</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-end mt-3">
                <button class="btn btn-primary px-4">Simpan</button>
            </div>
        </form>
    </div>
</div>
