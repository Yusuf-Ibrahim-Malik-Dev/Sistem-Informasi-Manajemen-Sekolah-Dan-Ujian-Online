<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Konfigurasi;
use App\Models\Pembayaran;
use App\Models\Soal;
use App\Models\Tes;
use App\Models\TesSoal;
use App\Models\TesToken;
use App\Models\TesUser;
use App\Support\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    public function index()
    {
        $peserta = auth('peserta')->user();

        $tes = Tes::with('groups')
            ->withCount('topikSets')
            ->whereHas('groups', fn ($q) => $q->where('grup_id', $peserta->user_grup_id))
            ->where(fn ($q) => $q->whereNull('tes_end_time')->orWhere('tes_end_time', '>=', now()))
            ->orderBy('tes_begin_time')
            ->get();

        $attempts = TesUser::where('tesuser_user_id', $peserta->user_id)
            ->get()
            ->keyBy('tesuser_tes_id');

        $ringkasan = [
            'tersedia' => $tes->count(),
            'selesai' => $attempts->where('tesuser_status', TesUser::STATUS_SELESAI)->count(),
            'berjalan' => $attempts->where('tesuser_status', TesUser::STATUS_BERJALAN)->count(),
        ];

        $rankingTes = Tes::where('tes_ranking_to_users', true)
            ->whereHas('groups', fn ($q) => $q->where('grup_id', $peserta->user_grup_id))
            ->whereHas('tesUsers', fn ($q) => $q
                ->where('tesuser_user_id', $peserta->user_id)
                ->where('tesuser_status', TesUser::STATUS_SELESAI))
            ->orderByDesc('tes_end_time')
            ->get();

        return view('peserta.dashboard', compact('peserta', 'tes', 'attempts', 'ringkasan', 'rankingTes'));
    }

    public function show(Tes $tes)
    {
        $peserta = auth('peserta')->user();
        $this->pastikanAkses($tes, $peserta);

        $attempt = TesUser::where('tesuser_tes_id', $tes->tes_id)
            ->where('tesuser_user_id', $peserta->user_id)
            ->latest('tesuser_id')
            ->first();

        if ($attempt && $attempt->is_berjalan) {
            return redirect()->route('peserta.ujian.kerjakan', $tes);
        }

        $pembayaran = Pembayaran::where('pembayaran_user_id', $peserta->user_id)
            ->where('pembayaran_tes_id', $tes->tes_id)
            ->first();

        $perluPembayaran = $this->perluPembayaran($tes, $peserta);

        $attendance = app(AttendanceService::class);
        $examSession = $attendance->examSessionFor($tes, false);

        $attendanceRecord = $examSession
            ? $attendance->recordFor($examSession, (int) $peserta->user_id)
            : null;

        $attendanceLocation = $tes->attendanceLocation ?: $examSession?->location;
        $perluAbsensi = $tes->tes_absensi_wajib && ! ($attendanceRecord?->isPresent());

        return view('peserta.tes', compact(
            'tes',
            'attempt',
            'peserta',
            'pembayaran',
            'perluPembayaran',
            'examSession',
            'attendanceRecord',
            'attendanceLocation',
            'perluAbsensi'
        ));
    }

    public function mulai(Request $request, Tes $tes)
    {
        $peserta = auth('peserta')->user();
        $this->pastikanAkses($tes, $peserta);

        if (! $tes->is_open) {
            return back()->with('error', 'Waktu pelaksanaan tes belum dimulai atau sudah berakhir.');
        }

        if (! app(AttendanceService::class)->hasValidExamAttendance($tes, $peserta)) {
            throw ValidationException::withMessages([
                'absensi' => 'Anda belum melakukan check-in absensi pra-ujian yang valid. Silakan check-in lokasi terlebih dahulu.',
            ]);
        }

        if ($this->perluPembayaran($tes, $peserta)) {
            throw ValidationException::withMessages([
                'pembayaran' => 'Pembayaran biaya ujian belum diverifikasi. Silakan hubungi orang tua atau admin sekolah.',
            ]);
        }

        $existing = TesUser::where('tesuser_tes_id', $tes->tes_id)
            ->where('tesuser_user_id', $peserta->user_id)
            ->latest('tesuser_id')
            ->first();

        if ($existing) {
            if ($existing->is_berjalan) {
                return redirect()->route('peserta.ujian.kerjakan', $tes);
            }

            return redirect()->route('peserta.ujian.hasil', $tes);
        }

        $token = null;

        if ($tes->tes_token) {
            $request->validate(['token' => ['required', 'string']]);
            $token = TesToken::validFor($request->token, $tes->tes_id);

            if (! $token) {
                throw ValidationException::withMessages(['token' => 'Token tidak valid atau sudah kedaluwarsa.']);
            }
        }

        DB::transaction(function () use ($tes, $peserta, $token) {
            $attempt = TesUser::create([
                'tesuser_tes_id' => $tes->tes_id,
                'tesuser_user_id' => $peserta->user_id,
                'tesuser_status' => TesUser::STATUS_BERJALAN,
                'tesuser_creation_time' => now(),
                'tesuser_token' => $token?->token_isi,
            ]);

            Absensi::updateOrCreate(
                [
                    'absensi_user_id' => $peserta->user_id,
                    'absensi_tes_id' => $tes->tes_id,
                ],
                [
                    'absensi_tanggal' => now()->toDateString(),
                    'absensi_status' => Absensi::HADIR,
                    'absensi_sumber' => Absensi::SUMBER_UJIAN,
                ]
            );

            $nomor = 1;
            $dipakai = [];

            foreach ($tes->topikSets()->orderBy('tset_id')->get() as $set) {
                $soal = Soal::where('soal_topik_id', $set->tset_topik_id)
                    ->where('soal_aktif', 1)
                    ->where('soal_difficulty', $set->tset_difficulty)
                    ->when((int) $set->tset_tipe !== 0, fn ($q) => $q->where('soal_tipe', $set->tset_tipe))
                    ->whereNotIn('soal_id', $dipakai)
                    ->when((int) $set->tset_acak_soal === 1, fn ($q) => $q->inRandomOrder(), fn ($q) => $q->orderBy('soal_id'))
                    ->limit((int) $set->tset_jumlah)
                    ->get();

                if ($soal->count() < (int) $set->tset_jumlah) {
                    throw ValidationException::withMessages([
                        'token' => 'Soal pada tes ini belum lengkap. Silakan hubungi operator.',
                    ]);
                }

                foreach ($soal as $item) {
                    $dipakai[] = $item->soal_id;

                    $tesSoal = TesSoal::create([
                        'tessoal_tesuser_id' => $attempt->tesuser_id,
                        'tessoal_user_ip' => request()->ip(),
                        'tessoal_soal_id' => $item->soal_id,
                        'tessoal_creation_time' => now(),
                        'tessoal_order' => $nomor++,
                    ]);

                    $jawabans = $item->jawabans()->get();

                    if ((int) $set->tset_acak_jawaban === 1) {
                        $jawabans = $jawabans->shuffle();
                    }

                    foreach ($jawabans->values() as $index => $jawaban) {
                        $tesSoal->pilihan()->create([
                            'soaljawaban_jawaban_id' => $jawaban->jawaban_id,
                            'soaljawaban_selected' => -1,
                            'soaljawaban_order' => $index + 1,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('peserta.ujian.kerjakan', $tes)->with('success', 'Tes dimulai. Semoga sukses!');
    }

    private function perluPembayaran(Tes $tes, $peserta): bool
    {
        if ((float) $tes->tes_biaya <= 0) {
            return false;
        }

        if (Konfigurasi::get('cbt_gate_pembayaran', 'tidak') !== 'ya') {
            return false;
        }

        return ! Pembayaran::where('pembayaran_user_id', $peserta->user_id)
            ->where('pembayaran_tes_id', $tes->tes_id)
            ->where('pembayaran_status', Pembayaran::DIVERIFIKASI)
            ->exists();
    }

    private function pastikanAkses(Tes $tes, $peserta): void
    {
        $punyaAkses = $tes->groups()->where('grup_id', $peserta->user_grup_id)->exists();

        abort_unless($punyaAkses, 403, 'Anda tidak terdaftar pada tes ini.');
    }
}
