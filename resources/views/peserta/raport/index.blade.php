@extends('layouts.peserta')

@section('title', 'Raport')

@section('content')
    @php $aktifLabel = $aktif['label'] ?? 'Belum ada kelas'; @endphp

    <div class="section-head mb-3">
        <div>
            <h3 class="section-title">Raport Peserta</h3>
            <p class="section-sub">Nilai raport per mata pelajaran yang diinput oleh guru.</p>
        </div>
        <button type="button" class="btn btn-soft btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i> Cetak</button>
    </div>

    <div class="glass-card card-pad mb-4" data-aos="fade-up">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="text-muted-2" style="font-size:.76rem;letter-spacing:.08em;text-transform:uppercase">Nama Peserta</div>
                <div class="fw-800" style="font-size:1.2rem">{{ $peserta->nama }}</div>
                <div class="text-muted-2" style="font-size:.82rem">
                    {{ $peserta->user_name }} &middot; {{ $peserta->grup?->kelas_label ?? 'Belum ada kelas' }}
                    @if ($aktif && ! $aktif['aktif'])
                        &middot; melihat riwayat {{ $aktifLabel }}
                    @endif
                </div>
            </div>
            <div class="text-md-end">
                <div class="text-muted-2" style="font-size:.76rem;text-transform:uppercase;letter-spacing:.08em">Rata-rata {{ $aktifLabel }}</div>
                <div class="fw-800" style="font-size:1.8rem;color:#4338ca">{{ $rataRata !== null ? number_format($rataRata, 1) : '-' }}</div>
                <div class="text-muted-2" style="font-size:.76rem">{{ $aktif['jumlah'] ?? 0 }} dari {{ $moduls->count() }} mata pelajaran dinilai</div>
            </div>
        </div>
    </div>

    @if ($levels->count() > 1)
        <div class="glass-card card-pad mb-4" data-aos="fade-up">
            <div class="section-head">
                <div>
                    <h3 class="section-title">Riwayat Kelas</h3>
                    <p class="section-sub">Pilih kelas untuk melihat nilai raport dari jenjang sebelumnya.</p>
                </div>
                <span class="badge-soft info">{{ $levels->count() }} kelas</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($levels as $level)
                    <a href="{{ route('peserta.raport.index', ['kelas' => $level['key']]) }}"
                       class="btn btn-sm {{ $aktif && $level['key'] === $aktif['key'] ? 'btn-gradient' : 'btn-ghost' }}">
                        {{ $level['label'] }}
                        <span class="ms-1" style="opacity:.78">{{ $level['rata_rata'] !== null ? number_format($level['rata_rata'], 1) : '-' }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="glass-card card-pad" data-aos="fade-up">
        <div class="section-head">
            <div>
                <h3 class="section-title">Nilai Mata Pelajaran &middot; {{ $aktifLabel }}</h3>
                <p class="section-sub">Nilai diberikan oleh guru pengampu masing-masing mata pelajaran.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Mata Pelajaran</th>
                        <th>Guru Penilai</th>
                        <th>Catatan</th>
                        <th class="text-end">Nilai</th>
                        <th class="text-center">Predikat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($moduls as $modul)
                        @php $raport = $rapors->get($modul->modul_id); @endphp
                        <tr>
                            <td class="fw-semibold">{{ $modul->modul_nama }}</td>
                            <td class="text-muted-2" style="font-size:.82rem">{{ $raport?->guru?->nama ?? '-' }}</td>
                            <td class="text-muted-2" style="font-size:.82rem">{{ $raport?->raport_catatan ?: '-' }}</td>
                            <td class="text-end fw-800" style="color:#4338ca">{{ $raport ? number_format((float) $raport->raport_nilai, 1) : '-' }}</td>
                            <td class="text-center">
                                @if ($raport)
                                    <span class="badge-soft {{ $raport->predikat === 'A' ? 'success' : ($raport->predikat === 'D' ? 'danger' : 'info') }}">
                                        {{ $raport->predikat }}
                                    </span>
                                @else
                                    <span class="badge-soft neutral">Belum dinilai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-journal-x"></i>
                                    Belum ada mata pelajaran aktif.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
