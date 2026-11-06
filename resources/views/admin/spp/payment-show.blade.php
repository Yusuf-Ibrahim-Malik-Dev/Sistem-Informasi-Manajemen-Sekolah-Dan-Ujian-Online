@extends('layouts.admin')

@section('title', 'Detail Pembayaran SPP')
@section('heading', 'Detail Pembayaran SPP')
@section('eyebrow', 'Keuangan')

@section('content')
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3" data-aos="fade-up">
        <div>
            <div class="text-muted-2" style="font-size:.78rem">{{ $payment->payment_number }}</div>
            <div class="fw-bold" style="font-size:1.05rem">{{ $payment->student?->nama ?? '-' }}</div>
            <div class="text-muted-2" style="font-size:.8rem">{{ $payment->student?->grup?->grup_nama ?? '-' }} &middot; {{ $payment->school?->school_name ?? '-' }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.spp.payments') }}" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            @if ($payment->status === \App\Models\SchoolPayment::STATUS_VERIFIED)
                <a href="{{ route('admin.spp.payment.receipt', $payment) }}" class="btn btn-gradient"><i class="bi bi-printer me-1"></i> Kwitansi</a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7" data-aos="fade-up">
            <div class="glass-card card-pad mb-4">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Informasi Pembayaran</h3>
                        <p class="section-sub">Diajukan {{ $payment->created_at?->translatedFormat('d F Y H:i') ?? '-' }}</p>
                    </div>
                    <span class="badge-soft {{ $payment->status_color }}">{{ $payment->status_label }}</span>
                </div>

                <div class="row g-3" style="font-size:.84rem">
                    <div class="col-md-6"><div class="text-muted-2">Nominal</div><div class="fw-bold">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</div></div>
                    <div class="col-md-6"><div class="text-muted-2">Metode</div><div class="fw-semibold">{{ $payment->method?->method_name ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted-2">Tagihan</div><div class="fw-semibold">{{ $payment->bill?->bill_number ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted-2">Orang Tua</div><div class="fw-semibold">{{ $payment->guardian?->ortu_nama ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted-2">Tanggal Bayar</div><div class="fw-semibold">{{ $payment->paid_at?->translatedFormat('d M Y H:i') ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted-2">Diverifikasi Oleh</div><div class="fw-semibold">{{ $payment->verifier?->nama ?? '-' }}</div></div>
                    @if ($payment->notes)
                        <div class="col-12"><div class="text-muted-2">Catatan Orang Tua</div><div>{{ $payment->notes }}</div></div>
                    @endif
                    @if ($payment->rejection_reason)
                        <div class="col-12"><div class="text-muted-2">Alasan Penolakan</div><div class="text-danger">{{ $payment->rejection_reason }}</div></div>
                    @endif
                </div>

                @if ($payment->proof_path)
                    <div class="mt-4">
                        <a href="{{ route('admin.spp.payment.proof', $payment) }}" target="_blank" class="btn btn-soft">
                            <i class="bi bi-paperclip me-1"></i> Lihat Bukti Pembayaran
                        </a>
                    </div>
                @else
                    <div class="mt-4 p-3" style="border-radius:12px;background:#f8fafc;border:1px solid var(--line);font-size:.82rem">
                        <i class="bi bi-info-circle me-1"></i> Pembayaran ini dicatat langsung oleh admin (tanpa bukti unggahan).
                    </div>
                @endif
            </div>

            @if ($payment->bill)
                <div class="glass-card card-pad">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Rincian Tagihan</h3>
                            <p class="section-sub">{{ $payment->bill->bill_number }} &middot; {{ $payment->bill->status_label }}</p>
                        </div>
                        <a href="{{ route('admin.spp.show', $payment->bill) }}" class="btn btn-soft btn-sm">Buka Tagihan</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-modern align-middle">
                            <thead>
                                <tr><th>Deskripsi</th><th class="text-end">Subtotal</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($payment->bill->items as $item)
                                    <tr>
                                        <td style="font-size:.82rem">{{ $item->description }}</td>
                                        <td class="text-end text-nowrap">Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr><td class="text-end fw-bold">Total Tagihan</td><td class="text-end fw-bold text-nowrap">Rp {{ number_format((float) $payment->bill->total_amount, 0, ',', '.') }}</td></tr>
                                <tr><td class="text-end text-muted-2">Sudah Dibayar</td><td class="text-end text-nowrap">Rp {{ number_format((float) $payment->bill->paid_amount, 0, ',', '.') }}</td></tr>
                                <tr><td class="text-end fw-bold">Sisa</td><td class="text-end fw-bold text-nowrap text-danger">Rp {{ number_format($payment->bill->remaining_amount, 0, ',', '.') }}</td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-xl-5" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Tindakan</h3>
                        <p class="section-sub">Verifikasi, tolak, atau batalkan pembayaran.</p>
                    </div>
                </div>

                @if ($payment->status === \App\Models\SchoolPayment::STATUS_PENDING)
                    <form method="POST" action="{{ route('admin.spp.payment.verify', $payment) }}" class="mb-3">
                        @csrf
                        <input type="hidden" name="aksi" value="verifikasi">
                        <button class="btn btn-gradient w-100" onclick="return confirm('Verifikasi pembayaran ini dan alokasikan ke tagihan?')">
                            <i class="bi bi-check2-circle me-1"></i> Verifikasi Pembayaran
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.spp.payment.verify', $payment) }}">
                        @csrf
                        <input type="hidden" name="aksi" value="tolak">
                        <div class="mb-2">
                            <label class="form-label">Alasan Penolakan</label>
                            <textarea name="alasan" rows="2" class="form-control" maxlength="500" required placeholder="Contoh: nominal tidak sesuai atau bukti tidak jelas."></textarea>
                        </div>
                        <button class="btn btn-soft text-danger w-100"><i class="bi bi-x-circle me-1"></i> Tolak Pembayaran</button>
                    </form>
                @elseif (in_array($payment->status, [\App\Models\SchoolPayment::STATUS_REJECTED, \App\Models\SchoolPayment::STATUS_CANCELLED], true))
                    <div class="p-3 mb-3" style="border-radius:12px;background:#f8fafc;border:1px solid var(--line);font-size:.82rem">
                        Pembayaran ini tidak memiliki alokasi ke tagihan. Orang tua dapat mengunggah bukti baru dari portal.
                    </div>
                    @if ($payment->status === \App\Models\SchoolPayment::STATUS_REJECTED)
                        <form method="POST" action="{{ route('admin.spp.payment.cancel', $payment) }}" onsubmit="return confirm('Batalkan pembayaran ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-ghost w-100">Batalkan Pembayaran</button>
                        </form>
                    @endif
                @else
                    <div class="p-3" style="border-radius:12px;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.18);font-size:.82rem;color:#047857">
                        <i class="bi bi-check2-circle me-1"></i> Pembayaran sudah terverifikasi dan dialokasikan. Kwitansi dapat dicetak.
                    </div>
                @endif
            </div>

            @if ($payment->allocations->count())
                <div class="glass-card card-pad mt-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Alokasi</h3>
                            <p class="section-sub">Rincian ke tagihan.</p>
                        </div>
                    </div>
                    @foreach ($payment->allocations as $allocation)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color:var(--line)!important;font-size:.82rem">
                            <div>
                                <div class="fw-semibold">{{ $allocation->bill?->bill_number ?? '-' }}</div>
                                <div class="text-muted-2" style="font-size:.74rem">{{ $allocation->bill?->status_label ?? '-' }}</div>
                            </div>
                            <div class="fw-bold">Rp {{ number_format((float) $allocation->allocated_amount, 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
