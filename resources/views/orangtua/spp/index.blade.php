@extends('layouts.orangtua')

@section('title', 'Tagihan SPP')

@section('content')
    @if (! $anak)
        <div class="glass-card card-pad" data-aos="fade-up">
            <div class="empty-state">
                <i class="bi bi-person-x"></i>
                Akun Anda belum terhubung dengan data peserta. Silakan hubungi admin sekolah.
            </div>
        </div>
    @else
        @php $anakQuery = ['anak' => $anak->user_id]; @endphp

        <div class="stat-grid mb-4">
            <div class="stat-card" data-aos="fade-up">
                <div class="stat-icon bg-grad-1"><i class="bi bi-receipt"></i></div>
                <div class="stat-value" style="font-size:1.2rem">Rp {{ number_format($statistik['total'], 0, ',', '.') }}</div>
                <div class="stat-label">Total Tagihan</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
                <div class="stat-icon bg-grad-4"><i class="bi bi-patch-check"></i></div>
                <div class="stat-value" style="font-size:1.2rem">Rp {{ number_format($statistik['terbayar'], 0, ',', '.') }}</div>
                <div class="stat-label">Sudah Dibayar</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
                <div class="stat-icon bg-grad-2"><i class="bi bi-exclamation-circle"></i></div>
                <div class="stat-value" style="font-size:1.2rem">Rp {{ number_format($statistik['sisa'], 0, ',', '.') }}</div>
                <div class="stat-label">Sisa Tagihan</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
                <div class="stat-icon bg-grad-3"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-value">{{ $statistik['menunggu'] }}</div>
                <div class="stat-label">Menunggu Verifikasi</div>
            </div>
        </div>

        @if ($statistik['ditolak'] > 0)
            <div class="alert alert-warning d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                <div style="font-size:.84rem">Ada {{ $statistik['ditolak'] }} bukti pembayaran SPP yang ditolak. Buka detail tagihan untuk melihat alasan dan mengunggah ulang.</div>
            </div>
        @endif

        @if ($rekening->isNotEmpty())
            <div class="glass-card card-pad mb-4" data-aos="fade-up">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Rekening Tujuan Transfer</h3>
                        <p class="section-sub">Transfer ke salah satu rekening sekolah berikut, lalu unggah bukti melalui detail tagihan.</p>
                    </div>
                    <span class="badge-soft"><i class="bi bi-bank2"></i> {{ $rekening->count() }} rekening</span>
                </div>
                @include('orangtua.partials.rekening', ['rekening' => $rekening])
            </div>
        @endif

        <div class="glass-card card-pad mb-4" data-aos="fade-up">
            <div class="section-head">
                <div>
                    <h3 class="section-title">Tagihan &middot; {{ $anak->nama }}</h3>
                    <p class="section-sub">Tagihan SPP/keuangan sekolah untuk anak Anda. Pembayaran dapat dicicil.</p>
                </div>
                <span class="badge-soft"><i class="bi bi-mortarboard"></i> {{ $anak->grup?->grup_nama ?? '-' }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle">
                    <thead>
                        <tr>
                            <th>Tagihan</th>
                            <th>Terbit / Jatuh Tempo</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Dibayar</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bills as $bill)
                            <tr>
                                <td>
                                    <div class="fw-semibold" style="font-size:.84rem">{{ $bill->bill_number }}</div>
                                    <div class="text-muted-2" style="font-size:.74rem">{{ $bill->school?->school_name ?? '-' }}</div>
                                </td>
                                <td class="text-muted-2" style="font-size:.78rem">
                                    {{ $bill->issue_date?->translatedFormat('d M Y') ?? '-' }}
                                    <div>Jatuh tempo {{ $bill->due_date?->translatedFormat('d M Y') ?? '-' }}</div>
                                </td>
                                <td class="text-end text-nowrap">Rp {{ number_format((float) $bill->total_amount, 0, ',', '.') }}</td>
                                <td class="text-end text-nowrap">Rp {{ number_format((float) $bill->paid_amount, 0, ',', '.') }}</td>
                                <td class="text-end text-nowrap fw-semibold {{ $bill->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">Rp {{ number_format($bill->remaining_amount, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge-soft {{ $bill->status_color }}">{{ $bill->status_label }}</span>
                                    @if ($bill->pendingPayments->where('status', \App\Models\SchoolPayment::STATUS_PENDING)->count())
                                        <div class="badge-soft warning mt-1"><i class="bi bi-hourglass-split"></i> Menunggu</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('orangtua.spp.show', ['bill' => $bill, 'anak' => $anak->user_id]) }}" class="btn btn-soft btn-sm">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="empty-state"><i class="bi bi-receipt-cutoff"></i>Belum ada tagihan SPP.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass-card card-pad" data-aos="fade-up">
            <div class="section-head">
                <div>
                    <h3 class="section-title">Riwayat Pembayaran</h3>
                    <p class="section-sub">{{ $payments->count() }} transaksi tercatat.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle">
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>Tagihan</th>
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
                                <td class="text-muted-2" style="font-size:.8rem">{{ $payment->bill?->bill_number ?? '-' }}</td>
                                <td class="text-muted-2" style="font-size:.8rem">{{ ($payment->paid_at ?? $payment->created_at)?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                <td class="text-end text-nowrap">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                <td class="text-center"><span class="badge-soft {{ $payment->status_color }}">{{ $payment->status_label }}</span></td>
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
                            <tr><td colspan="6"><div class="empty-state"><i class="bi bi-cash-stack"></i>Belum ada pembayaran SPP.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
