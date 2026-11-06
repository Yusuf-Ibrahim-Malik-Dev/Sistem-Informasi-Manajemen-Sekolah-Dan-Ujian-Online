<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kwitansi {{ $payment->payment_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #1e293b; margin: 0; padding: 32px; background: #f1f5f9; }
        .sheet { max-width: 760px; margin: 0 auto; background: #fff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 32px; }
        .head { display: flex; justify-content: space-between; gap: 16px; border-bottom: 2px solid #4338ca; padding-bottom: 16px; margin-bottom: 20px; }
        .brand { font-size: 18px; font-weight: 700; color: #4338ca; }
        .muted { color: #64748b; font-size: 12px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e2e8f0; font-size: 13px; }
        th { background: #f8fafc; }
        .right { text-align: right; }
        .total { font-size: 18px; font-weight: 700; color: #4338ca; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px; }
        .label { color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; }
        .foot { margin-top: 28px; display: flex; justify-content: space-between; gap: 24px; }
        .sign { width: 220px; text-align: center; font-size: 13px; }
        .sign .line { margin-top: 56px; border-top: 1px solid #94a3b8; padding-top: 6px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: 11px; font-weight: 700; }
        .actions { max-width: 760px; margin: 0 auto 16px; display: flex; justify-content: space-between; }
        .btn { border: 1px solid #cbd5e1; background: #fff; color: #334155; border-radius: 8px; padding: 8px 14px; font-size: 13px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #4338ca; border-color: #4338ca; color: #fff; }
        @media print { body { background: #fff; padding: 0; } .sheet { border: 0; border-radius: 0; padding: 0; max-width: none; } .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions">
        <a href="{{ route('admin.spp.payment.show', $payment) }}" class="btn">Kembali</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Cetak Kwitansi</button>
    </div>

    <div class="sheet">
        <div class="head">
            <div>
                <div class="brand">{{ $payment->school?->school_name ?? \App\Models\Konfigurasi::get('cbt_nama', 'Sekolah') }}</div>
                <div class="muted">{{ $payment->school?->address ?? \App\Models\Konfigurasi::get('cbt_alamat', '') }}</div>
                <div class="muted">{{ $payment->school?->phone ?? '' }} {{ $payment->school?->email ? '· '.$payment->school->email : '' }}</div>
            </div>
            <div class="right">
                <h1>KWITANSI PEMBAYARAN</h1>
                <div class="muted">{{ $payment->payment_number }}</div>
                <div class="badge">{{ $payment->status_label }}</div>
            </div>
        </div>

        <div class="grid">
            <div>
                <div class="label">Diterima Dari</div>
                <div><strong>{{ $payment->guardian?->ortu_nama ?? $payment->student?->nama ?? '-' }}</strong></div>
                <div class="muted">Siswa: {{ $payment->student?->nama ?? '-' }} ({{ $payment->student?->user_nis ?? '-' }})</div>
            </div>
            <div>
                <div class="label">Tanggal Pembayaran</div>
                <div><strong>{{ $payment->paid_at?->translatedFormat('d F Y H:i') ?? $payment->created_at?->translatedFormat('d F Y H:i') }}</strong></div>
                <div class="muted">Metode: {{ $payment->method?->method_name ?? 'Transfer / manual' }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr><th>Uraian</th><th class="right">Nominal</th></tr>
            </thead>
            <tbody>
                @forelse ($payment->allocations as $allocation)
                    <tr>
                        <td>Pembayaran tagihan <strong>{{ $allocation->bill?->bill_number ?? '-' }}</strong></td>
                        <td class="right">Rp {{ number_format((float) $allocation->allocated_amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td>Pembayaran tagihan SPP</td><td class="right">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td></tr>
                @endforelse
                <tr><td class="right"><strong>Total Dibayar</strong></td><td class="right total">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td></tr>
            </tbody>
        </table>

        @if ($payment->bill)
            <div class="grid" style="margin-top:16px">
                <div><div class="label">Sisa Tagihan</div><div>Rp {{ number_format($payment->bill->remaining_amount, 0, ',', '.') }}</div></div>
                <div><div class="label">Status Tagihan</div><div>{{ $payment->bill->status_label }}</div></div>
            </div>
        @endif

        <div class="foot">
            <div class="sign">
                <div>Orang Tua / Wali</div>
                <div class="line">{{ $payment->guardian?->ortu_nama ?? '-' }}</div>
            </div>
            <div class="sign">
                <div>Petugas / Admin</div>
                <div class="line">{{ $payment->verifier?->nama ?? 'Admin Sekolah' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
