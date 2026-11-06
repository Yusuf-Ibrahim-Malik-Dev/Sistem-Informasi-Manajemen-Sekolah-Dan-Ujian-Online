@php
    $tanggalLahir = $peserta->user_birthdate
        ? \Illuminate\Support\Carbon::parse($peserta->user_birthdate)->translatedFormat('d F Y')
        : '-';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Keterangan Lulus - {{ $peserta->nama }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            background: #eef2f7;
            color: #0f172a;
            font-family: "Times New Roman", Times, serif;
            font-size: 13px;
        }
        .print-actions {
            max-width: 820px;
            margin: 0 auto 14px;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        .print-actions button,
        .print-actions a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: #0f172a;
            padding: 8px 14px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            text-decoration: none;
            cursor: pointer;
        }
        .print-actions button.primary {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #fff;
        }
        .sheet {
            max-width: 820px;
            margin: 0 auto;
            padding: 46px 54px;
            background: #fff;
            box-shadow: 0 10px 40px rgba(15, 23, 42, .12);
        }
        .header {
            text-align: center;
            padding-bottom: 14px;
            border-bottom: 3px double #0f172a;
        }
        .header h1 {
            margin: 0 0 4px;
            font-size: 22px;
            letter-spacing: .04em;
        }
        .header .sub {
            margin: 0;
            font-size: 12px;
            color: #475569;
        }
        .doc-title {
            margin: 26px 0 4px;
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            letter-spacing: .14em;
            text-transform: uppercase;
        }
        .doc-number {
            text-align: center;
            font-size: 12px;
            color: #475569;
            margin-bottom: 24px;
        }
        .body-text {
            line-height: 1.8;
            margin: 0 0 14px;
            text-align: justify;
        }
        .identity {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 20px;
        }
        .identity td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 13px;
        }
        .identity td.label { width: 160px; }
        .identity td.sep { width: 12px; }
        .identity td.value { font-weight: bold; }
        .raport {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 20px;
        }
        .raport th,
        .raport td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
            font-size: 12px;
            text-align: left;
        }
        .raport th { background: #f1f5f9; text-align: center; }
        .raport .center { text-align: center; }
        .raport .right { text-align: right; }
        .status {
            display: inline-block;
            margin: 4px 0 16px;
            padding: 10px 18px;
            border: 2px solid #047857;
            color: #047857;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: .18em;
        }
        .signatures {
            display: flex;
            justify-content: flex-end;
            margin-top: 34px;
            page-break-inside: avoid;
        }
        .signature {
            width: 280px;
            text-align: center;
            font-size: 13px;
        }
        .signature .space { height: 72px; }
        .signature .name {
            border-top: 1px solid #0f172a;
            padding-top: 5px;
            font-weight: bold;
        }
        .footer {
            margin-top: 34px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 9px;
            text-align: center;
        }
        @page { size: A4; margin: 18mm; }
        @media print {
            body { padding: 0; background: #fff; }
            .no-print { display: none !important; }
            .sheet { max-width: none; padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <a href="{{ $kembali }}">Kembali</a>
        <button type="button" class="primary" onclick="window.print()">Cetak Sekarang</button>
        <button type="button" onclick="window.close()">Tutup</button>
    </div>

    <div class="sheet">
        <div class="header">
            <h1>{{ $namaCbt }}</h1>
            <p class="sub">{{ $alamat ?: 'Lembaga Penyelenggara Pendidikan' }}</p>
        </div>

        <div class="doc-title">Surat Keterangan Lulus</div>
        <div class="doc-number">Nomor: {{ $kelulusan['nomor'] }}</div>

        <p class="body-text">
            Yang bertanda tangan di bawah ini, Kepala {{ $namaCbt }}, menerangkan bahwa:
        </p>

        <table class="identity">
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="sep">:</td>
                <td class="value">{{ $peserta->nama }}</td>
            </tr>
            <tr>
                <td class="label">Nama Pengguna / NIS</td>
                <td class="sep">:</td>
                <td class="value">{{ $peserta->user_name }}</td>
            </tr>
            <tr>
                <td class="label">Tempat, Tanggal Lahir</td>
                <td class="sep">:</td>
                <td class="value">{{ $peserta->user_birthplace ?: '-' }}, {{ $tanggalLahir }}</td>
            </tr>
            <tr>
                <td class="label">Kelas</td>
                <td class="sep">:</td>
                <td class="value">{{ $kelulusan['kelas'] }}</td>
            </tr>
            <tr>
                <td class="label">Tahun Ajaran</td>
                <td class="sep">:</td>
                <td class="value">{{ $tahunAjaran ?: '-' }}</td>
            </tr>
        </table>

        <p class="body-text">
            Berdasarkan hasil penilaian raport seluruh mata pelajaran, peserta tersebut dinyatakan:
        </p>

        <div class="status">LULUS</div>

        <table class="raport">
            <thead>
                <tr>
                    <th style="width:44px">No</th>
                    <th>Mata Pelajaran</th>
                    <th style="width:90px">Nilai</th>
                    <th style="width:90px">Predikat</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kelulusan['moduls'] as $index => $modul)
                    @php $raport = $kelulusan['rapors']->get($modul->modul_id); @endphp
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $modul->modul_nama }}</td>
                        <td class="right">{{ $raport ? number_format((float) $raport->raport_nilai, 1) : '-' }}</td>
                        <td class="center">{{ $raport?->predikat ?? '-' }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" class="right"><strong>Rata-rata</strong></td>
                    <td class="right"><strong>{{ $kelulusan['rata_rata'] !== null ? number_format($kelulusan['rata_rata'], 1) : '-' }}</strong></td>
                    <td class="center"><strong>{{ $kelulusan['predikat'] }}</strong></td>
                </tr>
            </tbody>
        </table>

        <p class="body-text">
            Surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.
        </p>

        <div class="signatures">
            <div class="signature">
                <div>Jakarta, {{ $kelulusan['tanggal']->translatedFormat('d F Y') }}</div>
                <div>Kepala {{ $namaCbt }}</div>
                <div class="space"></div>
                <div class="name">{{ $kepalaSekolah }}</div>
                <div>NIP. {{ $nipKepalaSekolah ?: '-' }}</div>
            </div>
        </div>

        <div class="footer">
            Dokumen ini dicetak dari {{ $namaCbt }} pada {{ now()->translatedFormat('d F Y, H:i') }}.
        </div>
    </div>

    @if (request('auto'))
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 400);
            });
        </script>
    @endif
</body>
</html>
