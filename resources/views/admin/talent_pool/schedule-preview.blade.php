@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Jadwal Pelatihan (Pratinjau)</h4>
        <small class="text-muted">Tampilan templat jadwal instansi; klik "Unduh/Cetak" untuk PDF.</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="fas fa-file-pdf"></i> Unduh / Cetak
        </button>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="schedule-sheet print-area">
            <div class="hdr">
                <div class="hdr-topbar"></div>
                <div class="hdr-body">
                    <div class="hdr-logo">
                        <div class="hdr-logo-placeholder">LOGO</div>
                    </div>
                    <div class="hdr-center">
                        <div class="hdr-line hdr-line1">KEMENTERIAN KETENAGAKERJAAN RI</div>
                        <div class="hdr-line hdr-line2">DIREKTORAT JENDERAL PEMBINAAN PELATIHAN VOKASI DAN PRODUKTIVITAS</div>
                        <div class="hdr-line hdr-line3">BALAI PELATIHAN VOKASI DAN PRODUKTIVITAS SURAKARTA</div>
                        <div class="hdr-line hdr-line4">Jalan Bhayangkara No. 38 Telp/Fax. (0271) 714885&nbsp;&nbsp;Surakarta</div>
                        <div class="hdr-line hdr-line5">Laman : http://www.naker.go.id</div>
                    </div>
                    <div class="hdr-meta">
                        <table class="hdr-meta-table">
                            <tbody>
                                <tr>
                                    <td class="hdr-meta-top" colspan="2">{{ $meta['nomor'] }}</td>
                                    <td class="hdr-meta-top">Hal : {{ $meta['hal'] }}</td>
                                </tr>
                                <tr>
                                    <td class="hdr-meta-lbl">No Terbit</td>
                                    <td class="hdr-meta-lbl">No. Rev</td>
                                    <td class="hdr-meta-lbl">Tanggal terbit :</td>
                                </tr>
                                <tr>
                                    <td class="hdr-meta-val">{{ $meta['no_terbit'] }}</td>
                                    <td class="hdr-meta-val">{{ $meta['no_rev'] }}</td>
                                    <td class="hdr-meta-val">{{ $meta['tanggal_terbit'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="hdr-title">JADWAL - PELATIHAN</div>
            </div>

            <div class="info-pairs">
                <div class="info-box">
                    <div class="info-row"><div class="label">KEJURUAN</div><div class="value">: {{ $meta['kejuruan'] }}</div></div>
                    <div class="info-row"><div class="label">SUB KEJURUAN</div><div class="value">: {{ $meta['sub_kejuruan'] }}</div></div>
                    <div class="info-row"><div class="label">PROGRAM PELATIHAN</div><div class="value">: {{ $meta['program'] }}</div></div>
                    <div class="info-row"><div class="label">PBK ANGKATAN KE-</div><div class="value">: {{ $meta['pbk_ke'] }}</div></div>
                    <div class="info-row"><div class="label">TAHUN</div><div class="value">: {{ $meta['tahun'] }}</div></div>
                </div>
                <div class="info-box">
                    <div class="info-row"><div class="label">JENIS PELATIHAN</div><div class="value">: {{ $meta['jenis_pelatihan'] }}</div></div>
                    <div class="info-row"><div class="label">MINGGU KE</div><div class="value">: {{ $meta['minggu_ke'] }}</div></div>
                    <div class="info-row"><div class="label">BULAN</div><div class="value">: {{ $meta['bulan'] }}</div></div>
                    <div class="info-row"><div class="label">TANGGAL</div><div class="value">: {{ $meta['tanggal'] }}</div></div>
                </div>
            </div>

            <div class="toolbar">
                <div class="input-group search">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Cari kode unit / instruktur / label (BREAK, ISOMA, Apel Pagi)…">
                </div>
                <div class="toolbar-actions">
                    <span class="badge bg-light text-dark border d-none d-md-inline-flex">Templat</span>
                    <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-print me-1"></i> Cetak</button>
                    <button class="btn btn-outline-secondary btn-sm"><i class="fas fa-download me-1"></i> Ekspor</button>
                    <button class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="schedule-table">
                    <colgroup>
                        <col style="width:50px">
                        <col style="width:170px">
                        <col style="width:50px">
                        <col style="width:170px">
                        @foreach($days as $day)
                            <col style="width:220px">
                            <col style="width:60px">
                        @endforeach
                    </colgroup>
                    <thead>
                        <tr>
                            <th rowspan="3">NO</th>
                            <th rowspan="3" class="waktukol">WAKTU<div class="sub">SENIN-KAMIS</div></th>
                            <th rowspan="3">NO</th>
                            <th rowspan="3" class="waktukol">WAKTU<div class="sub">JUM'AT</div></th>
                            @foreach($days as $day)
                                <th colspan="2">{{ $day['label'] }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($days as $day)
                                <th class="date-cell" colspan="2">{{ $day['date'] }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($days as $day)
                                <th class="kode">KODE UNIT</th>
                                <th class="inst">INST.</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php($daySpan = collect($days)->mapWithKeys(fn($d) => [$d['key'] => 0])->all())
                        @php($friSpan = 0)
                        @foreach($schedule as $row)
                            @php($sessions = $row['sessions'] ?? [])
                            <tr class="{{ ($row['is_break'] ?? false) ? 'break-row' : '' }}">
                                <td class="center fw-bold">{{ $row['no_l'] ?? '' }}</td>
                                <td class="center fw-bold {{ ($row['is_break'] ?? false) ? 'muted' : '' }}">{{ $row['time_mon_thu'] ?? '' }}</td>
                                <td class="center fw-bold">{{ $row['no_r'] ?? '' }}</td>
                                @if($friSpan > 0)
                                    @php($friSpan--)
                                @else
                                    @if(!empty($row['time_fri']))
                                        @php($span = $row['time_fri_row_span'] ?? 1)
                                        @php($friSpan = ($span > 1) ? $span - 1 : 0)
                                        <td class="center fw-bold {{ ($row['is_fri_special'] ?? false) ? 'fri-special' : (($row['is_break'] ?? false) ? 'muted' : '') }}" @if($span > 1) rowspan="{{ $span }}" @endif>
                                            {{ $row['time_fri'] }}
                                        </td>
                                    @else
                                        <td class="center fw-bold"></td>
                                    @endif
                                @endif

                                @foreach($days as $day)
                                    @php($key = $day['key'])
                                    @if($daySpan[$key] > 0)
                                        @php($daySpan[$key]--)
                                        @continue
                                    @endif

                                    @php($cell = $sessions[$key] ?? null)

                                    @if(($day['hatched'] ?? false) && (!$cell || (!($cell['code'] ?? null) && !($cell['label'] ?? null))))
                                        <td class="center hatch" colspan="2"></td>
                                        @continue
                                    @endif

                                    @if(!$cell)
                                        <td class="center"></td><td class="center"></td>
                                        @continue
                                    @endif

                                    @php($rowspan = $cell['row_span'] ?? 1)
                                    @php($colspan = $cell['col_span'] ?? 2)
                                    @php($label = $cell['label'] ?? null)
                                    @php($isLabel = !empty($label))

                                    @if($rowspan > 1)
                                        @php($daySpan[$key] = $rowspan - 1)
                                    @endif

                                    @if($isLabel)
                                        <td class="center {{ in_array(strtoupper($label), ['BREAK','ISOMA']) ? 'dotfill' : 'apel-cell' }}" colspan="{{ $colspan }}" @if($rowspan > 1) rowspan="{{ $rowspan }}" @endif>
                                            {{ $label }}
                                        </td>
                                        @if($colspan === 1)
                                            <td class="center"></td>
                                        @endif
                                    @else
                                        <td class="center">{{ $cell['code'] ?? '' }}</td>
                                        <td class="center">{{ $cell['inst'] ?? '' }}</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="legend">
                <div class="legend-title">Keterangan Unit Kompetensi :</div>
                <div class="legend-grid">
                    @foreach(array_chunk($unitDescriptions, ceil(count($unitDescriptions)/2)) as $chunk)
                        <div class="legend-col">
                            @foreach($chunk as $idx => $u)
                                <div class="legend-row">
                                    <span class="n">{{ $loop->index + 1 + ($loop->parent->index * ceil(count($unitDescriptions)/2)) }}.</span>
                                    <span class="code">{{ $u['code'] }}</span>
                                    <span class="colon">:</span>
                                    <span class="desc">{{ $u['desc'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div class="legend-inst">
                    <div class="legend-inst-title">Kode Nama Instruktur/Pelatih :</div>
                    <div class="legend-inst-row"><span class="code">{{ $trainer['code'] }}</span><span class="colon">:</span>{{ $trainer['name'] }}</div>
                </div>
            </div>

@php($sig = $signatures ?? [
    'knowing_title_1' => 'Mengetahui/Menyetujui:',
    'knowing_title_2' => 'An. Kepala BPVP Surakarta',
    'knowing_title_3' => 'Koordinator Satuan Pelayanan Bantul',
    'knowing_name' => 'RADEN ROHADIJANTO, S.E., M.Sc.',
    'knowing_nip' => 'NIP 19690719 199603 1 003',
    'mid_left_role' => 'Ketua Kejuruan',
    'mid_left_program' => 'GARMEN APPAREL',
    'mid_right_role' => 'Ketua',
    'mid_right_program' => 'Program Pelatihan',
    'mid_left_name' => 'PURI ARIMA K., S.T.',
    'mid_left_nip' => 'NIP 19850103 200901 2 004',
    'mid_right_name' => $trainer['name'] ?? 'Wanda Verdita, S.Pd.',
    'mid_right_nip' => 'NIP 19920920 201801 2 004',
])
            <div class="sign">
                <div class="sign-left">
                    <div class="sign-title">{{ $sig['knowing_title_1'] ?? 'Mengetahui/Menyetujui:' }}</div>
                    <div class="sign-sub">{{ $sig['knowing_title_2'] ?? 'An. Kepala BPVP Surakarta' }}</div>
                    <div class="sign-sub">{{ $sig['knowing_title_3'] ?? 'Koordinator Satuan Pelayanan Bantul' }}</div>
                    <div class="sign-space"></div>
                    <div class="sign-name-left">
                        <strong>{{ $sig['knowing_name'] ?? 'RADEN ROHADIJANTO, S.E., M.Sc.' }}</strong><br>
                        <span>{{ $sig['knowing_nip'] ?? 'NIP 19690719 199603 1 003' }}</span>
                    </div>
                </div>
                <div class="sign-right">
                    <div class="sign-mid">
                        <div class="center">
                            <div class="sign-role">{{ $sig['mid_left_role'] ?? 'Ketua Kejuruan' }}</div>
                            <div class="sign-program">{{ $sig['mid_left_program'] ?? ($meta['kelas'] ?? '') }}</div>
                        </div>
                        <div class="right">
                            <div class="sign-role">{{ $sig['mid_right_role'] ?? 'Ketua' }}</div>
                            <div class="sign-program">{{ $sig['mid_right_program'] ?? 'Program Pelatihan' }}</div>
                        </div>
                    </div>
                    <div class="sign-space"></div>
                    <div class="sign-name">
                        <div class="center">
                            <strong>{{ $sig['mid_left_name'] ?? 'PURI ARIMA K., S.T.' }}</strong><br>
                            <span>{{ $sig['mid_left_nip'] ?? 'NIP 19850103 200901 2 004' }}</span>
                        </div>
                        <div class="right">
                            <strong>{{ $sig['mid_right_name'] ?? ($trainer['name'] ?? '') }}</strong><br>
                            <span>{{ $sig['mid_right_nip'] ?? 'NIP 19920920 201801 2 004' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-note text-center text-muted small mt-3">© Templat UI Jadwal Pelatihan — Sesuaikan konten sesuai format dokumen internal.</div>
        </div>
    </div>
</div>

<style>
.schedule-sheet { font-size: 12px; color:#111; }
.hdr { background:#fff; margin-bottom: 12px; }
.hdr-topbar { height: 12px; background:#111; }
.hdr-body { display:grid; grid-template-columns: 120px 1fr 260px; gap:16px; align-items:center; padding:10px 16px; }
.hdr-logo { display:flex; align-items:center; justify-content:center; }
.hdr-logo-placeholder { width:112px; height:112px; border-radius:10px; background:#fff; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:12px; }
.hdr-center { text-align:center; padding:0 8px; }
.hdr-line { color:#000; line-height:1.2; }
.hdr-line1 { font-weight:900; font-size:20px; letter-spacing:0.4px; }
.hdr-line2 { font-weight:700; font-size:14px; margin-top:2px; }
.hdr-line3 { font-weight:900; font-size:22px; margin-top:8px; }
.hdr-line4 { font-weight:700; font-size:16px; margin-top:8px; }
.hdr-line5 { font-weight:700; font-size:16px; margin-top:6px; }
.hdr-meta { display:flex; justify-content:flex-end; }
.hdr-meta-table { border-collapse:collapse; border:2px solid #0b0b0b; background:#fff; width:220px; table-layout:fixed; }
.hdr-meta-table td { border:2px solid #0b0b0b; padding:4px 5px; font-size:13px; font-weight:800; color:#000; }
.hdr-meta-top { text-align:center; font-weight:900; }
.hdr-meta-lbl { text-align:left; font-weight:800; }
.hdr-meta-val { text-align:center; font-weight:900; }
.hdr-title { background:#111; color:#fff; text-align:center; font-weight:900; font-size:30px; padding:14px 0; letter-spacing:1px; }

.info-pairs { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px; }
.info-box { border:1px solid #000; border-radius:8px; padding:10px 12px; background:#fff; }
.info-row { display:flex; gap:6px; font-size:12px; font-weight:700; line-height:1.4; }
.info-row .label { min-width:170px; color:#444; font-weight:600; }
.info-row .value { flex:1; }

.toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:space-between; margin-bottom:12px; }
.toolbar .search { max-width:460px; flex:1; }
.toolbar-actions { display:flex; gap:8px; align-items:center; }

.table-wrapper { overflow-x:auto; }
.schedule-table { border-collapse: collapse; border: 2px solid #000; font-size: 12px; width: max-content; }
.schedule-table th, .schedule-table td { border: 1px solid #000; padding: 6px 8px; vertical-align: middle; background: #fff; }
.schedule-table th { text-align: center; font-weight: 900; }
.schedule-table .waktukol { width: 9%; }
.schedule-table .kode { width: 8%; }
.schedule-table .inst { width: 5%; }
.schedule-table .center { text-align: center; }
.schedule-table .sub { font-size: 10px; font-weight: 800; margin-top: 2px; letter-spacing: 0.3px; }
.date-cell { background:#7a7a7a; color:#fff; font-weight: 900; position: relative; }
.date-cell::before { content: ''; position: absolute; left: 0; top: 0; border-left: 12px solid #2ea043; border-bottom: 12px solid transparent; }
.apel-cell { background:#f2f2f2; font-weight:900; }
.break-row td { background:#f5f5f5; }
.dotfill { background-color: #d9d9d9; background-image: radial-gradient(#000 0.6px, transparent 0.6px); background-size: 6px 6px; font-style: italic; font-weight: 900; }
.muted { background:#e4e4e4; font-style: italic; }
.hatch { background-color: #f1f1f1; background-image: radial-gradient(#000 0.6px, transparent 0.6px); background-size: 4px 4px; }
.fri-special { font-style: italic; text-decoration: line-through; }

.legend { font-size:14px; margin-top:12px; }
.legend-title { font-weight:900; margin-bottom:4px; }
.legend-grid { display:grid; grid-template-columns: 1fr 1fr; gap:32px; }
.legend-row { display:flex; align-items:flex-start; gap:6px; margin-bottom:4px; line-height:1.25; }
.legend-row .n { width:24px; flex:0 0 24px; }
.legend-row .code { min-width:170px; font-weight:700; }
.legend-row .colon { width:10px; text-align:center; }
.legend-inst { margin-top:10px; }
.legend-inst-title { font-style:italic; margin-bottom:2px; }
.legend-inst-row { display:flex; gap:6px; font-weight:700; }
.legend-inst-row .code { min-width:40px; }

.sign { display:grid; grid-template-columns: 1fr 1fr; font-size:14px; margin-top:28px; gap:16px; }
.sign-title { font-weight:900; margin-bottom:4px; }
.sign-sub { font-weight:700; margin-bottom:2px; }
.sign-space { height:80px; }
.sign-mid { display:grid; grid-template-columns: 1fr 1fr; margin-top:18px; }
.sign-program { font-weight:700; }
.sign-name { display:grid; grid-template-columns: 1fr 1fr; margin-top:6px; }
.center { text-align:center; }
.right { text-align:right; }
.sign span { font-size:13px; }

@media print {
    body * { visibility: hidden; }
    .print-area, .print-area * { visibility: visible; }
    .print-area { position: absolute; left: 0; top: 0; width: 100%; }
    .card, .card-body { border: none !important; box-shadow: none !important; }
    button { display: none; }
    body { background: #fff; }
}
</style>
@endsection
