@extends('layouts.orangtua')

@section('title', 'Absensi Anak')

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
                <div class="stat-icon bg-grad-4"><i class="bi bi-check2-circle"></i></div>
                <div class="stat-value">{{ $rekap['hadir'] ?? 0 }}</div>
                <div class="stat-label">Hadir</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
                <div class="stat-icon bg-grad-1"><i class="bi bi-envelope-check"></i></div>
                <div class="stat-value">{{ $rekap['izin'] ?? 0 }}</div>
                <div class="stat-label">Izin</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
                <div class="stat-icon bg-grad-3"><i class="bi bi-bandaid"></i></div>
                <div class="stat-value">{{ $rekap['sakit'] ?? 0 }}</div>
                <div class="stat-label">Sakit</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
                <div class="stat-icon bg-grad-2"><i class="bi bi-person-dash"></i></div>
                <div class="stat-value">{{ $rekap['alpha'] ?? 0 }}</div>
                <div class="stat-label">Alpha</div>
            </div>
        </div>

        <div class="glass-card card-pad" data-aos="fade-up">
            <div class="section-head">
                <div>
                    <h3 class="section-title">Riwayat Absensi &middot; {{ $anak->nama }}</h3>
                    <p class="section-sub">Kehadiran otomatis tercatat saat anak memulai ujian. Admin dapat menambahkan atau mengoreksi data.</p>
                </div>
                <span class="badge-soft"><i class="bi bi-mortarboard"></i> {{ $anak->grup?->grup_nama ?? '-' }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Ujian / Kegiatan</th>
                            <th class="text-center">Status</th>
                            <th>Sumber</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($absensis as $absen)
                            <tr>
                                <td class="fw-semibold text-nowrap">{{ $absen->absensi_tanggal?->translatedFormat('d M Y') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $absen->tes?->tes_nama ?? 'Absensi manual' }}</div>
                                    @if ($absen->tes)
                                        <div class="text-muted-2" style="font-size:.74rem">{{ $absen->tes->tes_begin_time?->translatedFormat('d M Y H:i') }}</div>
                                    @endif
                                </td>
                                <td class="text-center"><span class="badge-soft {{ $absen->status_color }}">{{ $absen->status_label }}</span></td>
                                <td>
                                    <span class="badge-soft {{ $absen->absensi_sumber === \App\Models\Absensi::SUMBER_UJIAN ? 'info' : 'neutral' }}">
                                        {{ $absen->absensi_sumber === \App\Models\Absensi::SUMBER_UJIAN ? 'Otomatis ujian' : 'Manual admin' }}
                                    </span>
                                </td>
                                <td class="text-muted-2" style="font-size:.82rem">{{ $absen->absensi_keterangan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="bi bi-calendar-x"></i>
                                        Belum ada catatan absensi untuk anak Anda.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $absensis->links() }}</div>
        </div>
    @endif
@endsection
