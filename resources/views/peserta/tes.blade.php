@extends('layouts.peserta')

@section('title', $tes->tes_nama)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('peserta.dashboard') }}" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
        @if ($attempt && ! $attempt->is_berjalan)
            <a href="{{ route('peserta.ujian.hasil', $tes) }}" class="btn btn-soft"><i class="bi bi-bar-chart me-1"></i> Lihat Hasil</a>
        @endif
    </div>

    <div class="row g-4">
        <div class="col-lg-8" data-aos="fade-up">
            <div class="glass-card card-pad">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                    <div>
                        <span class="badge-soft {{ $tes->status === 'aktif' ? 'success' : 'warning' }} mb-2">
                            <i class="bi {{ $tes->status === 'aktif' ? 'bi-broadcast' : 'bi-hourglass-split' }}"></i>
                            {{ $tes->status === 'aktif' ? 'Sedang Dibuka' : 'Belum Dibuka' }}
                        </span>
                        <h2 class="fw-800 mb-1" style="font-size:1.45rem;letter-spacing:-.03em">{{ $tes->tes_nama }}</h2>
                        <p class="text-muted-2 mb-0" style="font-size:.86rem">{{ Str::limit(strip_tags($tes->tes_detail), 200) }}</p>
                    </div>
                </div>

                <div class="meta-row mb-4">
                    <span><i class="bi bi-clock"></i> {{ $tes->tes_duration_time }} menit</span>
                    <span><i class="bi bi-list-ol"></i> {{ $tes->jumlah_soal }} soal</span>
                    <span><i class="bi bi-calendar-event"></i> {{ $tes->tes_begin_time?->translatedFormat('d M Y, H:i') }}</span>
                    <span><i class="bi bi-calendar-check"></i> s.d. {{ $tes->tes_end_time?->translatedFormat('d M Y, H:i') }}</span>
                    @if ($tes->tes_token)
                        <span><i class="bi bi-key"></i> Wajib token</span>
                    @endif
                </div>

                <div class="p-3 mb-4" style="border-radius:16px;background:#f8fafc;border:1px solid var(--line)">
                    <div class="fw-bold mb-2" style="font-size:.84rem"><i class="bi bi-info-circle me-1 text-primary"></i> Petunjuk Pengerjaan</div>
                    <ul class="mb-0 ps-3 text-muted-2" style="font-size:.82rem;line-height:1.8">
                        <li>Pastikan koneksi internet stabil sebelum memulai ujian.</li>
                        <li>Jawaban tersimpan saat kamu menekan tombol simpan.</li>
                        <li>Waktu berjalan otomatis dan jawaban akan dikumpulkan saat waktu habis.</li>
                        <li>Tandai soal ragu-ragu untuk memeriksanya kembali sebelum selesai.</li>
                        <li>Ujian hanya dapat dikerjakan satu kali.</li>
                    </ul>
                </div>

                @if ($attempt && ! $attempt->is_berjalan)
                    <div class="p-4 text-center" style="border-radius:18px;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.18)">
                        <div class="stat-icon bg-grad-4 mx-auto"><i class="bi bi-check2-circle"></i></div>
                        <h4 class="fw-800 mb-1">Ujian sudah selesai</h4>
                        <p class="text-muted-2 mb-3" style="font-size:.85rem">Kamu telah menyelesaikan tes ini pada {{ $attempt->tesuser_creation_time?->translatedFormat('d M Y, H:i') }}.</p>
                        <a href="{{ route('peserta.ujian.hasil', $tes) }}" class="btn btn-gradient">Lihat Hasil Ujian</a>
                    </div>
                @elseif (! $tes->is_open)
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-hourglass-split me-1"></i> Tes belum memasuki waktu pelaksanaan atau sudah berakhir.
                    </div>
                @elseif ($perluPembayaran)
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-wallet2 me-1"></i> Ujian ini berbayar. Pembayaran harus diverifikasi admin sebelum kamu dapat memulai ujian.
                    </div>
                    <div class="p-3" style="border-radius:14px;background:#f8fafc;border:1px solid var(--line);font-size:.82rem">
                        <div class="d-flex justify-content-between mb-1"><span class="text-muted-2">Biaya ujian</span><span class="fw-bold">Rp {{ number_format((float) $tes->tes_biaya, 0, ',', '.') }}</span></div>
                        <div class="d-flex justify-content-between"><span class="text-muted-2">Status pembayaran</span><span class="fw-semibold">{{ $pembayaran?->status_label ?? 'Belum dibayar' }}</span></div>
                        @if ($pembayaran?->pembayaran_catatan_admin)
                            <div class="text-muted-2 mt-2">Catatan admin: {{ $pembayaran->pembayaran_catatan_admin }}</div>
                        @endif
                    </div>
                @elseif ($perluAbsensi)
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-geo-alt me-1"></i> Ujian ini mewajibkan absensi pra-ujian. Lakukan check-in lokasi sebelum memulai ujian.
                    </div>
                    <div class="p-3 mb-3" style="border-radius:14px;background:#f8fafc;border:1px solid var(--line);font-size:.82rem">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted-2">Titik presensi</span>
                            <span class="fw-semibold">{{ $attendanceLocation?->name ?? 'Belum diatur' }}</span>
                        </div>
                        @if ($attendanceLocation)
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted-2">Radius</span>
                                <span class="fw-semibold">{{ $attendanceLocation->radius_meters }} meter</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between">
                            <span class="text-muted-2">Jendela check-in</span>
                            <span class="fw-semibold">
                                {{ ($examSession?->starts_at ?? $tes->tes_checkin_mulai ?? $tes->tes_begin_time)?->translatedFormat('d M H:i') ?? '-' }}
                                &ndash;
                                {{ ($examSession?->ends_at ?? $tes->tes_checkin_selesai ?? $tes->tes_end_time)?->translatedFormat('d M H:i') ?? '-' }}
                            </span>
                        </div>
                    </div>
                    <div id="examCheckinAlert" class="alert d-none mb-3"></div>
                    <button type="button" class="btn btn-gradient w-100 py-2" id="examCheckinBtn" data-url="{{ route('peserta.tes.checkin', $tes) }}">
                        <i class="bi bi-geo-alt me-1"></i> Check-in Lokasi Sekarang
                    </button>
                    <div class="text-muted-2 mt-2 text-center" style="font-size:.74rem">
                        Aktifkan izin lokasi pada perangkat. Server memvalidasi jarak ke titik sekolah.
                    </div>
                @else
                    @if ($tes->tes_absensi_wajib && $attendanceRecord?->isPresent())
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check2-circle me-1"></i>
                            Check-in absensi tercatat {{ $attendanceRecord->check_in_at?->translatedFormat('H:i') }}
                            @if ($attendanceRecord->distance_meters !== null)
                                &middot; jarak {{ round($attendanceRecord->distance_meters) }} m
                            @endif
                            @if ($attendanceRecord->accuracy_meters !== null)
                                &middot; akurasi {{ round($attendanceRecord->accuracy_meters) }} m
                            @endif
                        </div>
                    @endif
                    <form method="POST" action="{{ route('peserta.tes.mulai', $tes) }}" onsubmit="return confirm('Mulai ujian sekarang? Waktu akan langsung berjalan.')">
                        @csrf
                        @if ($tes->tes_token)
                            <div class="mb-3">
                                <label class="form-label">Token Ujian</label>
                                <input type="text" name="token" class="form-control" placeholder="Masukkan token dari operator" style="letter-spacing:.18em;font-weight:700" required>
                            </div>
                        @endif
                        <button class="btn btn-gradient w-100 py-2"><i class="bi bi-play-circle me-1"></i> Mulai Ujian Sekarang</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Peserta</h3>
                        <p class="section-sub">Pastikan data kamu benar.</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar" style="width:52px;height:52px;font-size:1.2rem">{{ strtoupper(substr($peserta->nama, 0, 1)) }}</div>
                    <div>
                        <div class="fw-bold">{{ $peserta->nama }}</div>
                        <div class="text-muted-2" style="font-size:.78rem">{{ $peserta->user_name }}</div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.82rem">
                    <span class="text-muted-2">Group</span>
                    <span class="fw-semibold">{{ $peserta->grup?->grup_nama ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between" style="font-size:.82rem">
                    <span class="text-muted-2">Ruang / Sesi</span>
                    <span class="fw-semibold">{{ $peserta->user_detail ?: '-' }}</span>
                </div>
            </div>

            <div class="glass-card card-pad mt-4" style="background:linear-gradient(135deg,#1e1b4b,#4338ca);color:#fff;border:0">
                <div class="d-flex gap-3">
                    <i class="bi bi-lightbulb" style="font-size:1.5rem;color:#fde68a"></i>
                    <div>
                        <div class="fw-bold mb-1">Tetap tenang dan fokus</div>
                        <div style="font-size:.8rem;color:#dbeafe">Baca setiap soal dengan teliti. Jika ragu, tandai soal dan kembali lagi nanti.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const button = document.getElementById('examCheckinBtn');

        if (!button) {
            return;
        }

        const alertBox = document.getElementById('examCheckinAlert');

        const showAlert = (message, type) => {
            alertBox.className = 'alert alert-' + type + ' mb-3';
            alertBox.textContent = message;
        };

        button.addEventListener('click', function () {
            if (!navigator.geolocation) {
                showAlert('Perangkat ini tidak mendukung GPS. Hubungi petugas untuk verifikasi manual.', 'danger');
                return;
            }

            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengambil lokasi...';
            showAlert('Meminta izin dan membaca lokasi perangkat...', 'info');

            navigator.geolocation.getCurrentPosition(async function (position) {
                try {
                    const response = await fetch(button.dataset.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy_meters: position.coords.accuracy,
                        }),
                    });

                    const data = await response.json();

                    if (response.ok && data.ok) {
                        showAlert(data.message, 'success');
                        setTimeout(() => window.location.reload(), 900);
                        return;
                    }

                    showAlert(data.message || 'Check-in gagal. Silakan coba lagi.', 'danger');
                } catch (error) {
                    showAlert('Gagal menghubungi server. Periksa koneksi lalu coba lagi.', 'danger');
                } finally {
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-geo-alt me-1"></i> Check-in Lokasi Sekarang';
                }
            }, function (error) {
                const messages = {
                    1: 'Izin lokasi ditolak. Aktifkan izin lokasi atau hubungi petugas.',
                    2: 'Lokasi tidak tersedia. Coba di area terbuka atau hubungi petugas.',
                    3: 'Waktu pengambilan lokasi habis. Silakan coba lagi.',
                };

                showAlert(messages[error.code] || 'Gagal membaca lokasi perangkat.', 'danger');
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-geo-alt me-1"></i> Check-in Lokasi Sekarang';
            }, {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0,
            });
        });
    })();
</script>
@endpush
