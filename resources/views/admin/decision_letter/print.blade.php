<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keputusan</title>
    <style>
        body { font-family: "Times New Roman", serif; font-size: 12px; color: #111; margin: 24px; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-text { text-align: center; line-height: 1.2; }
        .header-text h1 { font-size: 14px; margin: 0; font-weight: 700; }
        .header-text h2 { font-size: 13px; margin: 0; font-weight: 700; }
        .header-text h3 { font-size: 12px; margin: 0; font-weight: 700; }
        .header-text .sub { font-size: 10px; }
        .logo { width: 70px; }
        .hr-line { border: none; border-top: 2px solid #111; margin: 10px 0 14px; }
        .center { text-align: center; }
        .title { font-weight: 700; margin-top: 4px; }
        .subject-line { font-weight: 700; }
        .section-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .section-table td { vertical-align: top; padding: 2px 4px; }
        .section-table .label { width: 90px; }
        .section-table .colon { width: 10px; }
        ol.alpha { margin: 0; padding-left: 18px; }
        .decision-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .decision-table td { vertical-align: top; padding: 2px 4px; }
        .signatory { text-align: right; margin-top: 24px; }
        .signatory .name { margin-top: 48px; font-weight: 700; }
        .curriculum-title { text-align: center; font-weight: 700; margin: 10px 0; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .meta-table td { padding: 2px 4px; }
        .curriculum-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .curriculum-table th, .curriculum-table td { border: 1px solid #111; padding: 3px 4px; }
        .curriculum-table th { text-align: center; }
        .curriculum-table .center { text-align: center; }
        .curriculum-table .section-row td { font-weight: 700; }
        .curriculum-table .total-row td { font-weight: 700; }
        .signatures { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .signatures td { text-align: center; vertical-align: top; }
        .lampiran-title { text-align: left; margin-bottom: 8px; }
        .lampiran-sub { text-align: center; font-weight: 700; margin-bottom: 8px; }
        .team-table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .team-table th, .team-table td { border: 1px solid #111; padding: 4px 6px; }
        .team-table th { text-align: center; }
        .team-sign { text-align: right; margin-top: 24px; }
        .team-sign .name { margin-top: 40px; font-weight: 700; }
    </style>
</head>
<body>
    @php
        $decisions = $letter->decisions ?? [];
    @endphp
    <div class="page">
        <table class="header-table">
            <tr>
                <td style="width:90px;"><img src="{{ asset('image/logo/logo_kemnaker.svg') }}" alt="Kemnaker" class="logo"></td>
                <td class="header-text">
                    <h1>KEMENTERIAN KETENAGAKERJAAN RI</h1>
                    <h2>DIREKTORAT JENDERAL</h2>
                    <h2>PEMBINAAN PELATIHAN VOKASI DAN PRODUKTIVITAS</h2>
                    <h2>BALAI PELATIHAN VOKASI DAN PRODUKTIVITAS</h2>
                    <div class="sub">Jl. Bhayangkara No. 38 Surakarta 57149, Telp (0271) 714885, Fax. (0271) 711646</div>
                    <div class="sub">Laman: http://www.kemnaker.go.id</div>
                </td>
            </tr>
        </table>
        <hr class="hr-line">

        <div class="center">
            <div class="title">KEPUTUSAN</div>
            <div class="title">KEPALA BALAI PELATIHAN VOKASI DAN PRODUKTIVITAS SURAKARTA</div>
            <div class="title">NOMOR {{ $letter->letter_number ?? '-' }}</div>
            <div class="title" style="margin-top:12px;">TENTANG</div>
            @foreach($subjectLines as $line)
                <div class="subject-line">{{ $line }}</div>
            @endforeach
        </div>

        <table class="section-table">
            <tr>
                <td class="label">Menimbang</td>
                <td class="colon">:</td>
                <td>
                    <ol class="alpha" type="a">
                        @foreach($letter->considerations ?? [] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                </td>
            </tr>
            <tr>
                <td class="label">Mengingat</td>
                <td class="colon">:</td>
                <td>
                    <ol class="alpha">
                        @foreach($letter->legal_basis ?? [] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                </td>
            </tr>
        </table>

        <div class="center" style="margin-top:10px; font-weight:700;">MEMUTUSKAN:</div>
        <div style="margin-top:6px;">Menetapkan:</div>

        <table class="decision-table">
            <tr>
                <td class="label">KESATU</td>
                <td class="colon">:</td>
                <td>{!! nl2br(e($decisions['kesatu'] ?? '-')) !!}</td>
            </tr>
            <tr>
                <td class="label">KEDUA</td>
                <td class="colon">:</td>
                <td>{!! nl2br(e($decisions['kedua'] ?? '-')) !!}</td>
            </tr>
            <tr>
                <td class="label">KETIGA</td>
                <td class="colon">:</td>
                <td>{!! nl2br(e($decisions['ketiga'] ?? '-')) !!}</td>
            </tr>
            <tr>
                <td class="label">KEEMPAT</td>
                <td class="colon">:</td>
                <td>{!! nl2br(e($decisions['keempat'] ?? '-')) !!}</td>
            </tr>
            <tr>
                <td class="label">KELIMA</td>
                <td class="colon">:</td>
                <td>{!! nl2br(e($decisions['kelima'] ?? '-')) !!}</td>
            </tr>
        </table>

        <div class="signatory">
            <div>{{ $letter->signed_city ?? '-' }}, {{ optional($letter->signed_at)->locale('id')->translatedFormat('d F Y') ?? '-' }}</div>
            <div>{{ $letter->signatory_position ?? 'KEPALA BPVP SURAKARTA' }}</div>
            <div class="name">{{ $letter->signatory_name ?? '-' }}</div>
            <div>{{ $letter->signatory_nip ? 'NIP. ' . $letter->signatory_nip : '' }}</div>
        </div>
    </div>

    @foreach($curricula as $curriculum)
        <div class="page">
            <table class="header-table">
                <tr>
                    <td style="width:90px;"><img src="{{ asset('image/logo/logo_kemnaker.svg') }}" alt="Kemnaker" class="logo"></td>
                    <td class="header-text">
                        <h1>KEMENTERIAN KETENAGAKERJAAN RI</h1>
                        <h2>DIREKTORAT JENDERAL</h2>
                        <h2>PEMBINAAN PELATIHAN VOKASI DAN PRODUKTIVITAS</h2>
                        <h2>BALAI PELATIHAN VOKASI DAN PRODUKTIVITAS</h2>
                        <div class="sub">Jl. Bhayangkara No. 38 Surakarta 57149, Telp (0271) 714885, Fax. (0271) 711646</div>
                        <div class="sub">Laman: http://www.kemnaker.go.id</div>
                    </td>
                </tr>
            </table>
            <hr class="hr-line">
            <div class="curriculum-title">KURIKULUM PELATIHAN BERBASIS KOMPETENSI PELATIHAN DURASI SINGKAT</div>

            <table class="meta-table">
                <tr>
                    <td style="width:130px;">Kejuruan</td>
                    <td style="width:10px;">:</td>
                    <td>{{ $curriculum['kejuruan'] }}</td>
                </tr>
                <tr>
                    <td>Program Pelatihan</td>
                    <td>:</td>
                    <td>{{ $curriculum['program'] }}</td>
                </tr>
                <tr>
                    <td>Tahun</td>
                    <td>:</td>
                    <td>{{ $curriculum['tahun'] }}</td>
                </tr>
            </table>

            <table class="curriculum-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:40px;">No.</th>
                        <th rowspan="2">Judul Unit Kompetensi</th>
                        <th rowspan="2" style="width:130px;">Kode Unit</th>
                        <th colspan="3">Perkiraan Waktu Pelatihan</th>
                    </tr>
                    <tr>
                        <th style="width:60px;">Teori</th>
                        <th style="width:60px;">Praktek</th>
                        <th style="width:60px;">Jum-lah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="section-row">
                        <td class="center">I.</td>
                        <td colspan="5">KELOMPOK UNIT KOMPETENSI</td>
                    </tr>
                    @if($curriculum['units']->isEmpty())
                        <tr>
                            <td class="center">1.1</td>
                            <td>-</td>
                            <td class="center">-</td>
                            <td class="center">-</td>
                            <td class="center">-</td>
                            <td class="center">-</td>
                        </tr>
                    @else
                        @foreach($curriculum['units'] as $index => $unit)
                            @php
                                $theory = (int) $unit->jp_theory;
                                $practice = (int) $unit->jp_practice;
                                $total = $theory + $practice;
                            @endphp
                            <tr>
                                <td class="center">1.{{ $index + 1 }}</td>
                                <td>{{ $unit->unit_title }}</td>
                                <td class="center">{{ $unit->unit_code }}</td>
                                <td class="center">{{ $theory ?: '-' }}</td>
                                <td class="center">{{ $practice ?: '-' }}</td>
                                <td class="center">{{ $total ?: '-' }}</td>
                            </tr>
                        @endforeach
                    @endif
                    <tr class="total-row">
                        <td></td>
                        <td>JUMLAH I</td>
                        <td></td>
                        <td class="center">{{ $curriculum['total_theory'] ?: '-' }}</td>
                        <td class="center">{{ $curriculum['total_practice'] ?: '-' }}</td>
                        <td class="center">{{ $curriculum['total_all'] ?: '-' }}</td>
                    </tr>
                    <tr class="section-row">
                        <td class="center">II.</td>
                        <td colspan="5">Kelompok Pilihan/Penunjang</td>
                    </tr>
                    <tr>
                        <td class="center">2.1</td>
                        <td>Soft Skills</td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                    </tr>
                    <tr>
                        <td class="center">2.2</td>
                        <td>Produktivitas</td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                    </tr>
                    <tr class="total-row">
                        <td></td>
                        <td>JUMLAH II</td>
                        <td></td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                    </tr>
                    <tr class="section-row">
                        <td class="center">III.</td>
                        <td colspan="5">EVALUASI PROGRAM PELATIHAN</td>
                    </tr>
                    <tr>
                        <td class="center">3.1</td>
                        <td>Evaluasi Program Pelatihan</td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                    </tr>
                    <tr class="total-row">
                        <td></td>
                        <td>JUMLAH III</td>
                        <td></td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                    </tr>
                    <tr class="total-row">
                        <td></td>
                        <td>JUMLAH I s/d III</td>
                        <td></td>
                        <td class="center">{{ $curriculum['total_theory'] ?: '-' }}</td>
                        <td class="center">{{ $curriculum['total_practice'] ?: '-' }}</td>
                        <td class="center">{{ $curriculum['total_all'] ?: '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="signatures">
                <tr>
                    <td>
                        Mengetahui/ Menyetujui :<br>
                        {{ $letter->approval_left_position ?? 'Kepala BPVP Surakarta' }}
                    </td>
                    <td>
                        {{ $letter->approval_right_position ?? '' }}
                    </td>
                </tr>
                <tr style="height:60px;">
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <strong>{{ $letter->approval_left_name ?? '-' }}</strong><br>
                        {{ $letter->approval_left_nip ? 'NIP. ' . $letter->approval_left_nip : '' }}
                    </td>
                    <td>
                        <strong>{{ $letter->approval_right_name ?? '-' }}</strong><br>
                        {{ $letter->approval_right_nip ? 'NIP. ' . $letter->approval_right_nip : '' }}
                    </td>
                </tr>
            </table>
        </div>
    @endforeach

    @php
        $teamPages = [
            ['title' => 'Susunan Tim Instruktur Pelatihan Durasi Pendek Tahun Anggaran ' . ($letter->period_year ?? now()->year) . ' di BPVP Surakarta', 'rows' => $teams['instruktur'] ?? []],
            ['title' => 'Susunan Tim Rekrutmen Pelatihan Durasi Pendek Tahun Anggaran ' . ($letter->period_year ?? now()->year) . ' di BPVP Surakarta', 'rows' => $teams['rekrutmen'] ?? []],
            ['title' => 'Susunan Tim Pengelola Pelatihan Durasi Pendek Tahun Anggaran ' . ($letter->period_year ?? now()->year) . ' di BPVP Surakarta', 'rows' => $teams['pengelola'] ?? []],
        ];
    @endphp

    @foreach($teamPages as $page)
        <div class="page">
            <div class="lampiran-title">
                Lampiran Keputusan Kepala BPVP Surakarta<br>
                Nomor : {{ $letter->letter_number ?? '-' }}
            </div>
            <div class="lampiran-sub">{{ $page['title'] }}</div>
            <table class="team-table">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Nama / NIP</th>
                        <th style="width:200px;">Jabatan</th>
                        <th style="width:160px;">Jabatan Dalam Tim</th>
                    </tr>
                </thead>
                <tbody>
                    @if(empty($page['rows']))
                        <tr>
                            <td colspan="4" class="center">Belum ada data.</td>
                        </tr>
                    @else
                        @foreach($page['rows'] as $idx => $row)
                            <tr>
                                <td class="center">{{ $idx + 1 }}</td>
                                <td>
                                    {{ $row['name'] ?? '-' }}
                                    @if(!empty($row['nip']))
                                        <br>NIP. {{ $row['nip'] }}
                                    @endif
                                </td>
                                <td>{{ $row['position'] ?? '-' }}</td>
                                <td>{{ $row['role'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>

            <div class="team-sign">
                <div>Dikeluarkan di : {{ $letter->signed_city ?? '-' }}</div>
                <div>pada tanggal : {{ optional($letter->signed_at)->locale('id')->translatedFormat('d F Y') ?? '-' }}</div>
                <div>{{ $letter->signatory_position ?? 'Kepala BPVP Surakarta' }}</div>
                <div class="name">{{ $letter->signatory_name ?? '-' }}</div>
                <div>{{ $letter->signatory_nip ? 'NIP. ' . $letter->signatory_nip : '' }}</div>
            </div>
        </div>
    @endforeach
</body>
</html>
