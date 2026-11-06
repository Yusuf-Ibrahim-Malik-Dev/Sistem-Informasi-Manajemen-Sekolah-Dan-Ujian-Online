@extends('layouts.admin')

@section('title', 'Pengaturan Aplikasi')
@section('heading', 'Pengaturan Aplikasi')
@section('eyebrow', 'Sistem')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8" data-aos="fade-up">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Identitas Aplikasi</h3>
                        <p class="section-sub">Informasi ini tampil pada halaman login dan dashboard peserta.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.pengaturan.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Aplikasi</label>
                            <input type="text" name="cbt_nama" class="form-control" value="{{ old('cbt_nama', $konfigurasi['cbt_nama']) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="cbt_keterangan" class="form-control" value="{{ old('cbt_keterangan', $konfigurasi['cbt_keterangan']) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Informasi Dashboard Peserta</label>
                            <textarea name="cbt_informasi" class="form-control" rows="5" placeholder="HTML diperbolehkan">{{ old('cbt_informasi', $konfigurasi['cbt_informasi']) }}</textarea>
                            <div class="form-text">Ditampilkan sebagai pengumuman pada dashboard peserta.</div>
                        </div>

                        <div class="col-12 mt-4">
                            <hr>
                            <h4 class="fw-bold mb-1" style="font-size:.98rem">Identitas Sekolah & Kelulusan</h4>
                            <p class="section-sub mb-0">Digunakan pada surat keterangan lulus peserta kelas akhir.</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat Sekolah</label>
                            <input type="text" name="cbt_alamat" class="form-control" value="{{ old('cbt_alamat', $konfigurasi['cbt_alamat']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Kepala Sekolah</label>
                            <input type="text" name="cbt_kepala_sekolah" class="form-control" value="{{ old('cbt_kepala_sekolah', $konfigurasi['cbt_kepala_sekolah']) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIP Kepala Sekolah</label>
                            <input type="text" name="cbt_nip_kepala_sekolah" class="form-control" value="{{ old('cbt_nip_kepala_sekolah', $konfigurasi['cbt_nip_kepala_sekolah']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Ajaran</label>
                            <input type="text" name="cbt_tahun_ajaran" class="form-control" value="{{ old('cbt_tahun_ajaran', $konfigurasi['cbt_tahun_ajaran']) }}" placeholder="2025/2026" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nilai Minimal Kelulusan</label>
                            <input type="number" name="cbt_nilai_kelulusan" class="form-control" value="{{ old('cbt_nilai_kelulusan', $konfigurasi['cbt_nilai_kelulusan']) }}" min="0" max="100" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link Login Operator</label>
                            <select name="link_login_operator" class="form-select">
                                <option value="ya" @selected($konfigurasi['link_login_operator'] === 'ya')>Tampilkan</option>
                                <option value="tidak" @selected($konfigurasi['link_login_operator'] === 'tidak')>Sembunyikan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lock Browser Mobile (Xambro)</label>
                            <select name="cbt_mobile_lock_xambro" class="form-select">
                                <option value="tidak" @selected($konfigurasi['cbt_mobile_lock_xambro'] === 'tidak')>Tidak</option>
                                <option value="ya" @selected($konfigurasi['cbt_mobile_lock_xambro'] === 'ya')>Ya</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Wajib Pembayaran Sebelum Ujian</label>
                            <select name="cbt_gate_pembayaran" class="form-select">
                                <option value="tidak" @selected($konfigurasi['cbt_gate_pembayaran'] === 'tidak')>Tidak (bisa mulai kapan saja)</option>
                                <option value="ya" @selected($konfigurasi['cbt_gate_pembayaran'] === 'ya')>Ya (harus diverifikasi)</option>
                            </select>
                            <div class="form-text">Jika diaktifkan, peserta hanya bisa memulai tes berbayar setelah pembayaran diverifikasi admin.</div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-gradient"><i class="bi bi-save me-1"></i> Simpan Pengaturan</button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">Batal</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Ganti Password</h3>
                        <p class="section-sub">Perbarui kata sandi akun Anda.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.password') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="password_baru_confirmation" class="form-control" required>
                    </div>
                    <button class="btn btn-soft w-100"><i class="bi bi-shield-lock me-1"></i> Perbarui Password</button>
                </form>
            </div>
        </div>
    </div>
@endsection
