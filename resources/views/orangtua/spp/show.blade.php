@extends('layouts.orangtua')

@section('title', 'Detail Tagihan SPP')

@section('content')
    @php
        $anakQuery = ['anak' => $anak->user_id];
        $pending = $bill->pendingPayments->firstWhere('status', \App\Models\SchoolPayment::STATUS_PENDING);
        $rejected = $bill->pendingPayments->firstWhere('status', \App\Models\SchoolPayment::STATUS_REJECTED);
    @endphp

    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3" data-aos="fade-up">
        <div>
            <div class="text-muted-2" style="font-size:.78rem">{{ $bill->bill_number }}</div>
            <div class="fw-bold" style="font-size:1.05rem">{{ $bill->school?->school_name ?? '-' }}</div>
            <div class="text-muted-2" style="font-size:.8rem">{{ $anak->nama }} &middot; {{ $anak->grup?->grup_nama ?? '-' }}</div>
        </div>
        <a href="{{ route('orangtua.spp.index', $anakQuery) }}" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>

    <div class="row g-4">
        <div class="col-xl-8" data-aos="fade-up">
            <div class="glass-card card-pad mb-4">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Rincian Tagihan</h3>
                        <p class="section-sub">Terbit {{ $bill->issue_date?->translatedFormat('d F Y') ?? '-' }} &middot; Jatuh tempo {{ $bill->due_date?->translatedFormat('d F Y') ?? '-' }}</p>
                    </div>
                    <span class="badge-soft {{ $bill->status_color }}">{{ $bill->status_label }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Deskripsi</th>
                                <th>Kategori</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bill->items as $item)
                                <tr>
                                    <td class="fw-semibold" style="font-size:.84rem">{{ $item->description }}</td>
                                    <td class="text-muted-2" style="font-size:.8rem">{{ $item->category?->category_name ?? '-' }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                    <td class="text-end text-nowrap">Rp {{ number_format((float) $item->unit_amount, 0, ',', '.') }}</td>
                                    <td class="text-end text-nowrap fw-semibold">Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total Tagihan</td>
                                <td class="text-end text-nowrap fw-bold">Rp {{ number_format((float) $bill->total_amount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end text-muted-2">Sudah Dibayar</td>
                                <td class="text-end text-nowrap text-muted-2">Rp {{ number_format((float) $bill->paid_amount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Sisa Tagihan</td>
                                <td class="text-end text-nowrap fw-bold text-danger">Rp {{ number_format($bill->remaining_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($bill->notes)
                    <div class="mt-3 p-3" style="border-radius:12px;background:#f8fafc;border:1px solid var(--line);font-size:.82rem">
                        <i class="bi bi-sticky me-1 text-primary"></i> {{ $bill->notes }}
                    </div>
                @endif
            </div>

            <div class="glass-card card-pad" data-aos="fade-up">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Riwayat Pembayaran</h3>
                        <p class="section-sub">{{ $payments->count() }} transaksi untuk tagihan ini.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Tanggal</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr>
                                    <td class="fw-semibold" style="font-size:.8rem">{{ $payment->payment_number }}</td>
                                    <td class="text-muted-2" style="font-size:.8rem">{{ ($payment->paid_at ?? $payment->created_at)?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                    <td class="text-end text-nowrap">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge-soft {{ $payment->status_color }}">{{ $payment->status_label }}</span>
                                        @if ($payment->rejection_reason)
                                            <div class="text-muted-2 mt-1" style="font-size:.72rem">{{ $payment->rejection_reason }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @if ($payment->proof_path)
                                            <a href="{{ route('orangtua.spp.bukti', $payment) }}" target="_blank" class="btn btn-ghost btn-sm"><i class="bi bi-paperclip"></i></a>
                                        @endif
                                        @if ($payment->status === \App\Models\SchoolPayment::STATUS_VERIFIED)
                                            <a href="{{ route('orangtua.spp.kwitansi', $payment) }}" class="btn btn-soft btn-sm">Kwitansi</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="empty-state"><i class="bi bi-cash"></i>Belum ada pembayaran.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4" data-aos="fade-up" data-aos-delay="60">
            @if ($rekening->isNotEmpty())
                <div class="glass-card card-pad mb-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Rekening Tujuan Transfer</h3>
                            <p class="section-sub">Transfer ke salah satu rekening sekolah berikut.</p>
                        </div>
                    </div>
                    @include('orangtua.partials.rekening', ['rekening' => $rekening])
                </div>
            @endif

            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Bayar / Cicil Tagihan</h3>
                        <p class="section-sub">Unggah bukti transfer sesuai nominal yang dibayarkan.</p>
                    </div>
                </div>

                @if ($bill->remaining_amount <= 0 || in_array($bill->status, [\App\Models\StudentBill::STATUS_PAID, \App\Models\StudentBill::STATUS_CANCELLED], true))
                    <div class="p-3" style="border-radius:12px;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.18);font-size:.82rem;color:#047857">
                        <i class="bi bi-check2-circle me-1"></i> Tagihan ini sudah lunas atau tidak dapat dibayar lagi.
                    </div>
                @elseif ($pending)
                    <div class="p-3 mb-3" style="border-radius:12px;background:#fffbeb;border:1px solid #fde68a;font-size:.82rem;color:#92400e">
                        <i class="bi bi-hourglass-split me-1"></i> Bukti pembayaran sebesar Rp {{ number_format((float) $pending->amount, 0, ',', '.') }} sedang menunggu verifikasi admin.
                    </div>
                    <form method="POST" action="{{ route('orangtua.spp.bayar', ['bill' => $bill, 'anak' => $anak->user_id]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Ganti Nominal</label>
                            <input type="number" name="amount" value="{{ old('amount', $pending->amount) }}" min="0.01" max="{{ $bill->remaining_amount }}" step="0.01" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ganti Bukti Pembayaran</label>
                            <input type="file" name="proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" rows="2" maxlength="500" class="form-control">{{ old('notes', $pending->notes) }}</textarea>
                        </div>
                        <button class="btn btn-gradient w-100"><i class="bi bi-upload me-1"></i> Perbarui Bukti</button>
                    </form>
                @else
                    @if ($rejected)
                        <div class="p-3 mb-3" style="border-radius:12px;background:#fef2f2;border:1px solid #fecaca;font-size:.82rem;color:#b91c1c">
                            <i class="bi bi-x-circle me-1"></i> Bukti sebelumnya ditolak. {{ $rejected->rejection_reason }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('orangtua.spp.bayar', ['bill' => $bill, 'anak' => $anak->user_id]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nominal Dibayarkan</label>
                            <input type="number" name="amount" value="{{ old('amount', $bill->remaining_amount) }}" min="0.01" max="{{ $bill->remaining_amount }}" step="0.01" class="form-control" required>
                            <div class="text-muted-2 mt-1" style="font-size:.72rem">Sisa tagihan: Rp {{ number_format($bill->remaining_amount, 0, ',', '.') }}. Boleh dibayar sebagian.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bukti Pembayaran <span class="text-danger">*</span></label>
                            <input type="file" name="proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                            <div class="text-muted-2 mt-1" style="font-size:.72rem">JPG, PNG, atau PDF. Maksimal 4 MB.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan untuk Admin</label>
                            <textarea name="notes" rows="2" maxlength="500" class="form-control" placeholder="Contoh: transfer atas nama...">{{ old('notes') }}</textarea>
                        </div>
                        <button class="btn btn-gradient w-100"><i class="bi bi-upload me-1"></i> Kirim Bukti Pembayaran</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
