@extends('layouts.peserta')

@section('title', 'Kelulusan')

@section('content')
    <div class="section-head mb-3">
        <div>
            <h3 class="section-title">Kelulusan</h3>
            <p class="section-sub">Surat keterangan lulus untuk setiap jenjang yang sudah diselesaikan.</p>
        </div>
        <span class="badge-soft info"><i class="bi bi-person-badge"></i> {{ $peserta->grup?->kelas_label ?? 'Belum ada kelas' }}</span>
    </div>

    @if (empty($daftarKelulusan))
        <div class="glass-card card-pad" data-aos="fade-up">
            <div class="empty-state">
                <i class="bi bi-award"></i>
                Surat kelulusan tersedia untuk peserta kelas akhir SD kelas 6, SMP/MTs kelas 3, SMA/MA kelas 3, atau SMK kelas 3.
            </div>
        </div>
    @else
        <div class="glass-card card-pad mb-4" data-aos="fade-up">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="text-muted-2" style="font-size:.76rem;letter-spacing:.08em;text-transform:uppercase">Nama Peserta</div>
                    <div class="fw-800" style="font-size:1.2rem">{{ $peserta->nama }}</div>
                    <div class="text-muted-2" style="font-size:.82rem">{{ $peserta->user_name }} &middot; {{ $peserta->grup?->kelas_label }}</div>
                </div>
                <div class="text-md-end">
                    <div class="text-muted-2" style="font-size:.76rem;text-transform:uppercase;letter-spacing:.08em">Surat Keterangan Lulus</div>
                    <div class="fw-800" style="font-size:1.8rem;color:#4338ca">{{ count($daftarKelulusan) }}</div>
                    <div class="text-muted-2" style="font-size:.76rem">jenjang selesai</div>
                </div>
            </div>
        </div>

        @foreach ($daftarKelulusan as $kelulusan)
            <div class="glass-card card-pad mb-4" data-aos="fade-up">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="text-muted-2" style="font-size:.76rem;letter-spacing:.08em;text-transform:uppercase">Status Kelulusan &middot; {{ $kelulusan['kelas'] }}</div>
                        <div class="fw-800" style="font-size:2rem;color:{{ $kelulusan['lulus'] ? '#059669' : '#b91c1c' }}">
                            {{ $kelulusan['lulus'] ? 'LULUS' : 'BELUM LULUS' }}
                        </div>
                        <div class="text-muted-2" style="font-size:.84rem">
                            {{ $kelulusan['lulus'] ? 'Surat keterangan lulus sudah dapat dicetak.' : 'Nilai raport belum lengkap atau belum memenuhi nilai minimal kelulusan.' }}
                        </div>
                    </div>
                    <div class="text-md-end">
                        <div class="text-muted-2" style="font-size:.76rem;text-transform:uppercase;letter-spacing:.08em">Rata-rata Raport</div>
                        <div class="fw-800" style="font-size:1.8rem;color:#4338ca">{{ $kelulusan['rata_rata'] !== null ? number_format($kelulusan['rata_rata'], 1) : '-' }}</div>
                        <div class="text-muted-2" style="font-size:.76rem">Nilai minimal {{ number_format($kelulusan['kkm'], 1) }} &middot; Predikat {{ $kelulusan['predikat'] }}</div>
                    </div>
                </div>

                @if ($kelulusan['lulus'])
                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <a href="{{ route('peserta.kelulusan.cetak', ['kelas' => $kelulusan['kode'], 'auto' => 1]) }}" target="_blank" class="btn btn-gradient">
                            <i class="bi bi-printer me-1"></i> Cetak Surat {{ $kelulusan['jenjang'] }}
                        </a>
                    </div>
                @endif

                <hr class="my-4">

                <div class="section-head">
                    <div>
                        <h3 class="section-title">Rekap Nilai &middot; {{ $kelulusan['kelas'] }}</h3>
                        <p class="section-sub">Nilai yang menjadi dasar surat keterangan lulus.</p>
                    </div>
                    <span class="badge-soft {{ $kelulusan['lengkap'] ? 'success' : 'warning' }}">
                        {{ $kelulusan['rapors']->count() }} / {{ $kelulusan['moduls']->count() }} mata pelajaran
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Mata Pelajaran</th>
                                <th>Guru Penilai</th>
                                <th class="text-end">Nilai</th>
                                <th class="text-center">Predikat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kelulusan['moduls'] as $modul)
                                @php $raport = $kelulusan['rapors']->get($modul->modul_id); @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $modul->modul_nama }}</td>
                                    <td class="text-muted-2" style="font-size:.82rem">{{ $raport?->guru?->nama ?? '-' }}</td>
                                    <td class="text-end fw-800" style="color:#4338ca">{{ $raport ? number_format((float) $raport->raport_nilai, 1) : '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge-soft {{ $raport?->predikat === 'A' ? 'success' : ($raport?->predikat === 'D' ? 'danger' : 'info') }}">
                                            {{ $raport?->predikat ?? '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
@endsection
