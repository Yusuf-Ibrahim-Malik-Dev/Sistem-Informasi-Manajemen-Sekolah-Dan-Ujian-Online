<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tes;
use App\Models\TesSoal;
use App\Models\TesUser;
use App\Models\UserGroup;
use App\Support\HasilUjian;
use Illuminate\Http\Request;

class HasilController extends Controller
{
    public function index(Request $request)
    {
        $tes = Tes::orderBy('tes_nama')->get();
        $grups = UserGroup::orderBy('grup_nama')->get();

        $hasil = TesUser::with(['tes', 'peserta.grup'])
            ->withSum('soals as total_nilai', 'tessoal_nilai')
            ->withCount('soals')
            ->when($request->filled('tes'), fn ($q) => $q->where('tesuser_tes_id', $request->tes))
            ->when($request->filled('grup'), function ($q) use ($request) {
                $q->whereHas('peserta', fn ($sub) => $sub->where('user_grup_id', $request->grup));
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->whereHas('peserta', function ($sub) use ($request) {
                    $sub->where('user_firstname', 'like', '%' . $request->q . '%')
                        ->orWhere('user_name', 'like', '%' . $request->q . '%');
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('tesuser_status', $request->status))
            ->orderByDesc('tesuser_creation_time')
            ->paginate(15)
            ->withQueryString();

        return view('admin.hasil.index', compact('hasil', 'tes', 'grups'));
    }

    public function detail(TesUser $tesUser)
    {
        $tesUser->load(['tes', 'peserta.grup', 'soals.soal.jawabans', 'soals.pilihan.jawaban']);

        $ringkasan = HasilUjian::ringkas($tesUser);

        return view('admin.hasil.detail', [
            'tesUser' => $tesUser,
            'benar' => $ringkasan['benar'],
            'salah' => $ringkasan['salah'],
            'nilai' => $ringkasan['nilai'],
        ]);
    }

    public function cetak(TesUser $tesUser)
    {
        $tesUser->load(['tes', 'peserta.grup']);
        $ringkasan = HasilUjian::ringkas($tesUser);

        return view('hasil-cetak', [
            'tes' => $tesUser->tes,
            'attempt' => $tesUser,
            'peserta' => $tesUser->peserta,
            'benar' => $ringkasan['benar'],
            'salah' => $ringkasan['salah'],
            'nilai' => $ringkasan['nilai'],
            'namaGuru' => HasilUjian::guru($tesUser),
            'kembali' => route('admin.hasil.detail', $tesUser),
            'namaCbt' => \App\Models\Konfigurasi::get('cbt_nama', 'CBT Online'),
        ]);
    }

    public function evaluasi(Request $request, TesSoal $tesSoal)
    {
        $data = $request->validate([
            'tessoal_nilai' => ['required', 'numeric', 'min:0'],
            'tessoal_comment' => ['nullable', 'string'],
        ]);

        $tesSoal->update($data);

        return back()->with('success', 'Nilai dan catatan berhasil disimpan.');
    }

    public function hentikan(TesUser $tesUser)
    {
        if ($tesUser->is_berjalan) {
            $tesUser->finalisasi();

            return back()->with('success', 'Ujian peserta berhasil dihentikan.');
        }

        return back()->with('error', 'Ujian tersebut sudah selesai.');
    }
}
