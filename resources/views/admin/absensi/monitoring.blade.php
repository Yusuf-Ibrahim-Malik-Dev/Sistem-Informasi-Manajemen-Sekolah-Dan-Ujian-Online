@extends('layouts.admin')

@section('title', 'Monitoring Kehadiran')
@section('heading', 'Monitoring Kehadiran')
@section('eyebrow', 'Data Peserta')

@php
    $isGuru = request()->routeIs('admin.guru.absensi*');
    $showRoute = $isGuru ? 'admin.guru.absensi.show' : 'admin.absensi.monitoring.show';
    $tesRoute = $isGuru ? 'admin.guru.absensi.tes' : 'admin.absensi.monitoring.tes';
    $indexRoute = $isGuru ? 'admin.guru.absensi' : 'admin.absensi.monitoring';
    $totalHadir = $sessions->sum('hadir_count');
    $totalDitolak = $sessions->sum('rejected_count');
    $totalOverride = $sessions->sum('override_count');
@endphp

@section('content')
    <div class="stat-grid mb-4">
        <div class="stat-card" data-aos="fade-up">
            <div class="stat-icon bg-grad-4"><i class="bi bi-check2-circle"></i></div>
            <div class="stat-value">{{ $totalHadir }}</div>
            <div class="stat-label">Hadir valid</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
            <div class="stat-icon bg-grad-2"><i class="bi bi-geo-alt"></i></div>
            <div class="stat-value">{{ $totalDitolak }}</div>
            <div class="stat-label">Percobaan ditolak</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-icon bg-grad-3"><i class="bi bi-person-check"></i></div>
            <div class="stat-value">{{ $totalOverride }}</div>
            <div class="stat-label">Override manual</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
            <div class="stat-icon bg-grad-1"><i class="bi bi-calendar3"></i></div>
            <div class="stat-value">{{ $sessions->count() }}</div>
            <div class="stat-label">Sesi pada tanggal ini</div>
        </div>
    </div>

    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Jenis Sesi</label>
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="daily" @selected($type === 'daily')>Absensi harian</option>
                    <option value="exam" @selected($type === 'exam')>Pra-ujian</option>
                </select>
            </div>
            <div class="col-md-6 d-flex gap-2 justify-content-md-end">
                <button class="btn btn-soft"><i class="bi bi-search me-1"></i> Tampilkan</button>
                <a href="{{ route($indexRoute) }}" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise me-1"></i> Hari Ini</a>
            </div>
        </form>
    </div>

    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Sesi Presensi</h3>
                <p class="section-sub">Klik sesi untuk melihat daftar peserta, jarak, akurasi GPS, metode, dan status ujian.</p>
            </div>
            <span class="badge-soft info"><i class="bi bi-list-check"></i> {{ $sessions->count() }} sesi</span>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Tanggal / Waktu</th>
                        <th>Jenis</th>
                        <th>Ujian</th>
                        <th>Lokasi</th>
                        <th class="text-center">Peserta</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Ditolak</th>
                        <th class="text-center">Override</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td class="text-nowrap">
                                <div class="fw-semibold">{{ $session->session_date?->translatedFormat('d M Y') }}</div>
                                <div class="text-muted-2" style="font-size:.74rem">{{ $session->starts_at?->format('H:i') ?? '00:00' }} - {{ $session->ends_at?->format('H:i') ?? '23:59' }}</div>
                            </td>
                            <td>
                                <span class="badge-soft {{ $session->session_type === 'exam' ? 'info' : 'success' }}">
                                    {{ $session->session_type === 'exam' ? 'Pra-ujian' : 'Harian' }}
                                </span>
                            </td>
                            <td>{{ $session->exam?->tes_nama ?? '-' }}</td>
                            <td class="text-muted-2" style="font-size:.78rem">{{ $session->attendance_location?->name ?? 'Belum diatur' }}</td>
                            <td class="text-center">{{ $session->total_students }}</td>
                            <td class="text-center fw-semibold text-success">{{ $session->hadir_count }}</td>
                            <td class="text-center fw-semibold text-danger">{{ $session->rejected_count }}</td>
                            <td class="text-center fw-semibold">{{ $session->override_count }}</td>
                            <td class="text-end">
                                <a href="{{ route($showRoute, $session) }}" class="btn btn-sm btn-soft">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-calendar-x"></i>
                                    Belum ada sesi presensi pada tanggal ini.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Ujian Terjadwal</h3>
                <p class="section-sub">Daftar ujian pada tanggal ini. Peserta yang belum check-in belum memiliki sesi presensi.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ujian</th>
                        <th>Mulai</th>
                        <th class="text-center">Absensi Wajib</th>
                        <th class="text-center">Lokasi</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exams as $exam)
                        <tr>
                            <td class="fw-semibold">{{ $exam->tes_nama }}</td>
                            <td class="text-nowrap">{{ $exam->tes_begin_time?->translatedFormat('d M Y H:i') }}</td>
                            <td class="text-center">
                                <span class="badge-soft {{ $exam->tes_absensi_wajib ? 'warning' : 'secondary' }}">{{ $exam->tes_absensi_wajib ? 'Wajib' : 'Tidak' }}</span>
                            </td>
                            <td class="text-center text-muted-2" style="font-size:.78rem">{{ $exam->attendanceLocation?->name ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route($tesRoute, $exam) }}" class="btn btn-sm btn-soft">
                                    <i class="bi bi-broadcast me-1"></i> Monitor
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-journal-x"></i>
                                    Tidak ada ujian terjadwal pada tanggal ini.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
