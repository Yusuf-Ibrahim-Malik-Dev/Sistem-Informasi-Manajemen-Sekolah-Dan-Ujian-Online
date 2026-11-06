@extends('layouts.admin')

@section('title', 'Tagihan SPP')
@section('heading', 'Tagihan SPP')
@section('eyebrow', 'Keuangan')

@section('content')
    <div class="stat-grid mb-4">
        <div class="stat-card" data-aos="fade-up">
            <div class="stat-icon bg-grad-1"><i class="bi bi-receipt"></i></div>
            <div class="stat-value">{{ number_format($statistik['jumlah'], 0, ',', '.') }}</div>
            <div class="stat-label">Jumlah Tagihan</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
            <div class="stat-icon bg-grad-2"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value" style="font-size:1.2rem">Rp {{ number_format($statistik['total_tagihan'], 0, ',', '.') }}</div>
            <div class="stat-label">Total Tagihan</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-icon bg-grad-4"><i class="bi bi-check2-circle"></i></div>
            <div class="stat-value" style="font-size:1.2rem">Rp {{ number_format($statistik['total_terbayar'], 0, ',', '.') }}</div>
            <div class="stat-label">Terbayar</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
            <div class="stat-icon bg-grad-3"><i class="bi bi-exclamation-diamond"></i></div>
            <div class="stat-value" style="font-size:1.2rem">Rp {{ number_format($statistik['outstanding'], 0, ',', '.') }}</div>
            <div class="stat-label">Outstanding</div>
        </div>
    </div>

    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Cari Peserta / Nomor Tagihan</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nama, username, NIS...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2 justify-content-md-end">
                <button class="btn btn-soft"><i class="bi bi-search me-1"></i> Cari</button>
                <a href="{{ route('admin.spp.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
                <a href="{{ route('admin.spp.create') }}" class="btn btn-gradient"><i class="bi bi-plus-lg me-1"></i> Buat Tagihan</a>
            </div>
        </form>
    </div>

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Daftar Tagihan</h3>
                <p class="section-sub">{{ $daftar->total() }} tagihan sesuai filter.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Peserta</th>
                        <th>Terbit / Jatuh Tempo</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Terbayar</th>
                        <th class="text-end">Sisa</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar as $bill)
                        <tr>
                            <td>
                                <div class="fw-semibold" style="font-size:.82rem">{{ $bill->bill_number }}</div>
                                <div class="text-muted-2" style="font-size:.72rem">{{ $bill->school?->school_name ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $bill->student?->nama ?? '-' }}</div>
                                <div class="text-muted-2" style="font-size:.74rem">{{ $bill->student?->grup?->grup_nama ?? '-' }}</div>
                            </td>
                            <td class="text-nowrap" style="font-size:.8rem">
                                <div>{{ $bill->issue_date?->translatedFormat('d M Y') ?? '-' }}</div>
                                <div class="text-muted-2">Jatuh tempo: {{ $bill->due_date?->translatedFormat('d M Y') ?? '-' }}</div>
                            </td>
                            <td class="text-end text-nowrap">Rp {{ number_format((float) $bill->total_amount, 0, ',', '.') }}</td>
                            <td class="text-end text-nowrap">Rp {{ number_format((float) $bill->paid_amount, 0, ',', '.') }}</td>
                            <td class="text-end text-nowrap fw-semibold">Rp {{ number_format($bill->remaining_amount, 0, ',', '.') }}</td>
                            <td class="text-center"><span class="badge-soft {{ $bill->status_color }}">{{ $bill->status_label }}</span></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.spp.show', $bill) }}" class="btn btn-soft btn-sm"><i class="bi bi-eye me-1"></i> Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="bi bi-receipt-cutoff"></i>
                                    Belum ada tagihan SPP. Klik "Buat Tagihan" untuk memulai.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $daftar->links() }}</div>
    </div>
@endsection
