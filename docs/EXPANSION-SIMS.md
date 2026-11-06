# Pengembangan SIMS — Hak Akses dan Personalisasi

## Yang ditambahkan pada paket ini

1. **Override menu per akun** melalui tabel `user_menu_user_access`.
   - Hak akses level/role tetap menjadi default.
   - Jika override personal diaktifkan, daftar menu akun menjadi sumber utama.
   - Pengaturan tersedia pada form Sistem → User Administrator.
   - Administrator utama (`level=admin`) tetap memiliki akses penuh.
2. **Preferensi tampilan per akun** melalui tabel `user_preferences`.
   - Light / dark mode.
   - Warna utama dan warna aksen dalam format HEX.
   - Preferensi sidebar ringkas.
   - Halaman: `/admin/preferensi-tampilan`.
3. Penambahan role awal: kasir seluruh pembayaran, petugas SPP, operator sekolah, perpustakaan, BK, dan UKS.
4. Hak akses kasir mencakup menu pembayaran ujian dan SPP. Petugas SPP hanya memperoleh menu SPP.

## Prinsip desain hak akses

- Role menentukan hak akses default.
- Override pengguna menentukan menu yang terlihat untuk akun tertentu.
- Penugasan guru (mata pelajaran, kelas, rombel, dan tahun ajaran) harus menjadi lapisan otorisasi data tersendiri.
- Menu yang disembunyikan **bukan** pengganti pemeriksaan otorisasi pada controller/query. Setiap modul wajib memfilter data berdasarkan sekolah, tahun ajaran, kelas, dan penugasan pengguna.
- Akun peserta dan orang tua tetap menggunakan guard dan area masing-masing; jangan berikan akses panel admin kepada keduanya.

## Modul SIMS yang perlu dikembangkan berikutnya

- Akademik: tahun ajaran, semester, kalender akademik, kurikulum, rombel, kenaikan kelas.
- SDM: data guru/pegawai, jabatan, kehadiran pegawai, cuti, penugasan.
- Pembelajaran: mata pelajaran, jadwal, jurnal mengajar, materi, tugas, penilaian.
- Kesiswaan: biodata, mutasi, prestasi, pelanggaran, konseling, ekstrakurikuler.
- Kehadiran: presensi siswa/guru, izin, sakit, rekap dan notifikasi.
- Keuangan: seluruh jenis tagihan, kas masuk/keluar, anggaran, jurnal, kuitansi, rekonsiliasi.
- Penilaian: bank soal, CBT, nilai harian, PTS/PAS, leger, rapor.
- Layanan: perpustakaan, UKS, inventaris, surat-menyurat, pengumuman, PPDB, alumni.
- Portal orang tua/siswa: informasi anak, jadwal, absensi, tugas, nilai, tagihan, pembayaran, komunikasi.

## Instalasi perubahan

Jalankan migrasi:

```bash
php artisan migrate
```

Jika memasang dari source bersih:

```bash
composer install
cp .env.example .env
php artisan key:generate
npm ci
npm run build
```

Jangan membagikan file `.env`. Isi kredensial database dan konfigurasi lokal pada mesin masing-masing.
