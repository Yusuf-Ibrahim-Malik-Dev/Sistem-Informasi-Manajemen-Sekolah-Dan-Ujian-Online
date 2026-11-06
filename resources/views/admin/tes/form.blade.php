@extends('layouts.admin')

@section('title', $tes->exists ? 'Edit Tes' : 'Buat Tes')
@section('heading', $tes->exists ? 'Edit Tes' : 'Buat Tes Baru')
@section('eyebrow', 'Manajemen Ujian')

@php
    $begin = old('tes_begin_time', $tes->tes_begin_time ? $tes->tes_begin_time->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i'));
    $end = old('tes_end_time', $tes->tes_end_time ? $tes->tes_end_time->format('Y-m-d\TH:i') : now()->addDays(7)->format('Y-m-d\TH:i'));
    $selectedGroups = old('groups', $tes->exists ? $tes->groups->pluck('grup_id')->all() : []);

    $initialSets = old('sets')
        ? collect(old('sets'))->map(fn ($s) => [
            'topik_id' => (int) ($s['topik_id'] ?? 0),
            'tipe' => (int) ($s['tipe'] ?? 1),
            'difficulty' => (int) ($s['difficulty'] ?? 1),
            'jumlah' => (int) ($s['jumlah'] ?? 1),
            'acak_jawaban' => ! empty($s['acak_jawaban']),
            'acak_soal' => ! empty($s['acak_soal']),
        ])->values()
        : $sets->map(fn ($s) => [
            'topik_id' => (int) $s->tset_topik_id,
            'tipe' => (int) $s->tset_tipe,
            'difficulty' => (int) $s->tset_difficulty,
            'jumlah' => (int) $s->tset_jumlah,
            'acak_jawaban' => (bool) $s->tset_acak_jawaban,
            'acak_soal' => (bool) $s->tset_acak_soal,
        ])->values();

    if ($initialSets->isEmpty()) {
        $initialSets = collect([['topik_id' => 0, 'tipe' => 1, 'difficulty' => 1, 'jumlah' => 1, 'acak_jawaban' => true, 'acak_soal' => true]]);
    }
@endphp

@section('content')
    <script>
        window.__tesInitial = @json(['sets' => $initialSets]);
    </script>

    <form method="POST" action="{{ $tes->exists ? route('admin.tes.update', $tes) : route('admin.tes.store') }}" x-data="tesForm()" data-aos="fade-up">
        @csrf
        @if ($tes->exists)
            @method('PUT')
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-card card-pad mb-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Informasi Tes</h3>
                            <p class="section-sub">Nama, deskripsi, dan periode pelaksanaan ujian.</p>
                        </div>
                        <a href="{{ route('admin.tes.index') }}" class="btn btn-ghost btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Tes</label>
                        <input type="text" name="tes_nama" class="form-control" value="{{ old('tes_nama', $tes->tes_nama) }}" placeholder="Contoh: Try Out Matematika" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi / Petunjuk</label>
                        <textarea name="tes_detail" class="form-control" rows="3" placeholder="Petunjuk pengerjaan untuk peserta..." required>{{ old('tes_detail', $tes->tes_detail) }}</textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mulai</label>
                            <input type="datetime-local" name="tes_begin_time" class="form-control" value="{{ $begin }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Berakhir</label>
                            <input type="datetime-local" name="tes_end_time" class="form-control" value="{{ $end }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Durasi (menit)</label>
                            <input type="number" name="tes_duration_time" class="form-control" min="1" value="{{ old('tes_duration_time', $tes->tes_duration_time) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rentang IP</label>
                            <input type="text" name="tes_ip_range" class="form-control" value="{{ old('tes_ip_range', $tes->tes_ip_range ?: '*.*.*.*') }}" placeholder="*.*.*.*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Biaya Ujian (Rp)</label>
                            <input type="number" step="1000" min="0" name="tes_biaya" class="form-control" value="{{ old('tes_biaya', $tes->tes_biaya ?? 0) }}" required>
                            <div class="text-muted-2 mt-1" style="font-size:.74rem">Isi 0 jika ujian tidak dipungut biaya.</div>
                        </div>
                    </div>
                </div>

                <div class="glass-card card-pad mb-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Absensi Pra-Ujian (Geofencing)</h3>
                            <p class="section-sub">Peserta wajib check-in lokasi sebelum tombol mulai ujian diaktifkan.</p>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="tes_absensi_wajib" value="1" id="tes_absensi_wajib" @checked(old('tes_absensi_wajib', $tes->tes_absensi_wajib))>
                        <label class="form-check-label" for="tes_absensi_wajib" style="font-size:.84rem">Wajibkan absensi pra-ujian</label>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Lokasi Presensi</label>
                            <select name="tes_absensi_location_id" class="form-select">
                                <option value="">Ikuti lokasi default sekolah</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" @selected((string) old('tes_absensi_location_id', $tes->tes_absensi_location_id) === (string) $location->id)>
                                        {{ $location->name }}{{ $location->school ? ' - '.$location->school->school_name : '' }} ({{ $location->radius_meters }} m)
                                    </option>
                                @endforeach
                            </select>
                            @if ($locations->isEmpty())
                                <div class="text-muted-2 mt-1" style="font-size:.74rem">
                                    Belum ada lokasi aktif. <a href="{{ route('admin.absensi.lokasi') }}">Atur titik lokasi</a> terlebih dahulu.
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Radius</label>
                            <input type="text" class="form-control" value="Mengikuti konfigurasi lokasi (default 50 m)" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-in Dibuka</label>
                            <input type="datetime-local" name="tes_checkin_mulai" class="form-control" value="{{ old('tes_checkin_mulai', $tes->tes_checkin_mulai?->format('Y-m-d\TH:i')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-in Ditutup</label>
                            <input type="datetime-local" name="tes_checkin_selesai" class="form-control" value="{{ old('tes_checkin_selesai', $tes->tes_checkin_selesai?->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0" style="font-size:.78rem">
                        <i class="bi bi-shield-check me-1"></i>
                        Validasi jarak, akurasi GPS, dan waktu check-in dilakukan di server. Jika rentang waktu kosong, sistem memakai jadwal mulai dan berakhir ujian.
                    </div>
                </div>

                <div class="glass-card card-pad mb-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Komposisi Soal</h3>
                            <p class="section-sub">Soal diambil acak dari bank soal sesuai topik dan tingkat kesulitan.</p>
                        </div>
                        <button type="button" class="btn btn-soft btn-sm" @click="addSet()"><i class="bi bi-plus-lg me-1"></i> Tambah Set</button>
                    </div>

                    <template x-for="(set, index) in sets" :key="index">
                        <div class="p-3 mb-3" style="border-radius:16px;background:#f8fafc;border:1px solid var(--line)">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge-soft"><i class="bi bi-layers"></i> Set <span x-text="index + 1"></span></span>
                                <button type="button" class="btn btn-ghost btn-icon text-danger" @click="removeSet(index)" :disabled="sets.length <= 1"><i class="bi bi-trash3"></i></button>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label">Topik</label>
                                    <select class="form-select" x-model.number="set.topik_id" :name="'sets[' + index + '][topik_id]'" required>
                                        <option value="0">Pilih topik</option>
                                        @foreach ($topiks as $topik)
                                            <option value="{{ $topik->topik_id }}">{{ $topik->modul?->modul_nama }} &mdash; {{ $topik->topik_nama }} ({{ $topik->soals_count }} soal)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Tipe</label>
                                    <select class="form-select" x-model.number="set.tipe" :name="'sets[' + index + '][tipe]'">
                                        <option value="0">Semua</option>
                                        <option value="1">PG</option>
                                        <option value="2">Essay</option>
                                        <option value="3">Singkat</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Level</label>
                                    <select class="form-select" x-model.number="set.difficulty" :name="'sets[' + index + '][difficulty]'">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">L{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Jumlah Soal</label>
                                    <input type="number" class="form-control" min="1" x-model.number="set.jumlah" :name="'sets[' + index + '][jumlah]'" required>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-3 mt-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" value="1" x-model="set.acak_soal" :name="'sets[' + index + '][acak_soal]'" :id="'acakSoal' + index">
                                    <label class="form-check-label" :for="'acakSoal' + index" style="font-size:.78rem">Acak soal</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" value="1" x-model="set.acak_jawaban" :name="'sets[' + index + '][acak_jawaban]'" :id="'acakJawaban' + index">
                                    <label class="form-check-label" :for="'acakJawaban' + index" style="font-size:.78rem">Acak jawaban</label>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="glass-card card-pad">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Group Peserta</h3>
                            <p class="section-sub">Hanya peserta dari group terpilih yang dapat mengikuti tes.</p>
                        </div>
                    </div>

                    <div class="row g-2">
                        @foreach ($grups as $grup)
                            <div class="col-md-4">
                                <label class="answer-option" style="cursor:pointer">
                                    <input class="form-check-input mt-1" type="checkbox" name="groups[]" value="{{ $grup->grup_id }}" @checked(in_array($grup->grup_id, $selectedGroups))>
                                    <span class="fw-semibold" style="font-size:.84rem">{{ $grup->grup_nama }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card card-pad mb-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Penilaian</h3>
                            <p class="section-sub">Bobot nilai setiap butir soal.</p>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Nilai Benar</label>
                            <input type="number" step="0.01" name="tes_score_right" class="form-control" value="{{ old('tes_score_right', $tes->tes_score_right) }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Nilai Salah</label>
                            <input type="number" step="0.01" name="tes_score_wrong" class="form-control" value="{{ old('tes_score_wrong', $tes->tes_score_wrong) }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tidak Dijawab</label>
                            <input type="number" step="0.01" name="tes_score_unanswered" class="form-control" value="{{ old('tes_score_unanswered', $tes->tes_score_unanswered) }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Nilai Maksimal</label>
                            <input type="number" step="0.01" name="tes_max_score" class="form-control" value="{{ old('tes_max_score', $tes->tes_max_score) }}" required>
                        </div>
                    </div>
                </div>

                <div class="glass-card card-pad mb-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Opsi Ujian</h3>
                            <p class="section-sub">Keamanan dan tampilan hasil.</p>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="tes_token" value="1" id="tes_token" @checked(old('tes_token', $tes->tes_token))>
                        <label class="form-check-label" for="tes_token" style="font-size:.84rem">Wajib token</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="tes_results_to_users" value="1" id="tes_results_to_users" @checked(old('tes_results_to_users', $tes->tes_results_to_users))>
                        <label class="form-check-label" for="tes_results_to_users" style="font-size:.84rem">Tampilkan nilai ke peserta</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="tes_detail_to_users" value="1" id="tes_detail_to_users" @checked(old('tes_detail_to_users', $tes->tes_detail_to_users))>
                        <label class="form-check-label" for="tes_detail_to_users" style="font-size:.84rem">Tampilkan detail jawaban</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="tes_ranking_to_users" value="1" id="tes_ranking_to_users" @checked(old('tes_ranking_to_users', $tes->tes_ranking_to_users))>
                        <label class="form-check-label" for="tes_ranking_to_users" style="font-size:.84rem">Tampilkan ranking ujian</label>
                        <div class="text-muted small mt-1">Ranking menampilkan nilai peserta yang sudah menyelesaikan ujian.</div>
                    </div>
                </div>

                <div class="glass-card card-pad">
                    <button class="btn btn-gradient w-100 mb-2"><i class="bi bi-save me-1"></i> {{ $tes->exists ? 'Simpan Perubahan' : 'Simpan Tes' }}</button>
                    <a href="{{ route('admin.tes.index') }}" class="btn btn-ghost w-100">Batal</a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    window.tesForm = function () {
        return {
            sets: window.__tesInitial.sets,
            addSet() {
                this.sets.push({ topik_id: 0, tipe: 1, difficulty: 1, jumlah: 1, acak_jawaban: true, acak_soal: true });
            },
            removeSet(index) {
                if (this.sets.length <= 1) return;
                this.sets.splice(index, 1);
            },
        };
    };
</script>
@endpush
