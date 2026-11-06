@extends('layouts.orangtua')

@section('title', 'Dashboard')

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

        <div class="hero-card mb-4" data-aos="fade-up">
            <div class="position-relative" style="z-index:1">
                <div class="text-uppercase fw-semibold mb-2" style="letter-spacing:.14em;font-size:.72rem;color:#c7d2fe">Selamat datang, {{ $orangTua->ortu_nama }}</div>
                <h2 class="fw-800 mb-2" style="font-size:1.75rem;letter-spacing:-.03em">Pantauan {{ $anak->nama }}</h2>
                <p class="mb-0" style="color:#dbeafe;max-width:680px;font-size:.92rem">
                    {{ $anak->grup?->grup_nama ? 'Terdaftar pada kelas ' . $anak->grup->grup_nama . '. ' : '' }}
                    Berikut ringkasan kehadiran ujian dan status pembayaran anak Anda.
                </p>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <span class="badge-soft" style="background:rgba(255,255,255,.14);color:#e0e7ff"><i class="bi bi-person-badge"></i> {{ $anak->user_name }}</span>
                    <span class="badge-soft" style="background:rgba(255,255,255,.14);color:#e0e7ff"><i class="bi bi-mortarboard"></i> {{ $anak->grup?->grup_nama ?? 'Tanpa kelas' }}</span>
                </div>
            </div>
        </div>

        <div class="stat-grid mb-4">
            <div class="stat-card" data-aos="fade-up">
                <div class="stat-icon bg-grad-4"><i class="bi bi-calendar-check"></i></div>
                <div class="stat-value">{{ $rekapAbsensi['hadir'] ?? 0 }}</div>
                <div class="stat-label">Kehadiran Ujian</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
                <div class="stat-icon bg-grad-3"><i class="bi bi-clipboard2-pulse"></i></div>
                <div class="stat-value">{{ ($rekapAbsensi['izin'] ?? 0) + ($rekapAbsensi['sakit'] ?? 0) }}</div>
                <div class="stat-label">Izin / Sakit</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
                <div class="stat-icon bg-grad-2"><i class="bi bi-person-dash"></i></div>
                <div class="stat-value">{{ $rekapAbsensi['alpha'] ?? 0 }}</div>
                <div class="stat-label">Tanpa Keterangan</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
                <div class="stat-icon bg-grad-1"><i class="bi bi-patch-check"></i></div>
                <div class="stat-value">{{ $pembayarans->where('pembayaran_status', \App\Models\Pembayaran::DIVERIFIKASI)->count() }}</div>
                <div class="stat-label">Pembayaran Terverifikasi</div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-7" data-aos="fade-up">
                <div class="glass-card card-pad h-100">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Ringkasan Pembayaran</h3>
                            <p class="section-sub">Biaya ujian yang ditetapkan admin sekolah.</p>
                        </div>
                        <a href="{{ route('orangtua.pembayaran.index', $anakQuery) }}" class="btn btn-soft btn-sm">Kelola <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>

                    <div class="row g-2">
                        <div class="col-sm-6">
                            <div class="p-3" style="border-radius:14px;background:#f8fafc;border:1px solid var(--line)">
                                <div class="text-muted-2" style="font-size:.74rem">Total Tagihan</div>
                                <div class="fw-800" style="font-size:1.15rem">Rp {{ number_format($ringkasanPembayaran['total_tagihan'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3" style="border-radius:14px;background:#ecfdf5;border:1px solid #a7f3d0">
                                <div class="text-muted-2" style="font-size:.74rem">Sudah Diverifikasi</div>
                                <div class="fw-800" style="font-size:1.15rem;color:#047857">Rp {{ number_format($ringkasanPembayaran['terbayar'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3" style="border-radius:14px;background:#fffbeb;border:1px solid #fde68a">
                                <div class="text-muted-2" style="font-size:.74rem">Menunggu Verifikasi</div>
                                <div class="fw-800" style="font-size:1.15rem;color:#b45309">{{ $ringkasanPembayaran['menunggu'] }} pembayaran</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3" style="border-radius:14px;background:#fef2f2;border:1px solid #fecaca">
                                <div class="text-muted-2" style="font-size:.74rem">Sisa Tagihan</div>
                                <div class="fw-800" style="font-size:1.15rem;color:#b91c1c">Rp {{ number_format($ringkasanPembayaran['sisa'], 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>

                    @if ($ringkasanPembayaran['ditolak'] > 0)
                        <div class="alert alert-warning d-flex align-items-start gap-2 mt-3 mb-0">
                            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                            <div style="font-size:.82rem">Ada {{ $ringkasanPembayaran['ditolak'] }} bukti pembayaran yang ditolak. Silakan periksa catatan admin dan unggah ulang bukti yang benar.</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-5" data-aos="fade-up" data-aos-delay="60">
                <div class="glass-card card-pad h-100">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Absensi Terbaru</h3>
                            <p class="section-sub">Kehadiran anak pada ujian.</p>
                        </div>
                        <a href="{{ route('orangtua.absensi.index', $anakQuery) }}" class="btn btn-soft btn-sm">Semua</a>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        @forelse ($absensis->take(5) as $absen)
                            <div class="d-flex align-items-center justify-content-between gap-2 p-2" style="border-radius:12px;background:#f8fafc;border:1px solid var(--line)">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate" style="font-size:.84rem">{{ $absen->tes?->tes_nama ?? 'Absensi manual' }}</div>
                                    <div class="text-muted-2" style="font-size:.74rem">{{ $absen->absensi_tanggal?->translatedFormat('d M Y') }}</div>
                                </div>
                                <span class="badge-soft {{ $absen->status_color }}">{{ $absen->status_label }}</span>
                            </div>
                        @empty
                            <div class="empty-state" style="padding:24px 12px">
                                <i class="bi bi-calendar-x"></i>
                                Belum ada catatan absensi.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="section-head">
            <div>
                <h3 class="section-title">Ujian &amp; Biaya</h3>
                <p class="section-sub">Status pembayaran setiap ujian yang tersedia untuk kelas {{ $anak->grup?->grup_nama ?? '-' }}.</p>
            </div>
            <a href="{{ route('orangtua.pembayaran.index', $anakQuery) }}" class="btn btn-gradient btn-sm"><i class="bi bi-wallet2 me-1"></i> Pembayaran</a>
        </div>

        <div class="row g-3">
            @forelse ($tes as $item)
                @php
                    $bayar = $pembayarans[$item->tes_id] ?? null;
                    $statusBayar = $bayar?->pembayaran_status;
                @endphp
                <div class="col-md-6 col-xl-4" data-aos="fade-up">
                    <div class="tes-card h-100">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <span class="badge-soft {{ $item->status === 'aktif' ? 'success' : 'neutral' }}">
                                <i class="bi {{ $item->status === 'aktif' ? 'bi-broadcast' : 'bi-clock' }}"></i>
                                {{ $item->status === 'aktif' ? 'Dibuka' : 'Selesai / Belum Dibuka' }}
                            </span>
                            @if ((float) $item->tes_biaya <= 0)
                                <span class="badge-soft success">Gratis</span>
                            @elseif ($statusBayar === \App\Models\Pembayaran::DIVERIFIKASI)
                                <span class="badge-soft success"><i class="bi bi-check2-circle"></i> Lunas</span>
                            @elseif ($statusBayar === \App\Models\Pembayaran::MENUNGGU)
                                <span class="badge-soft warning"><i class="bi bi-hourglass-split"></i> Menunggu</span>
                            @elseif ($statusBayar === \App\Models\Pembayaran::DITOLAK)
                                <span class="badge-soft danger"><i class="bi bi-x-circle"></i> Ditolak</span>
                            @else
                                <span class="badge-soft neutral">Belum bayar</span>
                            @endif
                        </div>

                        <h5 class="mb-1">{{ $item->tes_nama }}</h5>
                        <p class="text-muted-2 mb-3" style="font-size:.8rem">{{ Str::limit(strip_tags($item->tes_detail), 100) }}</p>

                        <div class="meta-row mb-3">
                            <span><i class="bi bi-calendar-event"></i> {{ $item->tes_begin_time?->translatedFormat('d M Y') }}</span>
                            <span><i class="bi bi-cash-coin"></i> {{ (float) $item->tes_biaya > 0 ? 'Rp ' . number_format($item->tes_biaya, 0, ',', '.') : 'Gratis' }}</span>
                        </div>

                        <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center gap-2" style="border-color:var(--line)!important">
                            <span class="text-muted-2" style="font-size:.74rem">{{ $item->tes_begin_time?->translatedFormat('d M') }} - {{ $item->tes_end_time?->translatedFormat('d M') }}</span>
                            @if ((float) $item->tes_biaya > 0 && $statusBayar !== \App\Models\Pembayaran::DIVERIFIKASI)
                                <a href="{{ route('orangtua.pembayaran.index', $anakQuery) }}#tes{{ $item->tes_id }}" class="btn btn-gradient btn-sm">
                                    {{ $statusBayar === \App\Models\Pembayaran::DITOLAK ? 'Unggah Ulang' : 'Bayar' }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="glass-card card-pad">
                        <div class="empty-state">
                            <i class="bi bi-journal-x"></i>
                            Belum ada ujian untuk kelas anak Anda.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    @endif
@endsection
