@extends('layouts.admin')

@section('title', 'Detail Tagihan SPP')
@section('heading', 'Detail Tagihan SPP')
@section('eyebrow', 'Keuangan')

@section('content')
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3" data-aos="fade-up">
        <div>
            <div class="text-muted-2" style="font-size:.78rem">{{ $bill->bill_number }}</div>
            <div class="fw-bold" style="font-size:1.05rem">{{ $bill->student?->nama ?? '-' }}</div>
            <div class="text-muted-2" style="font-size:.8rem">
                {{ $bill->student?->grup?->grup_nama ?? '-' }} &middot; {{ $bill->school?->school_name ?? '-' }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.spp.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            @if (! $bill->allocations->count())
                <form method="POST" action="{{ route('admin.spp.destroy', $bill) }}" onsubmit="return confirm('Hapus tagihan ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-soft text-danger"><i class="bi bi-trash3 me-1"></i> Hapus</button>
                </form>
            @endif
        </div>
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
                        <p class="section-sub">{{ $bill->allocations->count() }} pembayaran dialokasikan ke tagihan ini.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Tanggal</th>
                                <th>Metode</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bill->allocations as $allocation)
                                <tr>
                                    <td class="fw-semibold" style="font-size:.8rem">{{ $allocation->payment?->payment_number ?? '-' }}</td>
                                    <td class="text-muted-2" style="font-size:.8rem">{{ $allocation->payment?->paid_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                    <td class="text-muted-2" style="font-size:.8rem">{{ $allocation->payment?->method?->method_name ?? '-' }}</td>
                                    <td class="text-end text-nowrap">Rp {{ number_format((float) $allocation->allocated_amount, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge-soft {{ $allocation->payment?->status_color ?? 'neutral' }}">{{ $allocation->payment?->status_label ?? '-' }}</span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @if ($allocation->payment)
                                            <a href="{{ route('admin.spp.payment.show', $allocation->payment) }}" class="btn btn-ghost btn-sm">Detail</a>
                                            @if ($allocation->payment->status === \App\Models\SchoolPayment::STATUS_VERIFIED)
                                                <a href="{{ route('admin.spp.payment.receipt', $allocation->payment) }}" class="btn btn-ghost btn-sm" title="Kwitansi"><i class="bi bi-printer"></i></a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-cash"></i>Belum ada pembayaran.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Catat Pembayaran</h3>
                        <p class="section-sub">Pembayaran akan langsung dialokasikan ke tagihan ini.</p>
                    </div>
                </div>

                @if ($bill->remaining_amount <= 0 || in_array($bill->status, [\App\Models\StudentBill::STATUS_PAID, \App\Models\StudentBill::STATUS_CANCELLED], true))
                    <div class="p-3" style="border-radius:12px;background:rgba(6,182,212,.07);border:1px solid rgba(6,182,212,.16);font-size:.82rem;color:#155e75">
                        <i class="bi bi-info-circle me-1"></i> Tagihan ini tidak memiliki sisa pembayaran.
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.spp.bayar', $bill) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nominal</label>
                            <input type="number" name="amount" value="{{ old('amount', $bill->remaining_amount) }}" min="0.01" max="{{ $bill->remaining_amount }}" step="0.01" class="form-control" required>
                            <div class="text-muted-2 mt-1" style="font-size:.72rem">Sisa tagihan: Rp {{ number_format($bill->remaining_amount, 0, ',', '.') }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Metode</label>
                            <select name="payment_method_id" class="form-select">
                                <option value="">Tanpa metode</option>
                                @foreach ($methods as $method)
                                    <option value="{{ $method->id }}" @selected((string) old('payment_method_id') === (string) $method->id)>{{ $method->method_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Bayar</label>
                            <input type="date" name="paid_at" value="{{ old('paid_at', now()->toDateString()) }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Opsional">{{ old('notes') }}</textarea>
                        </div>
                        <button class="btn btn-gradient w-100"><i class="bi bi-check2-circle me-1"></i> Simpan Pembayaran</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
