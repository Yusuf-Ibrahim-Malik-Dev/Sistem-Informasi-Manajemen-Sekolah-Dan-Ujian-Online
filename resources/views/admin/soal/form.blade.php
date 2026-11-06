@extends('layouts.admin')

@section('title', $soal->exists ? 'Edit Soal' : 'Tambah Soal')
@section('heading', $soal->exists ? 'Edit Soal' : 'Tambah Soal')
@section('eyebrow', 'Bank Soal')

@php
    $existing = $jawabans->map(fn ($j) => ['id' => $j->jawaban_id, 'detail' => \App\Support\HtmlText::plain($j->jawaban_detail)])->values();

    if ($existing->isEmpty()) {
        $existing = collect([['id' => null, 'detail' => ''], ['id' => null, 'detail' => '']]);
    }

    if (old('jawaban')) {
        $existing = collect(old('jawaban'))->map(fn ($j) => ['id' => $j['id'] ?? null, 'detail' => \App\Support\HtmlText::plain($j['detail'] ?? '')])->values();
    }

    $benarIndex = old('jawaban_benar');
    if ($benarIndex === null) {
        $found = $jawabans->search(fn ($j) => (bool) $j->jawaban_benar);
        $benarIndex = $found === false ? 0 : $found;
    }
@endphp

@section('content')
    <script>
        window.__soalInitial = {
            tipe: @json((string) old('soal_tipe', $soal->soal_tipe)),
            jawaban: @json($existing),
            benar: @json((int) $benarIndex),
        };
    </script>

    <form method="POST" action="{{ $soal->exists ? route('admin.soal.update', $soal) : route('admin.soal.store') }}" enctype="multipart/form-data" x-data="soalForm()" data-aos="fade-up">
        @csrf
        @if ($soal->exists)
            @method('PUT')
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-card card-pad mb-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Konten Soal</h3>
                            <p class="section-sub">Gunakan HTML sederhana seperti &lt;p&gt;, &lt;strong&gt;, &lt;ul&gt;, atau &lt;img&gt; bila diperlukan.</p>
                        </div>
                        <a href="{{ route('admin.soal.index') }}" class="btn btn-ghost btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Topik</label>
                        <select name="soal_topik_id" class="form-select" required>
                            <option value="">Pilih topik</option>
                            @foreach ($topiks as $topik)
                                <option value="{{ $topik->topik_id }}" @selected(old('soal_topik_id', $soal->soal_topik_id) == $topik->topik_id)>
                                    {{ $topik->modul?->modul_nama }} &mdash; {{ $topik->topik_nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Isi Soal</label>
                        <textarea name="soal_detail" class="form-control" rows="7" placeholder="Tulis pertanyaan di sini..." required>{{ old('soal_detail', \App\Support\HtmlText::plain($soal->soal_detail)) }}</textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipe Soal</label>
                            <select name="soal_tipe" class="form-select" x-model="tipe" required>
                                <option value="1">Pilihan Ganda</option>
                                <option value="2">Essay</option>
                                <option value="3">Jawaban Singkat</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tingkat Kesulitan</label>
                            <select name="soal_difficulty" class="form-select" required>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" @selected(old('soal_difficulty', $soal->soal_difficulty) == $i)>Level {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <div class="glass-card card-pad mb-4" x-show="tipe === '1'" x-cloak>
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Pilihan Jawaban</h3>
                            <p class="section-sub">Tandai satu jawaban yang benar.</p>
                        </div>
                        <button type="button" class="btn btn-soft btn-sm" @click="add()"><i class="bi bi-plus-lg me-1"></i> Tambah Pilihan</button>
                    </div>

                    <template x-for="(item, index) in jawaban" :key="index">
                        <div class="d-flex gap-2 align-items-start mb-2">
                            <label class="answer-option flex-grow-1" :class="benar === index ? 'selected' : ''" style="cursor:pointer">
                                <input type="radio" name="jawaban_benar" :value="index" x-model.number="benar" class="form-check-input mt-1">
                                <span class="answer-key" x-text="String.fromCharCode(65 + index)"></span>
                                <span class="flex-grow-1">
                                    <textarea :name="'jawaban[' + index + '][detail]'" x-model="item.detail" class="form-control" rows="2" placeholder="Teks pilihan jawaban..."></textarea>
                                </span>
                            </label>
                            <input type="hidden" :name="'jawaban[' + index + '][id]'" :value="item.id">
                            <button type="button" class="btn btn-ghost btn-icon text-danger" @click="remove(index)" :disabled="jawaban.length <= 2" title="Hapus pilihan">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="glass-card card-pad mb-4" x-show="tipe === '3'" x-cloak>
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Kunci Jawaban Singkat</h3>
                            <p class="section-sub">Penilaian otomatis membandingkan jawaban peserta dengan kunci ini (tidak peka huruf besar/kecil).</p>
                        </div>
                    </div>
                    <input type="text" name="soal_kunci" class="form-control" value="{{ old('soal_kunci', $soal->soal_kunci) }}" placeholder="Contoh: 11">
                </div>

                <div class="glass-card card-pad" x-show="tipe === '2'" x-cloak>
                    <div class="d-flex align-items-start gap-3">
                        <div class="stat-icon bg-grad-3 mb-0"><i class="bi bi-pencil-square"></i></div>
                        <div>
                            <h3 class="section-title mb-1">Soal Essay</h3>
                            <p class="section-sub mb-0">Jawaban essay akan dinilai manual oleh operator melalui menu Hasil &amp; Evaluasi.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card card-pad mb-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Pengaturan</h3>
                            <p class="section-sub">Perilaku soal saat ujian.</p>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="soal_aktif" value="1" id="soal_aktif" @checked(old('soal_aktif', $soal->exists ? $soal->soal_aktif : true))>
                        <label class="form-check-label" for="soal_aktif" style="font-size:.84rem">Soal aktif</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="soal_audio_play" value="1" id="soal_audio_play" @checked(old('soal_audio_play', $soal->soal_audio_play))>
                        <label class="form-check-label" for="soal_audio_play" style="font-size:.84rem">Audio diputar otomatis</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="soal_inline_answers" value="1" id="soal_inline_answers" @checked(old('soal_inline_answers', $soal->soal_inline_answers))>
                        <label class="form-check-label" for="soal_inline_answers" style="font-size:.84rem">Jawaban inline</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="soal_auto_next" value="1" id="soal_auto_next" @checked(old('soal_auto_next', $soal->soal_auto_next))>
                        <label class="form-check-label" for="soal_auto_next" style="font-size:.84rem">Lanjut otomatis</label>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Timer Soal (detik)</label>
                        <input type="number" name="soal_timer" class="form-control" min="0" value="{{ old('soal_timer', $soal->soal_timer) }}" placeholder="Kosongkan jika tanpa timer">
                    </div>
                </div>

                <div class="glass-card card-pad mb-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Media Audio</h3>
                            <p class="section-sub">Opsional, maksimal 20 MB.</p>
                        </div>
                    </div>

                    @if ($soal->soal_audio)
                        <div class="d-flex align-items-center justify-content-between gap-2 p-2 mb-3" style="border-radius:12px;background:#f8fafc;border:1px solid var(--line)">
                            <div class="text-truncate" style="font-size:.8rem"><i class="bi bi-music-note-beamed me-1"></i>{{ $soal->soal_audio }}</div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="hapus_audio" value="1" id="hapus_audio">
                                <label class="form-check-label" for="hapus_audio" style="font-size:.74rem">Hapus</label>
                            </div>
                        </div>
                    @endif

                    <input type="file" name="soal_audio" class="form-control" accept=".mp3,.wav,.ogg,.m4a">
                </div>

                <div class="glass-card card-pad">
                    <button class="btn btn-gradient w-100 mb-2"><i class="bi bi-save me-1"></i> {{ $soal->exists ? 'Simpan Perubahan' : 'Simpan Soal' }}</button>
                    <a href="{{ route('admin.soal.index') }}" class="btn btn-ghost w-100">Batal</a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    window.soalForm = function () {
        return {
            tipe: window.__soalInitial.tipe,
            jawaban: window.__soalInitial.jawaban,
            benar: window.__soalInitial.benar,
            add() {
                this.jawaban.push({ id: null, detail: '' });
            },
            remove(index) {
                if (this.jawaban.length <= 2) return;
                this.jawaban.splice(index, 1);
                if (this.benar >= this.jawaban.length) {
                    this.benar = this.jawaban.length - 1;
                }
            },
        };
    };
</script>
@endpush
