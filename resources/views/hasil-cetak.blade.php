@php
    $maks = max(1, (float) $tes->tes_max_score);
    $persen = max(0, min(100, round(((float) $nilai / $maks) * 100)));
    $tidakDijawab = $attempt->soals->filter(fn ($s) => $s->tessoal_change_time === null)->count();
    $ragu = $attempt->soals->where('tessoal_ragu', 1)->count();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Hasil - {{ $tes->tes_nama }}</title>
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
        .score-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin: 16px 0;
        }
        .score-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }
        .score-box .value {
            font-size: 20px;
            font-weight: bold;
            line-height: 1.1;
        }
        .score-box .label {
            margin-top: 3px;
            color: #475569;
            font-size: 10px;
        }
        .section-title {
            margin: 20px 0 8px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .detail {
            width: 100%;
            border-collapse: collapse;
        }
        .detail th,
        .detail td {
            border: 1px solid #cbd5e1;
            padding: 6px 7px;
            vertical-align: top;
            text-align: left;
            font-size: 10.5px;
        }
        .detail th {
            background: #f1f5f9;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .detail .center { text-align: center; }
        .detail .text { white-space: pre-line; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 999px;
            font-size: 9.5px;
            font-weight: bold;
            white-space: nowrap;
        }
        .badge.green { background: #dcfce7; color: #166534; }
        .badge.red { background: #fee2e2; color: #991b1b; }
        .badge.gray { background: #e2e8f0; color: #334155; }
        .signatures {
            display: flex;
            justify-content: space-between;
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
            .detail tr { page-break-inside: avoid; }
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
                <div class="sub">Laporan Hasil Ujian Berbasis Komputer</div>
            </div>
            <div class="doc-meta">
                Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}<br>
                Dokumen: HASIL-{{ str_pad((string) $attempt->tesuser_id, 6, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <div class="doc-title">Laporan Hasil Ujian</div>

        <table class="identity">
            <tr>
                <td class="label">Nama Peserta</td>
                <td class="sep">:</td>
                <td>{{ $peserta?->nama ?? '-' }}</td>
                <td class="label">Nama Ujian</td>
                <td class="sep">:</td>
                <td>{{ $tes->tes_nama }}</td>
            </tr>
            <tr>
                <td class="label">Username</td>
                <td class="sep">:</td>
                <td>{{ $peserta?->user_name ?? '-' }}</td>
                <td class="label">Tanggal Ujian</td>
                <td class="sep">:</td>
                <td>{{ $attempt->tesuser_creation_time?->translatedFormat('d F Y, H:i') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Group</td>
                <td class="sep">:</td>
                <td>{{ $peserta?->grup?->grup_nama ?? '-' }}</td>
                <td class="label">Durasi</td>
                <td class="sep">:</td>
                <td>{{ $tes->tes_duration_time }} menit</td>
            </tr>
        </table>

        <div class="score-grid">
            <div class="score-box">
                <div class="value">{{ number_format((float) $nilai, 0) }}</div>
                <div class="label">Nilai</div>
            </div>
            <div class="score-box">
                <div class="value">{{ $persen }}%</div>
                <div class="label">Persentase</div>
            </div>
            <div class="score-box">
                <div class="value">{{ $benar }}</div>
                <div class="label">Jawaban Benar</div>
            </div>
            <div class="score-box">
                <div class="value">{{ $salah }}</div>
                <div class="label">Jawaban Salah</div>
            </div>
        </div>

        <table class="identity">
            <tr>
                <td class="label">Nilai Maksimal</td>
                <td class="sep">:</td>
                <td>{{ number_format($maks, 0) }}</td>
                <td class="label">Tidak Dijawab</td>
                <td class="sep">:</td>
                <td>{{ $tidakDijawab }}</td>
            </tr>
            <tr>
                <td class="label">Status Ujian</td>
                <td class="sep">:</td>
                <td>{{ $attempt->is_berjalan ? 'Berlangsung' : 'Selesai' }}</td>
                <td class="label">Ditandai Ragu</td>
                <td class="sep">:</td>
                <td>{{ $ragu }}</td>
            </tr>
        </table>

        @if ($tes->tes_detail_to_users)
            <div class="section-title">Rincian Jawaban</div>
            <table class="detail">
                <thead>
                    <tr>
                        <th class="center" style="width:34px">No</th>
                        <th>Soal</th>
                        <th style="width:180px">Jawaban Peserta</th>
                        <th style="width:160px">Kunci</th>
                        <th class="center" style="width:70px">Status</th>
                        <th class="center" style="width:52px">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attempt->soals as $item)
                        @php
                            $tipe = (int) ($item->soal?->soal_tipe ?? 1);
                            $selected = $item->pilihan->firstWhere('soaljawaban_selected', 1);
                            $kunci = $item->soal?->jawabans?->firstWhere('jawaban_benar', 1);
                            $jawabPeserta = '-';
                            $kunciTeks = '-';

                            if ($tipe === 1) {
                                $jawabPeserta = $selected?->jawaban?->jawaban_detail
                                    ? \App\Support\HtmlText::plain($selected->jawaban->jawaban_detail)
                                    : '-';
                                $kunciTeks = $kunci?->jawaban_detail
                                    ? \App\Support\HtmlText::plain($kunci->jawaban_detail)
                                    : '-';
                            } elseif ($tipe === 3) {
                                $jawabPeserta = $item->tessoal_jawaban_text ?: '-';
                                $kunciTeks = $item->soal?->soal_kunci ?: '-';
                            } else {
                                $jawabPeserta = $item->tessoal_jawaban_text ?: '-';
                                $kunciTeks = 'Dinilai guru';
                            }

                            if ($item->tessoal_change_time === null) {
                                $status = 'Kosong';
                                $badge = 'gray';
                            } elseif ($tipe === 1) {
                                $status = $selected && $selected->jawaban?->jawaban_benar ? 'Benar' : 'Salah';
                                $badge = $status === 'Benar' ? 'green' : 'red';
                            } elseif ($tipe === 3) {
                                $status = (float) $item->tessoal_nilai > 0 ? 'Benar' : 'Salah';
                                $badge = $status === 'Benar' ? 'green' : 'red';
                            } else {
                                $status = (float) $item->tessoal_nilai > 0 ? 'Dinilai' : 'Belum dinilai';
                                $badge = (float) $item->tessoal_nilai > 0 ? 'green' : 'gray';
                            }
                        @endphp
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td class="text">{{ \App\Support\HtmlText::plain($item->soal?->soal_detail) }}</td>
                            <td class="text">{{ $jawabPeserta }}</td>
                            <td class="text">{{ $kunciTeks }}</td>
                            <td class="center"><span class="badge {{ $badge }}">{{ $status }}</span></td>
                            <td class="center">{{ number_format((float) $item->tessoal_nilai, 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="signatures">
            <div class="signature">
                <div>Peserta</div>
                <div class="space"></div>
                <div class="name">{{ $peserta?->nama ?? '-' }}</div>
            </div>
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
