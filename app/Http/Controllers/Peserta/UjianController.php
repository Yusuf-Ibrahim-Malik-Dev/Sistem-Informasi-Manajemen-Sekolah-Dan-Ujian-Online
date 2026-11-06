<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Jawaban;
use App\Models\Tes;
use App\Models\TesSoal;
use App\Models\TesUser;
use App\Support\HasilUjian;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UjianController extends Controller
{
    public function kerjakan(Request $request, Tes $tes)
    {
        $attempt = $this->attemptBerjalan($tes);

        if (! $attempt) {
            return redirect()->route('peserta.tes.show', $tes)
                ->with('error', 'Anda belum memulai tes ini atau tes sudah selesai.');
        }

        if ($attempt->is_kedaluwarsa) {
            $attempt->finalisasi();

            return redirect()->route('peserta.ujian.hasil', $tes)
                ->with('error', 'Waktu ujian telah habis. Jawaban Anda otomatis dikumpulkan.');
        }

        $daftarSoal = $attempt->soals()->with('soal')->get();

        $current = $request->filled('soal')
            ? $daftarSoal->firstWhere('tessoal_id', (int) $request->soal)
            : $daftarSoal->firstWhere('tessoal_change_time', null);

        $current ??= $daftarSoal->first();

        abort_unless($current, 404, 'Soal tidak ditemukan pada ujian ini.');

        $current->load(['soal.jawabans', 'pilihan.jawaban']);

        if (! $current->tessoal_display_time) {
            $current->update(['tessoal_display_time' => now()]);
        }

        $terpilih = $current->pilihan->firstWhere('soaljawaban_selected', 1);

        return view('peserta.ujian', [
            'tes' => $tes,
            'attempt' => $attempt,
            'daftarSoal' => $daftarSoal,
            'current' => $current,
            'terpilih' => $terpilih,
            'sisaDetik' => $attempt->sisa_detik,
        ]);
    }

    public function jawab(Request $request, Tes $tes)
    {
        $attempt = $this->attemptBerjalan($tes);

        if (! $attempt) {
            return redirect()->route('peserta.tes.show', $tes);
        }

        if ($attempt->is_kedaluwarsa) {
            $attempt->finalisasi();

            return redirect()->route('peserta.ujian.hasil', $tes)
                ->with('error', 'Waktu ujian telah habis.');
        }

        $data = $request->validate([
            'tessoal_id' => ['required', 'integer'],
            'jawaban_id' => ['nullable', 'integer'],
            'jawaban_text' => ['nullable', 'string'],
            'ragu' => ['nullable', 'boolean'],
            'aksi' => ['nullable', 'in:simpan,lanjut,selesai'],
        ]);

        $tesSoal = $attempt->soals()->with('soal')->whereKey($data['tessoal_id'])->first();

        abort_unless($tesSoal, 404, 'Soal tidak ditemukan pada ujian ini.');

        $soal = $tesSoal->soal;
        $nilai = 0;

        if ((int) $soal->soal_tipe === 1) {
            if (empty($data['jawaban_id'])) {
                throw ValidationException::withMessages(['jawaban_id' => 'Pilih salah satu jawaban terlebih dahulu.']);
            }

            $jawaban = Jawaban::where('jawaban_soal_id', $soal->soal_id)
                ->whereKey($data['jawaban_id'])
                ->first();

            if (! $jawaban) {
                throw ValidationException::withMessages(['jawaban_id' => 'Pilihan jawaban tidak valid.']);
            }

            $tesSoal->pilihan()->update(['soaljawaban_selected' => -1]);
            $tesSoal->pilihan()->where('soaljawaban_jawaban_id', $jawaban->jawaban_id)
                ->update(['soaljawaban_selected' => 1]);

            $nilai = $jawaban->jawaban_benar ? (float) $tes->tes_score_right : (float) $tes->tes_score_wrong;
        } elseif ((int) $soal->soal_tipe === 3) {
            $teks = trim((string) ($data['jawaban_text'] ?? ''));

            if ($teks === '') {
                throw ValidationException::withMessages(['jawaban_text' => 'Jawaban tidak boleh kosong.']);
            }

            $benar = strtoupper($teks) === strtoupper(trim((string) $soal->soal_kunci));
            $nilai = $benar ? (float) $tes->tes_score_right : (float) $tes->tes_score_wrong;
        } else {
            $teks = trim((string) ($data['jawaban_text'] ?? ''));

            if ($teks === '') {
                throw ValidationException::withMessages(['jawaban_text' => 'Jawaban tidak boleh kosong.']);
            }

            $nilai = 0;
        }

        $reaksi = $tesSoal->tessoal_display_time
            ? max(0, (int) $tesSoal->tessoal_display_time->diffInSeconds(now()))
            : 0;

        $tesSoal->update([
            'tessoal_jawaban_text' => (int) $soal->soal_tipe === 1 ? null : ($data['jawaban_text'] ?? null),
            'tessoal_nilai' => $nilai,
            'tessoal_change_time' => now(),
            'tessoal_display_time' => $tesSoal->tessoal_display_time ?? now(),
            'tessoal_reaction_time' => $reaksi,
            'tessoal_ragu' => $request->boolean('ragu') ? 1 : 0,
            'tessoal_num_answers' => (int) $soal->soal_tipe === 1 ? 1 : 1,
        ]);

        $aksi = $data['aksi'] ?? 'simpan';

        if ($aksi === 'selesai') {
            return $this->selesaikan($attempt, $tes);
        }

        if ($aksi === 'lanjut') {
            $next = $attempt->soals()->where('tessoal_order', '>', $tesSoal->tessoal_order)->first();

            return redirect()->route('peserta.ujian.kerjakan', [
                'tes' => $tes,
                'soal' => $next?->tessoal_id ?? $tesSoal->tessoal_id,
            ])->with('success', 'Jawaban tersimpan.');
        }

        return redirect()->route('peserta.ujian.kerjakan', [
            'tes' => $tes,
            'soal' => $tesSoal->tessoal_id,
        ])->with('success', 'Jawaban tersimpan.');
    }

    public function ragu(Request $request, Tes $tes)
    {
        $attempt = $this->attemptBerjalan($tes);

        if (! $attempt) {
            return redirect()->route('peserta.tes.show', $tes);
        }

        $data = $request->validate([
            'tessoal_id' => ['required', 'integer'],
        ]);

        $tesSoal = $attempt->soals()->whereKey($data['tessoal_id'])->first();
        abort_unless($tesSoal, 404);

        $tesSoal->update(['tessoal_ragu' => $tesSoal->tessoal_ragu ? 0 : 1]);

        return redirect()->route('peserta.ujian.kerjakan', [
            'tes' => $tes,
            'soal' => $tesSoal->tessoal_id,
        ])->with('success', 'Status ragu berhasil diperbarui.');
    }

    public function selesai(Tes $tes)
    {
        $attempt = $this->attemptBerjalan($tes);

        if (! $attempt) {
            return redirect()->route('peserta.tes.show', $tes);
        }

        return $this->selesaikan($attempt, $tes);
    }

    public function hasil(Tes $tes)
    {
        $peserta = auth('peserta')->user();

        $attempt = TesUser::where('tesuser_tes_id', $tes->tes_id)
            ->where('tesuser_user_id', $peserta->user_id)
            ->latest('tesuser_id')
            ->first();

        if (! $attempt) {
            return redirect()->route('peserta.tes.show', $tes)
                ->with('error', 'Anda belum mengikuti tes ini.');
        }

        if ($attempt->is_berjalan) {
            return redirect()->route('peserta.ujian.kerjakan', $tes);
        }

        $ringkasan = HasilUjian::ringkas($attempt);
        $bolehLihatRincian = HasilUjian::semuaPesertaSelesai($tes);
        $ranking = $tes->tes_ranking_to_users
            ? HasilUjian::peringkat($tes, $peserta->user_id)
            : [];

        return view('peserta.hasil', [
            'tes' => $tes,
            'attempt' => $attempt,
            'benar' => $ringkasan['benar'],
            'salah' => $ringkasan['salah'],
            'nilai' => $ringkasan['nilai'],
            'bolehLihatRincian' => $bolehLihatRincian,
            'ranking' => $ranking,
        ]);
    }

    public function cetak(Tes $tes)
    {
        $peserta = auth('peserta')->user();

        $attempt = TesUser::where('tesuser_tes_id', $tes->tes_id)
            ->where('tesuser_user_id', $peserta->user_id)
            ->latest('tesuser_id')
            ->first();

        if (! $attempt) {
            return redirect()->route('peserta.tes.show', $tes)
                ->with('error', 'Anda belum mengikuti tes ini.');
        }

        if ($attempt->is_berjalan) {
            return redirect()->route('peserta.ujian.kerjakan', $tes);
        }

        if (! $tes->tes_results_to_users) {
            return redirect()->route('peserta.ujian.hasil', $tes)
                ->with('error', 'Hasil ujian ini belum diizinkan untuk dicetak.');
        }

        if (! HasilUjian::semuaPesertaSelesai($tes)) {
            return redirect()->route('peserta.ujian.hasil', $tes)
                ->with('error', 'Hasil ujian belum dapat dicetak sampai seluruh peserta menyelesaikan ujian.');
        }

        $ringkasan = HasilUjian::ringkas($attempt);

        return view('hasil-cetak', [
            'tes' => $tes,
            'attempt' => $attempt,
            'peserta' => $attempt->peserta,
            'benar' => $ringkasan['benar'],
            'salah' => $ringkasan['salah'],
            'nilai' => $ringkasan['nilai'],
            'namaGuru' => HasilUjian::guru($attempt),
            'kembali' => route('peserta.ujian.hasil', $tes),
            'namaCbt' => \App\Models\Konfigurasi::get('cbt_nama', 'CBT Online'),
        ]);
    }

    private function attemptBerjalan(Tes $tes): ?TesUser
    {
        $peserta = auth('peserta')->user();

        return TesUser::where('tesuser_tes_id', $tes->tes_id)
            ->where('tesuser_user_id', $peserta->user_id)
            ->where('tesuser_status', TesUser::STATUS_BERJALAN)
            ->latest('tesuser_id')
            ->first();
    }

    private function selesaikan(TesUser $attempt, Tes $tes)
    {
        $attempt->finalisasi();

        return redirect()->route('peserta.ujian.hasil', $tes)
            ->with('success', 'Ujian selesai. Jawaban Anda telah dikumpulkan.');
    }
}
