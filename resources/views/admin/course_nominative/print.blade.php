<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Nominatif</title>
    <style>
        body { font-family: "Arial", sans-serif; font-size: 11px; color: #111; margin: 24px; position: relative; }
        .header { text-align: center; line-height: 1.15; }
        .header h1 { font-size: 13px; margin: 0; font-weight: 700; }
        .header h2 { font-size: 12px; margin: 0; font-weight: 700; }
        .header h3 { font-size: 11px; margin: 0; font-weight: 700; }
        .header .sub { font-size: 10px; }
        .logo { width: 68px; }
        .title-bar { margin-top: 8px; background: #111; color: #fff; text-align: center; padding: 4px 8px; font-weight: 700; letter-spacing: 0.3px; }
        .meta { width: 100%; margin-top: 6px; border-collapse: collapse; }
        .meta td { vertical-align: top; font-size: 10.5px; padding: 2px 4px; }
        .meta .label { width: 150px; font-weight: 700; }
        .meta .divider { width: 8px; }
        .meta .box { border: 1px solid #333; height: 20px; width: 150px; }
        table.nominatif { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.nominatif th, table.nominatif td { border: 1px solid #333; padding: 4px 6px; font-size: 9.8px; }
        table.nominatif thead th { text-align: center; font-weight: 700; }
        table.nominatif .no { width: 24px; text-align: center; }
        table.nominatif .no-induk { width: 90px; }
        table.nominatif .nama { width: 140px; }
        table.nominatif .ttl { width: 150px; }
        table.nominatif .jk { width: 70px; text-align: center; }
        table.nominatif .pendidikan { width: 90px; text-align: center; }
        table.nominatif .alamat { width: 220px; }
        table.nominatif .hp { width: 90px; text-align: center; }
        table.nominatif .ket { width: 80px; }
        .footer { margin-top: 12px; text-align: right; font-size: 10px; }
        .signatory { margin-top: 6px; text-align: right; font-size: 10px; }
        .signatory .position { margin-top: 4px; }
        .signatory .name { margin-top: 36px; font-weight: 700; }
        .doc-meta { position: absolute; top: 0; right: 0; border-collapse: collapse; font-size: 8.5px; }
        .doc-meta td { border: 1px solid #333; padding: 2px 4px; text-align: center; line-height: 1.1; white-space: nowrap; }
        .doc-meta .code { text-align: left; }
    </style>
</head>
<body>
    <table class="doc-meta">
        <tr>
            <td class="code" colspan="2">SKA/PP/FM/13-06</td>
            <td>Hal: 1 Dari 1</td>
        </tr>
        <tr>
            <td>No Terbit<br>A</td>
            <td>No Revisi<br>2</td>
            <td>Tgl. Terbit<br>27/02/2014</td>
        </tr>
    </table>
    <table style="width:100%;">
        <tr>
            <td style="width:80px;"><img src="{{ asset('image/logo/logo_kemnaker.svg') }}" alt="Kemnaker" class="logo"></td>
            <td class="header">
                <h1>KEMENTERIAN KETENAGAKERJAAN RI</h1>
                <h2>DIREKTORAT JENDERAL</h2>
                <h2>PEMBINAAN PELATIHAN VOKASI DAN PRODUKTIVITAS</h2>
                <h2>BALAI PELATIHAN VOKASI DAN PRODUKTIVITAS</h2>
                <div class="sub">Jalan Bhayangkara Nomor 38 Surakarta 57149, Telepon (0271) 714885, Faksimile (0271) 711646</div>
                <div class="sub">Laman : http://www.naker.go.id</div>
            </td>
        </tr>
    </table>

    <div class="title-bar">DAFTAR NOMINATIF PESERTA&nbsp;&nbsp;PELATIHAN DURASI SINGKAT PERIODE (WEEK) {{ $periodeWeek ?? '-' }} | {{ $periodeTahun }}</div>

    <table class="meta">
        <tr>
            <td class="label">KEJURUAN</td>
            <td class="divider">:</td>
            <td>{{ strtoupper($kejuruan ?? '-') }}</td>
            <td class="label" style="text-align:right;">JENIS PELATIHAN</td>
            <td class="divider">:</td>
            <td>{{ strtoupper($jenisPelatihan ?? '-') }}</td>
        </tr>
        <tr>
            <td class="label">PROGRAM PELATIHAN</td>
            <td class="divider">:</td>
            <td>{{ strtoupper($programPelatihan ?? '-') }}</td>
            <td class="label" style="text-align:right;">JUMLAH PESERTA</td>
            <td class="divider">:</td>
            <td>{{ $jumlahPeserta }} ORANG</td>
        </tr>
        <tr>
            <td class="label"></td>
            <td class="divider"></td>
            <td><div class="box"></div></td>
            <td class="label" style="text-align:right;">LOKASI PELATIHAN</td>
            <td class="divider">:</td>
            <td>{{ strtoupper($lokasiPelatihan ?? '-') }}</td>
        </tr>
        <tr>
            <td class="label"></td>
            <td class="divider"></td>
            <td></td>
            <td class="label" style="text-align:right;">TANGGAL PELAKSANAAN</td>
            <td class="divider">:</td>
            <td>{{ strtoupper($tanggalPelaksanaan) }}</td>
        </tr>
    </table>

    <table class="nominatif">
        <thead>
            <tr>
                <th class="no">NO</th>
                <th class="no-induk">NO. INDUK SISWA</th>
                <th class="nama">N A M A</th>
                <th class="ttl">TEMPAT/TGL LAHIR</th>
                <th class="jk">JENIS<br>KELAMIN</th>
                <th class="pendidikan">PENDIDIKAN<br>TERAKHIR</th>
                <th class="alamat">ALAMAT</th>
                <th class="hp">NO. TELP/HP</th>
                <th class="ket">KETERANGAN</th>
            </tr>
            <tr>
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>6</th>
                <th>7</th>
                <th>8</th>
                <th>9</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td class="no">{{ $row['no'] }}</td>
                    <td class="no-induk">{{ $row['no_induk'] }}</td>
                    <td class="nama">{{ $row['nama'] }}</td>
                    <td class="ttl">{{ $row['ttl'] }}</td>
                    <td class="jk">{{ $row['gender'] }}</td>
                    <td class="pendidikan">{{ $row['education'] }}</td>
                    <td class="alamat">{{ $row['address'] }}</td>
                    <td class="hp">{{ $row['phone'] }}</td>
                    <td class="ket">{{ $row['note'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;">-</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">{{ $signedCity }}, {{ $signedAt?->locale('id')->translatedFormat('d F Y') }}</div>
    <div class="signatory">
        <div class="position">{{ $signatoryPosition }}</div>
        <div class="name">{{ $signatoryName }}</div>
        <div>{{ $signatoryNip ? 'NIP.' . $signatoryNip : '' }}</div>
    </div>
</body>
</html>
