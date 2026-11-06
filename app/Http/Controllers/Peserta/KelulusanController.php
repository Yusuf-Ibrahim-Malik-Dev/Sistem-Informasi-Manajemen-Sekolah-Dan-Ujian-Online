<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Konfigurasi;
use App\Support\Kelulusan;
use Illuminate\Http\Request;

class KelulusanController extends Controller
{
    public function index()
    {
        $peserta = auth('peserta')->user();

        return view('peserta.kelulusan.index', [
            'peserta' => $peserta,
            'daftarKelulusan' => Kelulusan::semua($peserta),
        ]);
    }

    public function cetak(Request $request)
    {
        $peserta = auth('peserta')->user();
        $kelulusan = Kelulusan::untuk($peserta, $request->query('kelas'));

        if (! $kelulusan['lulus']) {
            return redirect()
                ->route('peserta.kelulusan.index')
                ->with('popup_error', 'Surat kelulusan belum dapat dicetak. Pastikan kelas tersebut adalah kelas akhir dan seluruh nilai raport sudah lengkap.');
        }

        return view('kelulusan-cetak', [
            'peserta' => $peserta,
            'kelulusan' => $kelulusan,
            'namaCbt' => Konfigurasi::get('cbt_nama', 'CBT Online'),
            'alamat' => Konfigurasi::get('cbt_alamat', ''),
            'kepalaSekolah' => Konfigurasi::get('cbt_kepala_sekolah', 'Kepala Sekolah'),
            'nipKepalaSekolah' => Konfigurasi::get('cbt_nip_kepala_sekolah', '-'),
            'tahunAjaran' => Konfigurasi::get('cbt_tahun_ajaran', ''),
            'kembali' => route('peserta.kelulusan.index'),
        ]);
    }
}
