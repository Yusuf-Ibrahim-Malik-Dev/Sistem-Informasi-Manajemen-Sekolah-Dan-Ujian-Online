@extends('layouts.admin')

@section('title', 'Penilaian Raport')
@section('heading', 'Penilaian Raport')
@section('eyebrow', 'Guru')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3" data-aos="fade-up">
        <div class="section-sub mb-0">
            Pilih mata pelajaran dan kelas, lalu isi nilai raport peserta.
        </div>
        <a href="{{ route('admin.guru.index') }}" class="btn btn-ghost btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali ke Monitoring</a>
    </div>

    @if ($moduls->isEmpty())
        <div class="glass-card card-pad" data-aos="fade-up">
            <div class="empty-state">
                <i class="bi bi-journal-x"></i>
                Belum ada mata pelajaran yang dapat Anda nilai. Pastikan Anda memiliki topik/soal pada mata pelajaran tersebut.
            </div>
        </div>
    @else
        <div class="glass-card card-pad mb-4" data-aos="fade-up">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Mata Pelajaran</label>
                    <select name="modul" class="form-select" onchange="this.form.submit()">
                        @foreach ($moduls as $item)
                            <option value="{{ $item->modul_id }}" @selected($modul?->modul_id === $item->modul_id)>{{ $item->modul_nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Kelas</label>
                    <select name="grup" class="form-select" onchange="this.form.submit()">
                        @foreach ($grups as $item)
                            <option value="{{ $item->grup_id }}" @selected($grup?->grup_id === $item->grup_id)>
                                {{ $item->jenjang_label }} Kelas {{ (int) $item->grup_tingkat }} - {{ $item->grup_nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.guru.raport') }}" class="btn btn-ghost w-100"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
                </div>
            </form>
        </div>

        <div class="glass-card card-pad" data-aos="fade-up" data-aos-delay="60">
            <div class="section-head">
                <div>
                    <h3 class="section-title">{{ $modul?->modul_nama ?? '-' }}</h3>
                    <p class="section-sub">{{ $grup ? $grup->jenjang_label . ' Kelas ' . (int) $grup->grup_tingkat . ' - ' . $grup->grup_nama : 'Belum ada kelas' }} &middot; {{ $pesertas->count() }} peserta</p>
                </div>
            </div>

            @if ($grup && $pesertas->isNotEmpty())
                <form method="POST" action="{{ route('admin.guru.raport.simpan') }}">
                    @csrf
                    <input type="hidden" name="raport_modul_id" value="{{ $modul->modul_id }}">
                    <input type="hidden" name="raport_grup_id" value="{{ $grup->grup_id }}">

                    <div class="table-responsive">
                        <table class="table table-modern align-middle">
                            <thead>
                                <tr>
                                    <th>Peserta</th>
                                    <th>Username</th>
                                    <th style="width:130px">Nilai</th>
                                    <th>Catatan Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pesertas as $peserta)
                                    @php $nilai = $nilais->get($peserta->user_id); @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar" style="width:34px;height:34px;font-size:.78rem">{{ strtoupper(substr($peserta->nama, 0, 1)) }}</div>
                                                <span class="fw-semibold">{{ $peserta->nama }}</span>
                                            </div>
                                        </td>
                                        <td><code style="color:#4f46e5">{{ $peserta->user_name }}</code></td>
                                        <td>
                                            <input type="number"
                                                   name="nilai[{{ $peserta->user_id }}]"
                                                   value="{{ old('nilai.' . $peserta->user_id, $nilai?->raport_nilai) }}"
                                                   class="form-control @error('nilai.' . $peserta->user_id) is-invalid @enderror"
                                                   min="0" max="100" step="0.01" placeholder="0 - 100">
                                            @error('nilai.' . $peserta->user_id)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="catatan[{{ $peserta->user_id }}]"
                                                   value="{{ old('catatan.' . $peserta->user_id, $nilai?->raport_catatan) }}"
                                                   class="form-control"
                                                   maxlength="255" placeholder="Catatan (opsional)">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button class="btn btn-gradient"><i class="bi bi-save me-1"></i> Simpan Nilai Raport</button>
                    </div>
                </form>
            @else
                <div class="empty-state">
                    <i class="bi bi-person-x"></i>
                    Belum ada peserta pada kelas ini.
                </div>
            @endif
        </div>
    @endif
@endsection
