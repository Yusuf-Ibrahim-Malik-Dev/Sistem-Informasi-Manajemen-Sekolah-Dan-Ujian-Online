<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Konfigurasi;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('admin.pengaturan.index', [
            'konfigurasi' => [
                'cbt_nama' => Konfigurasi::get('cbt_nama', 'CBT Online'),
                'cbt_keterangan' => Konfigurasi::get('cbt_keterangan', 'Ujian Online Berbasis Komputer'),
                'cbt_informasi' => Konfigurasi::get('cbt_informasi', ''),
                'link_login_operator' => Konfigurasi::get('link_login_operator', 'ya'),
                'cbt_mobile_lock_xambro' => Konfigurasi::get('cbt_mobile_lock_xambro', 'tidak'),
                'cbt_alamat' => Konfigurasi::get('cbt_alamat', ''),
                'cbt_kepala_sekolah' => Konfigurasi::get('cbt_kepala_sekolah', 'Kepala Sekolah'),
                'cbt_nip_kepala_sekolah' => Konfigurasi::get('cbt_nip_kepala_sekolah', '-'),
                'cbt_tahun_ajaran' => Konfigurasi::get('cbt_tahun_ajaran', ''),
                'cbt_nilai_kelulusan' => Konfigurasi::get('cbt_nilai_kelulusan', '70'),
                'cbt_gate_pembayaran' => Konfigurasi::get('cbt_gate_pembayaran', 'tidak'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'cbt_nama' => ['required', 'string', 'max:100'],
            'cbt_keterangan' => ['required', 'string', 'max:100'],
            'cbt_informasi' => ['nullable', 'string'],
            'link_login_operator' => ['required', 'in:ya,tidak'],
            'cbt_mobile_lock_xambro' => ['required', 'in:ya,tidak'],
            'cbt_alamat' => ['nullable', 'string', 'max:255'],
            'cbt_kepala_sekolah' => ['required', 'string', 'max:100'],
            'cbt_nip_kepala_sekolah' => ['nullable', 'string', 'max:50'],
            'cbt_tahun_ajaran' => ['required', 'string', 'max:20'],
            'cbt_nilai_kelulusan' => ['required', 'numeric', 'min:0', 'max:100'],
            'cbt_gate_pembayaran' => ['required', 'in:ya,tidak'],
        ]);

        foreach ($data as $kode => $isi) {
            Konfigurasi::set($kode, (string) $isi);
        }

        return back()->with('success', 'Pengaturan aplikasi berhasil disimpan.');
    }
}
