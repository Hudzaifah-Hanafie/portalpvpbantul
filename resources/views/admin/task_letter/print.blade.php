<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Tugas - {{ $letter->letter_number ?? 'ST' }}</title>
    <style>
        body { font-family: "Times New Roman", serif; font-size: 13px; margin: 24px; color: #111; }
        h1, h2, h3 { margin: 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mt-4 { margin-top: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; }
        .no-border td { border: none; padding: 2px 0; }
    </style>
</head>
<body>
    <div class="text-center mb-4">
        <h2>SURAT TUGAS</h2>
        <div>Nomor: {{ $letter->letter_number ?? '-' }}</div>
    </div>

    <table class="no-border mb-4">
        <tr>
            <td style="width:140px;">Dasar Hukum</td>
            <td>: {{ $letter->legal_basis ?? '-' }}</td>
        </tr>
        <tr>
            <td>Nama Pelatihan</td>
            <td>: {{ $letter->course?->title ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jadwal</td>
            <td>: {{ optional($letter->start_date)->format('d M Y') ?? '-' }} @if($letter->end_date) - {{ $letter->end_date->format('d M Y') }} @endif
                @if($letter->start_time) • {{ $letter->start_time }} @endif @if($letter->end_time) - {{ $letter->end_time }} @endif
            </td>
        </tr>
        <tr>
            <td>Lokasi</td>
            <td>: {{ $letter->location_name ?? '-' }} @if($letter->location_link) ({{ $letter->location_link }}) @endif</td>
        </tr>
    </table>

    <div class="mb-2"><strong>Instruktur/Tenaga Pelatih</strong></div>
    <ol class="mb-4">
        @forelse(($letter->instructors ?? []) as $row)
            <li>{{ $row['name'] ?? '-' }} @if(!empty($row['nip'])) ({{ $row['nip'] }}) @endif — {{ $row['position'] ?? '-' }}</li>
        @empty
            <li>-</li>
        @endforelse
    </ol>

    <div class="mb-2"><strong>Panitia Penyelenggara</strong></div>
    <ol class="mb-4">
        @forelse(($letter->committees ?? []) as $row)
            <li>{{ $row['name'] ?? '-' }} — {{ $row['role'] ?? '-' }}</li>
        @empty
            <li>-</li>
        @endforelse
    </ol>

    <div class="mb-2"><strong>Lampiran I — Daftar Peserta (Nominatif)</strong></div>
    <table class="mb-4">
        <thead>
            <tr>
                <th style="width:40px;">No</th>
                <th>Nama</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @forelse($participants as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $row->user?->name ?? '-' }}</td>
                    <td>{{ $row->user?->email ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">Tidak ada peserta.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="text-right mt-4">
        <div>{{ $letter->signed_city ?? '-' }}, {{ optional($letter->signed_at)->format('d M Y') ?? '-' }}</div>
        <div class="mb-4">{{ $letter->signatory_position ?? 'Pejabat Berwenang' }}</div>
        <div style="height:48px;"></div>
        <div><strong>{{ $letter->signatory_name ?? '-' }}</strong></div>
        <div>{{ $letter->signatory_nip ? 'NIP ' . $letter->signatory_nip : '' }}</div>
    </div>
</body>
</html>
