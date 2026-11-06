@extends('layouts.admin')

@section('title', 'Detail Monitoring Kehadiran')
@section('heading', 'Detail Monitoring Kehadiran')
@section('eyebrow', 'Data Peserta')

@php
    $isGuru = request()->routeIs('admin.guru.absensi*');
    $overrideRoute = $isGuru ? 'admin.guru.absensi.override' : 'admin.absensi.monitoring.override';
    $backRoute = $isGuru ? 'admin.guru.absensi' : 'admin.absensi.monitoring';

    $attendanceLabels = [
        'hadir' => ['Hadir', 'success'],
        'terlambat' => ['Terlambat', 'warning'],
        'izin' => ['Izin', 'info'],
        'sakit' => ['Sakit', 'warning'],
        'dispensasi' => ['Dispensasi', 'info'],
        'alpha' => ['Alpha', 'danger'],
        'override' => ['Override manual', 'info'],
        'ditolak' => ['Ditolak', 'danger'],
        'belum' => ['Belum check-in', 'secondary'],
    ];

    $examLabels = [
        'belum_mulai' => ['Belum mulai', 'secondary'],
        'mengerjakan' => ['Mengerjakan', 'success'],
        'terputus' => ['Terputus / kedaluwarsa', 'warning'],
        'selesai' => ['Selesai', 'info'],
        'lainnya' => ['Status lain', 'secondary'],
    ];
@endphp

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <div class="section-title mb-1">{{ $tes?->tes_nama ?? 'Absensi Harian' }}</div>
            <div class="text-muted-2" style="font-size:.82rem">
                {{ $school?->label ?? '-' }}
                @if ($session)
                    &middot; {{ $session->session_date?->translatedFormat('d F Y') }}
                    &middot; {{ $session->starts_at?->format('H:i') ?? '00:00' }} - {{ $session->ends_at?->format('H:i') ?? '23:59' }}
                @endif
            </div>
        </div>
        <a href="{{ route($backRoute) }}" class="btn btn-ghost">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if (! $session)
        <div class="alert alert-warning mb-4">
            <i class="bi bi-info-circle me-1"></i>
            Belum ada peserta yang melakukan check-in pada ujian ini. Sesi presensi akan dibuat otomatis saat check-in pertama atau override manual.
        </div>
    @endif

    @if ($session?->location || $tes?->attendanceLocation)
        <div class="glass-card card-pad mb-4" data-aos="fade-up">
            <div class="row g-3" style="font-size:.82rem">
                <div class="col-md-4">
                    <div class="text-muted-2 mb-1">Titik presensi</div>
                    <div class="fw-semibold">{{ ($session?->location ?? $tes?->attendanceLocation)?->name }}</div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted-2 mb-1">Radius</div>
                    <div class="fw-semibold">{{ ($session?->location ?? $tes?->attendanceLocation)?->radius_meters }} m</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted-2 mb-1">Koordinat</div>
                    <div class="fw-semibold">
                        {{ ($session?->location ?? $tes?->attendanceLocation)?->latitude }},
                        {{ ($session?->location ?? $tes?->attendanceLocation)?->longitude }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted-2 mb-1">Akurasi GPS maks</div>
                    <div class="fw-semibold">{{ ($session?->location ?? $tes?->attendanceLocation)?->max_accuracy_meters }} m</div>
                </div>
            </div>
        </div>
    @endif

    <div class="stat-grid mb-4">
        <div class="stat-card" data-aos="fade-up">
            <div class="stat-icon bg-grad-1"><i class="bi bi-people"></i></div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total peserta</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
            <div class="stat-icon bg-grad-4"><i class="bi bi-check2-circle"></i></div>
            <div class="stat-value">{{ $stats['hadir'] }}</div>
            <div class="stat-label">Hadir valid</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
            <div class="stat-icon bg-grad-2"><i class="bi bi-geo-alt"></i></div>
            <div class="stat-value">{{ $stats['ditolak'] }}</div>
            <div class="stat-label">Ditolak / luar radius</div>
        </div>
        <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
            <div class="stat-icon bg-grad-3"><i class="bi bi-person-check"></i></div>
            <div class="stat-value">{{ $stats['override'] }}</div>
            <div class="stat-label">Override manual</div>
        </div>
    </div>

    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Daftar Peserta</h3>
                <p class="section-sub">Lokasi rinci hanya ditampilkan kepada petugas yang berwenang.</p>
            </div>
            <span class="badge-soft info"><i class="bi bi-broadcast"></i> {{ $rows->count() }} peserta</span>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th>Peserta</th>
                        <th class="text-center">Absensi</th>
                        <th>Check-in</th>
                        <th class="text-center">Jarak</th>
                        <th class="text-center">Akurasi</th>
                        <th>Metode</th>
                        <th class="text-center">Status Ujian</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php
                            $state = $attendanceLabels[$row['attendance_state']] ?? [ucfirst($row['attendance_state']), 'secondary'];
                            $examState = $examLabels[$row['exam_state']] ?? [ucfirst($row['exam_state']), 'secondary'];
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $row['student']->nama }}</div>
                                <div class="text-muted-2" style="font-size:.74rem">{{ $row['student']->user_name }} &middot; {{ $row['student']->grup?->grup_nama ?? '-' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge-soft {{ $state[1] }}">{{ $state[0] }}</span>
                                @if ($row['attendance_state'] === 'ditolak' && $row['check']?->reason)
                                    <div class="text-muted-2 mt-1" style="font-size:.68rem">{{ \Illuminate\Support\Str::limit($row['check']->reason, 60) }}</div>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $row['record']?->check_in_at?->format('H:i:s') ?? '-' }}</td>
                            <td class="text-center">{{ $row['record']?->distance_meters !== null ? round($row['record']->distance_meters).' m' : ($row['check']?->distance_meters !== null ? round($row['check']->distance_meters).' m' : '-') }}</td>
                            <td class="text-center">{{ $row['record']?->accuracy_meters !== null ? round($row['record']->accuracy_meters).' m' : ($row['check']?->accuracy_meters !== null ? round($row['check']->accuracy_meters).' m' : '-') }}</td>
                            <td>{{ $row['record']?->method_label ?? ($row['check'] ? 'GPS' : '-') }}</td>
                            <td class="text-center"><span class="badge-soft {{ $examState[1] }}">{{ $examState[0] }}</span></td>
                            <td class="text-end text-nowrap">
                                @if ($row['check'])
                                    <button type="button" class="btn btn-sm btn-ghost" title="Lihat percobaan check-in" data-bs-toggle="modal" data-bs-target="#checkDetail{{ $row['student']->user_id }}">
                                        <i class="bi bi-geo-alt"></i>
                                    </button>
                                @endif
                                <button type="button"
                                        class="btn btn-sm btn-soft"
                                        data-bs-toggle="modal"
                                        data-bs-target="#overrideModal"
                                        data-student-id="{{ $row['student']->user_id }}"
                                        data-student-name="{{ $row['student']->nama }}"
                                        data-current-status="{{ $row['record']?->status ?? 'alpha' }}">
                                    <i class="bi bi-pencil-square me-1"></i> Koreksi
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="bi bi-people"></i>
                                    Belum ada peserta pada sesi ini.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($rows as $row)
        @if ($row['check'])
            <div class="modal fade" id="checkDetail{{ $row['student']->user_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Percobaan Check-in</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body" style="font-size:.84rem">
                            <div class="d-flex justify-content-between mb-2"><span class="text-muted-2">Peserta</span><span class="fw-semibold">{{ $row['student']->nama }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><span class="text-muted-2">Waktu percobaan</span><span class="fw-semibold">{{ $row['check']->checked_at?->translatedFormat('d M Y H:i:s') }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><span class="text-muted-2">Koordinat</span><span class="fw-semibold">{{ $row['check']->latitude }}, {{ $row['check']->longitude }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><span class="text-muted-2">Jarak</span><span class="fw-semibold">{{ $row['check']->distance_meters !== null ? round($row['check']->distance_meters).' m' : '-' }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><span class="text-muted-2">Akurasi GPS</span><span class="fw-semibold">{{ $row['check']->accuracy_meters !== null ? round($row['check']->accuracy_meters).' m' : '-' }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><span class="text-muted-2">Status validasi</span><span class="fw-semibold">{{ $row['check']->validation_status }}</span></div>
                            <div class="d-flex justify-content-between"><span class="text-muted-2">Alasan</span><span class="fw-semibold text-end ms-3">{{ $row['check']->reason ?? '-' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <div class="modal fade" id="overrideModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route($overrideRoute) }}">
                    @csrf
                    @if ($session)
                        <input type="hidden" name="session_id" value="{{ $session->id }}">
                    @elseif ($tes)
                        <input type="hidden" name="tes_id" value="{{ $tes->tes_id }}">
                    @endif
                    <input type="hidden" name="student_id" id="overrideStudentId">

                    <div class="modal-header">
                        <h5 class="modal-title">Koreksi / Override Absensi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="text-muted-2" style="font-size:.78rem">Peserta</div>
                            <div class="fw-semibold" id="overrideStudentName">-</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status Kehadiran</label>
                            <select name="status" id="overrideStatus" class="form-select" required>
                                @foreach (\App\Models\AttendanceRecord::statusOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Alasan Koreksi <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" maxlength="500" required placeholder="Contoh: GPS perangkat rusak, diverifikasi petugas di lokasi."></textarea>
                            <div class="form-text">Alasan, actor, waktu, dan nilai sebelum/sesudah akan dicatat pada audit log.</div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-gradient">
                            <i class="bi bi-save me-1"></i> Simpan Koreksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($session && $audits->isNotEmpty())
        <div class="glass-card card-pad" data-aos="fade-up">
            <div class="section-head">
                <div>
                    <h3 class="section-title">Audit Log</h3>
                    <p class="section-sub">30 aktivitas terakhir pada sesi ini.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Event</th>
                            <th>Peserta</th>
                            <th>Actor</th>
                            <th>Metadata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($audits as $audit)
                            <tr>
                                <td class="text-nowrap">{{ $audit->created_at?->translatedFormat('d M Y H:i:s') }}</td>
                                <td><span class="badge-soft info">{{ $audit->event_type }}</span></td>
                                <td>{{ $audit->student_id ?? '-' }}</td>
                                <td>{{ $audit->actor_type ?? '-' }}{{ $audit->actor_id ? ' #'.$audit->actor_id : '' }}</td>
                                <td class="text-muted-2" style="font-size:.74rem">{{ $audit->metadata ? json_encode($audit->metadata, JSON_UNESCAPED_UNICODE) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        const modal = document.getElementById('overrideModal');

        if (!modal) {
            return;
        }

        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            if (!button) {
                return;
            }

            document.getElementById('overrideStudentId').value = button.getAttribute('data-student-id');
            document.getElementById('overrideStudentName').textContent = button.getAttribute('data-student-name');
            document.getElementById('overrideStatus').value = button.getAttribute('data-current-status') || 'alpha';
        });
    })();
</script>
@endpush
