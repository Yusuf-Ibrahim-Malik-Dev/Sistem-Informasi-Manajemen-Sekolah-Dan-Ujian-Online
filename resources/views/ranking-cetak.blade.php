@php
    $tertinggi = collect($ranking)->max('nilai') ?? 0;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Ranking - {{ $tes->tes_nama }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            background: #eef2f7;
            color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }
        .print-actions {
            max-width: 900px;
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
            max-width: 900px;
            margin: 0 auto;
            padding: 32px;
            background: #fff;
            box-shadow: 0 10px 40px rgba(15, 23, 42, .12);
        }
        .header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding-bottom: 14px;
            border-bottom: 3px double #0f172a;
        }
        .header h1 {
            margin: 0 0 4px;
            font-size: 19px;
            letter-spacing: .02em;
        }
        .header .sub {
            margin: 0;
            color: #475569;
            font-size: 11px;
        }
        .doc-meta {
            text-align: right;
            color: #475569;
            font-size: 10px;
            line-height: 1.6;
            white-space: nowrap;
        }
        .doc-title {
            margin: 18px 0 14px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .identity {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .identity td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 11px;
        }
        .identity td.label {
            width: 130px;
            color: #475569;
        }
        .identity td.sep {
            width: 10px;
            color: #475569;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin: 16px 0;
        }
        .summary-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }
        .summary-box .value {
            font-size: 20px;
            font-weight: bold;
            line-height: 1.1;
        }
        .summary-box .label {
            margin-top: 3px;
            color: #475569;
            font-size: 10px;
        }
        .ranking {
            width: 100%;
            border-collapse: collapse;
        }
        .ranking th,
        .ranking td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            vertical-align: middle;
            text-align: left;
            font-size: 10.5px;
        }
        .ranking th {
            background: #f1f5f9;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .ranking .center { text-align: center; }
        .ranking .right { text-align: right; }
        .ranking .rank {
            width: 55px;
            text-align: center;
            font-weight: bold;
        }
        .ranking .nilai {
            width: 90px;
            text-align: right;
            font-weight: bold;
        }
        .ranking tbody tr:nth-child(even) { background: #f8fafc; }
        .signatures {
            display: flex;
            justify-content: flex-end;
            gap: 40px;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature {
            width: 220px;
            text-align: center;
            font-size: 11px;
        }
        .signature .space {
            height: 64px;
        }
        .signature .name {
            border-top: 1px solid #0f172a;
            padding-top: 5px;
            font-weight: bold;
        }
        .footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 9px;
            text-align: center;
        }
        @page { size: A4; margin: 14mm; }
        @media print {
            body { padding: 0; background: #fff; }
            .no-print { display: none !important; }
            .sheet { max-width: none; padding: 0; box-shadow: none; }
            .ranking tr { page-break-inside: avoid; }
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
            <div>
                <h1>{{ $namaCbt }}</h1>
                <div class="sub">Laporan Ranking Ujian Berbasis Komputer</div>
            </div>
            <div class="doc-meta">
                Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}<br>
                Dokumen: RANKING-{{ str_pad((string) $tes->tes_id, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <div class="doc-title">Laporan Ranking Ujian</div>

        <table class="identity">
            <tr>
                <td class="label">Nama Ujian</td>
                <td class="sep">:</td>
                <td>{{ $tes->tes_nama }}</td>
                <td class="label">Guru</td>
                <td class="sep">:</td>
                <td>{{ $namaGuru }}</td>
            </tr>
            <tr>
                <td class="label">Durasi</td>
                <td class="sep">:</td>
                <td>{{ $tes->tes_duration_time }} menit</td>
                <td class="label">Peserta Selesai</td>
                <td class="sep">:</td>
                <td>{{ count($ranking) }} peserta</td>
            </tr>
        </table>

        <div class="summary">
            <div class="summary-box">
                <div class="value">{{ count($ranking) }}</div>
                <div class="label">Peserta Selesai</div>
            </div>
            <div class="summary-box">
                <div class="value">{{ number_format((float) $tertinggi, 1) }}</div>
                <div class="label">Nilai Tertinggi</div>
            </div>
            <div class="summary-box">
                <div class="value">{{ number_format((float) $tes->tes_max_score, 0) }}</div>
                <div class="label">Nilai Maksimal</div>
            </div>
        </div>

        <table class="ranking">
            <thead>
                <tr>
                    <th class="rank">Rank</th>
                    <th>Nama Peserta</th>
                    <th>Username</th>
                    <th>Group</th>
                    <th class="nilai">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ranking as $item)
                    <tr>
                        <td class="rank">#{{ $item['rank'] }}</td>
                        <td>{{ $item['nama'] }}</td>
                        <td>{{ $item['username'] }}</td>
                        <td>{{ $item['grup'] }}</td>
                        <td class="nilai">{{ number_format((float) $item['nilai'], 1) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center">Belum ada peserta yang menyelesaikan ujian ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="signatures">
            <div class="signature">
                <div>Guru</div>
                <div class="space"></div>
                <div class="name">{{ $namaGuru }}</div>
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
