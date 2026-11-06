# SIMS CBT Laravel

Sistem Informasi Sekolah (SIMS) + Computer Based Test (CBT) berbasis
**Laravel 10**, **PHP 8.1+**, **MySQL 8+**, **Blade + Alpine.js +
Bootstrap 5 + Chart.js**. Aplikasi ini mendukung banyak yayasan dan
banyak sekolah dalam satu database (multi-foundation / multi-school),
dengan tiga portal terpisah: administrator/staff, peserta, dan orang
tua.

Dokumen ini menjelaskan kondisi aplikasi yang terdokumentasi saat ini
sekaligus blueprint target pengembangan SIMS Enterprise. Bagian yang
berstatus **Sudah ada** merujuk pada dokumentasi fitur saat ini; bagian
**Rancangan / Belum diverifikasi** adalah kebutuhan target dan tidak
boleh dianggap sudah tersedia sebelum implementasi serta pengujian
selesai.

------------------------------------------------------------------------

## Daftar Isi

1.  [Ringkasan Fitur](#1-ringkasan-fitur)
2.  [Teknologi](#2-teknologi)
3.  [Struktur Aplikasi](#3-struktur-aplikasi)
4.  [Arsitektur dan Konsep Inti](#4-arsitektur-dan-konsep-inti)
5.  [Skema Database](#5-skema-database)
6.  [Menu dan Fungsinya](#6-menu-dan-fungsinya)
7.  [Matriks Role dan Hak Akses](#7-matriks-role-dan-hak-akses)
8.  [Instalasi dan Menjalankan
    Aplikasi](#8-instalasi-dan-menjalankan-aplikasi)
9.  [Akun Demo](#9-akun-demo)
10. [Cara Menggunakan Aplikasi](#10-cara-menggunakan-aplikasi)
11. [Flow / Alur Sistem](#11-flow--alur-sistem)
12. [Testing dan Pemeriksaan](#12-testing-dan-pemeriksaan)
13. [Keamanan](#13-keamanan)
14. [Batasan dan Rencana
    Pengembangan](#14-batasan-dan-rencana-pengembangan)
15. [Dokumen Terkait](#15-dokumen-terkait)
16. [Visi Produk dan Ruang Lingkup
    Enterprise](#16-visi-produk-dan-ruang-lingkup-enterprise)
17. [Gap Analysis dan Risiko Sistem](#17-gap-analysis-dan-risiko-sistem)
18. [Arsitektur Menu Target](#18-arsitektur-menu-target)
19. [Business Process dan Workflow](#19-business-process-dan-workflow)
20. [Role, Permission, dan Data
    Scope](#20-role-permission-dan-data-scope)
21. [Target Domain dan Database](#21-target-domain-dan-database)
22. [Arsitektur Teknis dan Standar
    Implementasi](#22-arsitektur-teknis-dan-standar-implementasi)
23. [Non-Functional Requirements](#23-non-functional-requirements)
24. [Testing, Acceptance Criteria, dan Production
    Readiness](#24-testing-acceptance-criteria-dan-production-readiness)
25. [Roadmap Implementasi](#25-roadmap-implementasi)
26. [Keputusan Arsitektur yang Harus
    Ditetapkan](#26-keputusan-arsitektur-yang-harus-ditetapkan)

------------------------------------------------------------------------

## 1. Ringkasan Fitur

### Portal Administrator / Staff

-   Login terpisah untuk administrator dan staff sekolah.
-   Multi-yayasan dan multi-sekolah dengan konteks sekolah aktif.
-   Manajemen yayasan, sekolah, tahun ajaran, dan semester.
-   Bank soal: modul/mata pelajaran, topik/grup soal, soal (pilihan
    ganda, essay, jawaban singkat), import CSV/XLSX, upload media soal.
-   Manajemen peserta, group/kelas, akun orang tua, absensi harian, dan
    absensi pra-ujian berbasis geofencing GPS.
-   Manajemen ujian CBT: komposisi soal per topik, bobot nilai, durasi,
    token, daftar peserta per group.
-   Monitoring ujian berjalan, hasil, evaluasi essay, ranking, dan cetak
    hasil.
-   Raport per mata pelajaran, per jenjang, per tingkat kelas.
-   Kelulusan dan cetak Surat Keterangan Lulus (SKL).
-   Laporan rekap nilai dan analisis butir soal (tingkat kesukaran &
    daya pembeda).
-   Verifikasi pembayaran biaya ujian CBT dengan gate opsional sebelum
    peserta memulai ujian.
-   Modul SPP/keuangan: kategori biaya, tarif, tagihan siswa, item
    tagihan, metode pembayaran, rekening bank/ATM tujuan transfer,
    pencatatan pembayaran, alokasi, cicilan, verifikasi/penolakan,
    pembatalan, dan kwitansi.
-   Portal orang tua untuk melihat tagihan SPP dan biaya ujian, melihat
    rekening bank/ATM sekolah yang ditampilkan admin, mengunggah bukti
    transfer, dan mengunduh kwitansi.
-   Pengaturan aplikasi, user administrator, level, hak akses per level,
    override menu per akun, dan preferensi tampilan
    (tema/warna/sidebar).

### Portal Peserta

-   Login peserta.
-   Melihat ujian yang tersedia sesuai group/kelas.
-   Mengerjakan ujian dengan timer, navigasi soal, simpan jawaban, dan
    tandai ragu.
-   Melihat hasil ujian, ranking (jika diaktifkan), raport, prestasi,
    dan kelulusan.
-   Mencetak hasil ujian, ranking, dan SKL (dengan syarat tertentu).

### Portal Orang Tua

-   Login orang tua dengan relasi ke banyak anak.
-   Melihat dashboard anak, absensi anak, dan tagihan/biaya ujian CBT.
-   Mengunggah bukti pembayaran biaya ujian.
-   Melihat status verifikasi pembayaran (menunggu / diverifikasi /
    ditolak).

------------------------------------------------------------------------

## 2. Teknologi

  -----------------------------------------------------------------------
  Komponen                            Keterangan
  ----------------------------------- -----------------------------------
  Backend                             Laravel 10, PHP 8.1+

  Database                            MySQL 8+ (`utf8mb4`)

  Frontend                            Blade, Alpine.js 3, Bootstrap 5,
                                      Bootstrap Icons, Chart.js, AOS

  Build tool                          Vite 4

  Spreadsheet                         PhpSpreadsheet 1.29 (import/export
                                      soal)

  Autentikasi                         Session guard: `web`, `peserta`,
                                      `orangtua`

  Testing                             PHPUnit 10
  -----------------------------------------------------------------------

Konfigurasi default aplikasi:

-   `APP_NAME="CBT Laravel"`
-   `APP_URL=http://localhost`
-   `DB_CONNECTION=mysql`, `DB_DATABASE=cbt_laravel`
-   Locale `id`, timezone `Asia/Jakarta` (lihat `config/app.php`)

------------------------------------------------------------------------

## 3. Struktur Aplikasi

    cbt-laravel/
    +-- app/
    |   +-- Console/Kernel.php
    |   +-- Exceptions/Handler.php
    |   +-- Http/
    |   |   +-- Controllers/
    |   |   |   +-- Admin/              # Controller panel administrator/staff
    |   |   |   |   +-- AbsensiController.php
    |   |   |   |   +-- AuthController.php
    |   |   |   |   +-- DashboardController.php
    |   |   |   |   +-- FoundationController.php
    |   |   |   |   +-- GrupController.php
    |   |   |   |   +-- GuruController.php
    |   |   |   |   +-- HasilController.php
    |   |   |   |   +-- KelulusanController.php
    |   |   |   |   +-- LaporanController.php
    |   |   |   |   +-- LevelController.php
    |   |   |   |   +-- ModulController.php
    |   |   |   |   +-- OrangTuaController.php
    |   |   |   |   +-- PembayaranController.php
    |   |   |   |   +-- PengaturanController.php
    |   |   |   |   +-- PesertaController.php
    |   |   |   |   +-- PreferenceController.php
    |   |   |   |   +-- SchoolController.php
    |   |   |   |   +-- SoalController.php
    |   |   |   |   +-- SppController.php
    |   |   |   |   +-- TesController.php
    |   |   |   |   +-- TokenController.php
    |   |   |   |   +-- TopikController.php
    |   |   |   |   +-- UserController.php
    |   |   |   +-- OrangTua/          # Controller portal orang tua
    |   |   |   +-- Peserta/           # Controller portal peserta
    |   |   +-- Middleware/
    |   |   |   +-- EnsureAdminAccess.php     # Cek akses menu admin
    |   |   |   +-- ResolveSchoolContext.php  # Resolusi yayasan/sekolah aktif
    |   |   |   +-- ...
    |   +-- Models/                    # Model Eloquent (tabel legacy cbt_*, core_*, fin_*)
    |   +-- Providers/
    |   +-- Support/
    |       +-- HasilUjian.php         # Ringkasan nilai, ranking, status ujian
    |       +-- HtmlText.php           # Sanitasi teks soal
    |       +-- Kelulusan.php          # Logika kelulusan dan SKL
    |       +-- LegacyPassword.php     # Verifikasi password lama (bcrypt/plain/sha1)
    |       +-- PrestasiKelas.php      # Statistik nilai per kelas
    |       +-- SchoolContext.php      # Scope yayasan/sekolah
    +-- bootstrap/
    +-- config/
    +-- database/
    |   +-- migrations/                # Migration Laravel (sebagian tabel legacy + fin_*)
    |   +-- schema/cbt_laravel_schema.sql  # Baseline schema-only legacy
    |   +-- seeders/
    |       +-- CbtSeeder.php          # Data demo lengkap
    |       +-- DatabaseSeeder.php
    +-- docs/
    |   +-- EXPANSION-SIMS.md
    |   +-- IMPLEMENTASI_ENTERPRISE.md
    +-- public/
    |   +-- build/                     # Hasil build Vite
    |   +-- uploads/                   # Upload media soal (dibuat saat upload)
    |   +-- storage/                   # Symlink storage publik
    +-- resources/
    |   +-- css/app.css
    |   +-- js/app.js
    |   +-- views/
    |       +-- admin/                 # View panel admin per modul
    |       +-- auth/                  # Login admin/peserta/orang tua
    |       +-- layouts/               # Layout admin, peserta, orang tua, exam
    |       +-- orangtua/              # View portal orang tua
    |       +-- peserta/               # View portal peserta
    |       +-- partials/              # Alert, grafik, url-mask
    |       +-- hasil-cetak.blade.php
    |       +-- kelulusan-cetak.blade.php
    |       +-- ranking-cetak.blade.php
    +-- routes/
    |   +-- web.php                    # Seluruh route web
    |   +-- api.php
    |   +-- channels.php
    |   +-- console.php
    +-- storage/
    +-- tests/
    |   +-- Feature/                   # Smoke test, audit, guru, orang tua, raport, SPP, import
    |   +-- Unit/
    +-- artisan
    +-- composer.json
    +-- package.json
    +-- cbt_laravel.sql                # Dump MySQL lengkap (berisi data; jangan dipakai sembarangan)

------------------------------------------------------------------------

## 4. Arsitektur dan Konsep Inti

### 4.1 Tiga Guard Autentikasi

Konfigurasi di `config/auth.php`:

  Guard        Model                   Tabel             Prefix URL
  ------------ ----------------------- ----------------- -------------
  `web`        `App\Models\Admin`      `user`            `/admin`
  `peserta`    `App\Models\Peserta`    `cbt_user`        `/peserta`
  `orangtua`   `App\Models\OrangTua`   `cbt_orang_tua`   `/orangtua`

Root `/` akan mengarahkan pengguna sesuai guard yang sedang login. Jika
belum login, diarahkan ke login peserta.

### 4.2 Multi-Tenant: Yayasan dan Sekolah

Tenant direpresentasikan oleh:

-   `core_foundations` (yayasan)
-   `core_schools` (sekolah)
-   `core_academic_years` (tahun ajaran)
-   `core_semesters` (semester)

Akun administrator memiliki `scope_type`:

  Scope          Arti            Akses Sekolah
  -------------- --------------- -----------------------------------
  `system`       Super admin     Semua yayasan/sekolah
  `foundation`   Admin yayasan   Semua sekolah di yayasan tertentu
  `school`       Admin sekolah   Satu sekolah saja

`SchoolContext` (`app/Support/SchoolContext.php`) menyimpan konteks
aktif:

-   Jika akun scope sekolah, konteks dikunci ke sekolahnya.
-   Jika scope yayasan/system, sekolah aktif bisa dipilih lewat dropdown
    di topbar (`POST /admin/sekolah/switch`) dan disimpan di session
    `active_school_id`.
-   Jika tidak ada sekolah aktif, query difilter berdasarkan
    `foundation_id`.
-   Jika tidak ada fondasi/sekolah, super admin melihat semua data.

Kolom tenant ditambahkan ke tabel utama: `user`, `cbt_user_grup`,
`cbt_user`, `cbt_tes`, `cbt_absensi`, `cbt_pembayaran`, serta seluruh
tabel `fin_*`.

### 4.3 Hak Akses Menu

Hak akses memiliki dua lapisan:

1.  **Level/role default** melalui `user_akses` (`level`, `kode_menu`,
    `add`, `edit`, `can_delete`, `can_approve`).
2.  **Override per akun** melalui `user_menu_user_access` (`is_allowed`,
    `can_add`, `can_edit`, `can_delete`, `can_approve`).

Aturan `Admin::hasAccess()` dan `Admin::canMenuAction()`:

-   Level `admin` selalu memiliki akses penuh.
-   Jika ada override personal untuk menu tersebut, override selalu
    menang atas hak level.
-   Jika `is_allowed` override bernilai `false`, seluruh aksi ditolak.
-   Jika tidak ada override, fallback ke hak akses level.
-   Aksi yang didukung: `view`, `add/create`, `edit/update`, `delete`,
    `approve/verify`.
-   Kolom `can_delete`/`can_approve` yang masih `null` pada data lama
    otomatis fallback ke nilai `edit` agar kompatibel.

Middleware `admin.access:{kode_menu},{action}` menolak aksi yang tidak
diizinkan, misalnya `admin.access:spp-pembayaran,approve`. Menu sidebar
difilter dengan aturan `view` yang sama. Form user dan level menampilkan
checkbox Lihat/Tambah/Ubah/Hapus/Setujui per menu.

> Catatan: penyembunyian menu bukan pengganti otorisasi data. Setiap
> controller tetap memfilter data berdasarkan tenant dan kepemilikan
> topik.

### 4.4 Kepemilikan Topik dan Soal

-   Setiap topik memiliki `topik_guru_id`.
-   `Topik::dapatDikelolaOleh($admin)` hanya mengembalikan `true` jika
    `topik_guru_id` sama dengan ID akun yang login.
-   Artinya, **hanya pemilik topik** yang dapat mengubah/menghapus topik
    dan soal di dalamnya. Administrator utama pun mengikuti aturan ini
    saat mengedit topik yang bukan miliknya.
-   Pembuatan topik baru bisa menunjuk guru pemilik melalui form.
-   Soal menyimpan `soal_creator_id`.

### 4.5 Password Legacy

`App\Support\LegacyPassword` mendukung verifikasi tiga format:

1.  Hash bcrypt (`Hash::check`).
2.  Plain text.
3.  SHA1 lama.

Setelah login sukses, password lama otomatis di-upgrade ke bcrypt
(`Hash::needsRehash`).

### 4.6 Konfigurasi Aplikasi

Tersimpan di `cbt_konfigurasi` dan di-cache permanen (`cbt_konfigurasi`
cache key). Nilai penting:

-   `cbt_nama`, `cbt_keterangan`, `cbt_informasi`
-   `link_login_operator` (`ya`/`tidak`)
-   `cbt_mobile_lock_xambro`
-   `cbt_alamat`, `cbt_kepala_sekolah`, `cbt_nip_kepala_sekolah`
-   `cbt_tahun_ajaran`
-   `cbt_nilai_kelulusan` (default 70, dipakai sebagai KKM kelulusan)

------------------------------------------------------------------------

## 5. Skema Database

### 5.1 Identitas, Auth, dan Hak Akses

  -------------------------------------------------------------------------
  Tabel                     Fungsi                  Kolom Penting
  ------------------------- ----------------------- -----------------------
  `user`                    Akun                    `username`, `password`,
                            administrator/staff     `nama`, `level`,
                                                    `scope_type`,
                                                    `foundation_id`,
                                                    `school_id`,
                                                    `remember_token`

  `user_level`              Master level/role       `level`, `keterangan`

  `user_akses`              Hak akses default per   `level`, `kode_menu`,
                            level                   `add`, `edit`,
                                                    `can_delete`,
                                                    `can_approve`

  `user_menu`               Definisi menu sidebar   `tipe` (0 parent, 1
                                                    child), `parent`,
                                                    `kode_menu`,
                                                    `nama_menu`, `url`
                                                    (nama route), `icon`,
                                                    `urutan`

  `user_menu_user_access`   Override menu per akun  `user_id`, `kode_menu`,
                                                    `is_allowed`,
                                                    `can_add`, `can_edit`,
                                                    `can_delete`,
                                                    `can_approve`

  `user_preferences`        Preferensi tampilan     `user_id`,
                                                    `theme_mode`,
                                                    `primary_color`,
                                                    `accent_color`,
                                                    `sidebar_collapsed`

  `user_log`                Log aktivitas           `username`, `log`, `ts`
  -------------------------------------------------------------------------

### 5.2 Yayasan, Sekolah, dan Tahun Ajaran

  -----------------------------------------------------------------------
  Tabel                               Fungsi
  ----------------------------------- -----------------------------------
  `core_foundations`                  Data yayasan (`foundation_code`,
                                      `foundation_name`, `legal_name`,
                                      alamat, kontak, `is_active`)

  `core_school_levels`                Jenjang sekolah (`SD`, `SMP`,
                                      `SMA`, `SMK`, `MI`, `MTs`, `MA`)

  `core_schools`                      Data sekolah (relasi yayasan dan
                                      jenjang, `school_code`, `npsn`,
                                      `school_name`, `is_active`)

  `core_academic_years`               Tahun ajaran per sekolah
                                      (`year_name`, `start_date`,
                                      `end_date`, `is_active`)

  `core_semesters`                    Semester per tahun ajaran
                                      (`semester_number`, tanggal,
                                      `is_active`)
  -----------------------------------------------------------------------

### 5.3 Bank Soal

  --------------------------------------------------------------------------
  Tabel                   Fungsi                  Kolom Penting
  ----------------------- ----------------------- --------------------------
  `cbt_konfigurasi`       Pengaturan aplikasi     `konfigurasi_kode`,
                                                  `konfigurasi_isi`,
                                                  `konfigurasi_keterangan`

  `cbt_modul`             Modul/mata pelajaran    `modul_nama`,
                                                  `modul_aktif`

  `cbt_topik`             Topik/grup soal         `topik_modul_id`,
                                                  `topik_nama`,
                                                  `topik_tipe_ujian`
                                                  (`um`/`uts`/`uas`),
                                                  `topik_guru_id`,
                                                  `topik_detail`,
                                                  `topik_aktif`,
                                                  `topik_creation_time`

  `cbt_soal`              Soal                    `soal_topik_id`,
                                                  `soal_detail`, `soal_tipe`
                                                  (1 PG, 2 essay, 3
                                                  singkat), `soal_kunci`,
                                                  `soal_difficulty` (1-5),
                                                  `soal_aktif`,
                                                  `soal_audio`,
                                                  `soal_timer`,
                                                  `soal_inline_answers`,
                                                  `soal_auto_next`,
                                                  `soal_creator_id`

  `cbt_jawaban`           Pilihan jawaban soal PG `jawaban_soal_id`,
                                                  `jawaban_detail`,
                                                  `jawaban_benar`,
                                                  `jawaban_aktif`
  --------------------------------------------------------------------------

### 5.4 Peserta dan Group/Kelas

  -------------------------------------------------------------------------
  Tabel                   Fungsi                  Kolom Penting
  ----------------------- ----------------------- -------------------------
  `cbt_user_grup`         Group/kelas             `grup_nama`,
                                                  `grup_jenjang`,
                                                  `grup_tingkat`,
                                                  `grup_foundation_id`,
                                                  `grup_school_id`,
                                                  `grup_academic_year_id`

  `cbt_user`              Peserta                 `user_grup_id`,
                                                  `user_name`,
                                                  `user_password`,
                                                  `user_firstname`,
                                                  `user_email`,
                                                  `user_birthdate`,
                                                  `user_birthplace`,
                                                  `user_level`,
                                                  `user_detail`,
                                                  `user_foundation_id`,
                                                  `user_school_id`,
                                                  `user_nis`, `user_nisn`,
                                                  `user_gender`,
                                                  `remember_token`
  -------------------------------------------------------------------------

> `user_level` pada peserta default 1. Ini berbeda dengan `user_level`
> milik admin/staff.

### 5.5 Ujian CBT

  ----------------------------------------------------------------------------
  Tabel                    Fungsi                  Kolom Penting
  ------------------------ ----------------------- ---------------------------
  `cbt_tes`                Definisi ujian          `tes_nama`, `tes_detail`,
                                                   `tes_begin_time`,
                                                   `tes_end_time`,
                                                   `tes_duration_time`,
                                                   `tes_ip_range`,
                                                   `tes_results_to_users`,
                                                   `tes_detail_to_users`,
                                                   `tes_ranking_to_users`,
                                                   `tes_score_right`,
                                                   `tes_score_wrong`,
                                                   `tes_score_unanswered`,
                                                   `tes_max_score`,
                                                   `tes_biaya`, `tes_token`,
                                                   `tes_foundation_id`,
                                                   `tes_school_id`,
                                                   `tes_academic_year_id`

  `cbt_tesgrup`            Relasi ujian ke group   `tstgrp_tes_id`,
                           peserta                 `tstgrp_grup_id`

  `cbt_tes_topik_set`      Komposisi soal ujian    `tset_tes_id`,
                                                   `tset_topik_id`,
                                                   `tset_tipe`,
                                                   `tset_difficulty`,
                                                   `tset_jumlah`,
                                                   `tset_jawaban`,
                                                   `tset_acak_jawaban`,
                                                   `tset_acak_soal`

  `cbt_tes_user`           Percobaan/attempt       `tesuser_tes_id`,
                           peserta                 `tesuser_user_id`,
                                                   `tesuser_status` (1
                                                   berjalan, 4 selesai),
                                                   `tesuser_creation_time`,
                                                   `tesuser_comment`,
                                                   `tesuser_token`

  `cbt_tes_soal`           Snapshot soal per       `tessoal_tesuser_id`,
                           attempt                 `tessoal_soal_id`,
                                                   `tessoal_jawaban_text`,
                                                   `tessoal_nilai`,
                                                   `tessoal_creation_time`,
                                                   `tessoal_display_time`,
                                                   `tessoal_change_time`,
                                                   `tessoal_reaction_time`,
                                                   `tessoal_ragu`,
                                                   `tessoal_order`,
                                                   `tessoal_num_answers`,
                                                   `tessoal_comment`,
                                                   `tessoal_audio_play`

  `cbt_tes_soal_jawaban`   Snapshot pilihan        `soaljawaban_tessoal_id`,
                           jawaban per soal        `soaljawaban_jawaban_id`,
                           attempt                 `soaljawaban_selected`,
                                                   `soaljawaban_order`

  `cbt_tes_token`          Token ujian             `token_isi`,
                                                   `token_user_id`,
                                                   `token_ts`, `token_aktif`,
                                                   `token_tes_id`
  ----------------------------------------------------------------------------

Catatan token:

-   `token_aktif = 1` berarti token berlaku selama masih di hari yang
    sama.
-   `token_aktif > 1` dianggap durasi menit sejak `token_ts`.
-   `token_tes_id = 0` berarti token berlaku untuk semua tes.
-   Token divalidasi hanya untuk tes yang `tes_token = true`.

### 5.6 Akademik dan Absensi

  -------------------------------------------------------------------------------------
  Tabel                     Fungsi                  Kolom Penting
  ------------------------- ----------------------- -----------------------------------
  `cbt_raport`              Nilai raport            `raport_user_id`,
                                                    `raport_modul_id`,
                                                    `raport_guru_id`, `raport_nilai`,
                                                    `raport_catatan`, `raport_jenjang`,
                                                    `raport_tingkat`

  `cbt_absensi`             Absensi peserta         `absensi_user_id`, `absensi_tes_id`
                                                    (nullable), `absensi_tanggal`,
                                                    `absensi_status`
                                                    (`hadir`/`izin`/`sakit`/`alpha`),
                                                    `absensi_sumber`
                                                    (`ujian`/`manual`),
                                                    `absensi_keterangan`

  `cbt_orang_tua`           Akun orang tua          `ortu_nama`, `ortu_username`,
                                                    `ortu_password`, `ortu_email`,
                                                    `ortu_telepon`

  `cbt_orang_tua_peserta`   Relasi orang tua ke     `ortu_id`, `user_id`
                            banyak anak             
  -------------------------------------------------------------------------------------

### 5.7 Pembayaran Biaya Ujian CBT

  ----------------------------------------------------------------------------------------
  Tabel                   Fungsi                  Kolom Penting
  ----------------------- ----------------------- ----------------------------------------
  `cbt_pembayaran`        Pembayaran biaya ujian  `pembayaran_user_id`,
                          CBT                     `pembayaran_tes_id`,
                                                  `pembayaran_ortu_id`,
                                                  `pembayaran_nominal`,
                                                  `pembayaran_status`
                                                  (`menunggu`/`diverifikasi`/`ditolak`),
                                                  `pembayaran_bukti`,
                                                  `pembayaran_catatan`,
                                                  `pembayaran_catatan_admin`,
                                                  `pembayaran_verified_by`,
                                                  `pembayaran_verified_at`, tenant columns

  ----------------------------------------------------------------------------------------

Satu peserta hanya memiliki satu record pembayaran per tes
(`ak_pembayaran_user_tes`). Bukti disimpan di
`storage/app/public/bukti-pembayaran` dan diakses melalui route khusus
(bukan URL publik langsung).

Gate pembayaran CBT dikonfigurasi melalui `cbt_konfigurasi` dengan kode
`cbt_gate_pembayaran` (`ya`/`tidak`, default `tidak`). Jika `ya` dan
`tes_biaya > 0`, peserta hanya dapat memulai ujian setelah memiliki
record `cbt_pembayaran` berstatus `diverifikasi` untuk tes tersebut.
Gate dapat diubah dari **Sistem -\> Pengaturan Aplikasi**.

### 5.8 Ledger SPP / Keuangan Sekolah

  -----------------------------------------------------------------------
  Tabel                               Fungsi
  ----------------------------------- -----------------------------------
  `fin_fee_categories`                Kategori biaya per sekolah
                                      (`category_code`, `category_name`,
                                      `is_recurring`, `is_active`)

  `fin_fee_structures`                Tarif biaya (`fee_category_id`,
                                      `academic_year_id`, `grade_code`,
                                      `amount`, periode efektif)

  `fin_student_bills`                 Tagihan siswa (`student_id`,
                                      `bill_number`, `issue_date`,
                                      `due_date`, `total_amount`,
                                      `paid_amount`, `status`)

  `fin_student_bill_items`            Rincian item tagihan
                                      (`description`, `quantity`,
                                      `unit_amount`, `line_total`)

  `fin_payment_methods`               Metode & rekening pembayaran per
                                      sekolah (`method_code`,
                                      `method_name`, `bank_name`,
                                      `account_number`, `account_holder`,
                                      `is_active`, `show_to_guardian`,
                                      `sort_order`)

  `fin_payments`                      Pembayaran SPP (`payment_number`,
                                      `bill_id`, `amount`, `status`,
                                      `payment_method_id`, `paid_at`,
                                      `recorded_by`, `guardian_id`,
                                      `proof_path`, `verified_by`,
                                      `verified_at`, `rejection_reason`)

  `fin_payment_allocations`           Alokasi pembayaran ke tagihan
                                      (`payment_id`, `bill_id`,
                                      `allocated_amount`)

  `fin_cash_accounts`                 Akun kas sekolah (fondasi, belum
                                      ada UI penuh)
  -----------------------------------------------------------------------

Kolom `bill_id`, `verified_by`, `verified_at`, dan `rejection_reason`
ditambahkan melalui migration
`2026_11_01_000018_add_spp_payment_verification`. Index `bill_id` serta
`status+paid_at` mempercepat daftar pembayaran dan rekonsiliasi.

Status tagihan SPP:

-   `draft` - baru dibuat, belum ada pembayaran.
-   `partial` - sudah dibayar sebagian.
-   `paid` - lunas.
-   `cancelled` - dibatalkan.

Status pembayaran SPP (`App\Models\SchoolPayment`):

-   `pending` - bukti diunggah orang tua, menunggu verifikasi.
-   `verified` - sudah diverifikasi admin/kasir, dialokasikan ke
    tagihan.
-   `rejected` - ditolak dengan `rejection_reason`; orang tua dapat
    mengunggah ulang.
-   `cancelled` - dibatalkan admin sebelum/sesudah ditolak; pembayaran
    terverifikasi tidak dapat dibatalkan.

Pembayaran yang dicatat langsung oleh admin/kasir melalui
`SppPaymentService::recordManual()` tersimpan sebagai `verified` dan
langsung mengurangi tagihan. Pembayaran dari portal orang tua melalui
`submitProof()` tersimpan sebagai `pending` sampai diverifikasi. Semua
transisi status memakai transaksi database dan `lockForUpdate`.

#### 5.8.1 Rekening Tujuan Transfer untuk Orang Tua

Admin dapat mengelola rekening bank/ATM yang ditampilkan ke orang tua
melalui **Keuangan -> Kategori & Tarif -> Metode & Rekening Pembayaran**
(`admin.spp.setup`). Setiap metode menyimpan `bank_name`,
`account_number`, `account_holder`, `show_to_guardian`, dan `sort_order`
di tabel `fin_payment_methods` (migration
`2026_11_02_000021_add_payment_account_fields_to_fin_payment_methods`).

-   Rekening hanya tampil jika `is_active = true`, `show_to_guardian =
    true`, dan `account_number` tidak kosong.
-   Daftar diurutkan berdasarkan `sort_order` lalu nama bank.
-   Halaman orang tua **Pembayaran Ujian** menampilkan kartu "Rekening
    Tujuan Transfer" dan mengulanginya di modal unggah bukti.
-   Halaman **Tagihan SPP** dan **Detail Tagihan SPP** menampilkan
    rekening yang sama sesuai sekolah tagihan/anak.
-   Nomor rekening dapat disalin langsung dari tombol **Salin**.
-   Jika belum ada rekening, halaman tetap dapat digunakan dan orang tua
    diarahkan menghubungi admin sekolah.

Admin dapat menambah, mengubah, dan menghapus rekening. Penghapusan
ditolak jika metode sudah dipakai transaksi pembayaran SPP.

### 5.9 Tabel Framework

-   `migrations`
-   `failed_jobs`
-   `personal_access_tokens`

### 5.10 Dua File SQL di Repositori

  ---------------------------------------------------------------------------------------------
  File                                       Isi                     Rekomendasi
  ------------------------------------------ ----------------------- --------------------------
  `database/schema/cbt_laravel_schema.sql`   Baseline schema-only    Referensi/kompatibilitas
                                             tabel legacy +          instalasi lama. Untuk
                                             migration history,      instalasi baru, migration
                                             tanpa data sample       Laravel sudah mencakup
                                                                     seluruh tabel legacy dan
                                                                     `fin_*`.

  `cbt_laravel.sql`                          Dump phpMyAdmin         Jangan dipakai untuk
                                             lengkap, kemungkinan    produksi; hanya referensi
                                             berisi data (25 INSERT) lokal. Jangan commit dump
                                                                     berisi data asli.
  ---------------------------------------------------------------------------------------------

------------------------------------------------------------------------

## 6. Menu dan Fungsinya

### 6.1 Menu Panel Administrator

  ----------------------------------------------------------------------------------------------
  Grup              Menu              Route                      Fungsi
  ----------------- ----------------- -------------------------- -------------------------------
  \-                Dashboard         `admin.dashboard`          Statistik
                                                                 modul/topik/soal/peserta/tes,
                                                                 grafik ujian 7 hari, nilai
                                                                 rata-rata, attempt terbaru, tes
                                                                 aktif, ubah password

  Yayasan & Sekolah Yayasan           `admin.yayasan.index`      CRUD yayasan (khusus scope
                                                                 system untuk tambah/hapus)

  Yayasan & Sekolah Sekolah & Tahun   `admin.sekolah.index`      CRUD sekolah, tahun ajaran, dan
                    Ajaran                                       semester otomatis

  Bank Soal         Modul             `admin.modul.index`        CRUD mata pelajaran/modul

  Bank Soal         Topik             `admin.topik.index`        CRUD grup soal, tipe ujian
                                                                 (UM/UTS/UAS), guru pemilik

  Bank Soal         Soal              `admin.soal.index`         CRUD soal, import CSV/XLSX,
                                                                 template, upload media, kelola
                                                                 grup soal

  Peserta           Group Peserta     `admin.grup.index`         CRUD kelas/group, jenjang
                                                                 (SD/SMP/SMA/SMK), tingkat

  Peserta           Daftar Peserta    `admin.peserta.index`      CRUD peserta, import CSV,
                                                                 template, grafik prestasi per
                                                                 kelas

  Peserta           Akun Orang Tua    `admin.orangtua.index`     CRUD akun orang tua dan relasi
                                                                 ke banyak peserta

  Peserta           Absensi Peserta   `admin.absensi.index`      CRUD absensi manual, filter,
                                                                 rekap status

  Manajemen Ujian   Daftar Tes        `admin.tes.index`          CRUD tes, pilih group,
                                                                 komposisi soal per topik, bobot
                                                                 nilai, token, biaya

  Manajemen Ujian   Token Ujian       `admin.token.index`        Generate token manual/acak,
                                                                 batas pemakaian, hapus token

  Manajemen Ujian   Hasil & Evaluasi  `admin.hasil.index`        Daftar hasil, detail jawaban,
                                                                 evaluasi essay, cetak, hentikan
                                                                 ujian

  Manajemen Ujian   Pembayaran Ujian  `admin.pembayaran.index`   Verifikasi/tolak bukti
                                                                 pembayaran biaya ujian CBT

  Keuangan          Tagihan SPP       `admin.spp.index`          Daftar tagihan, statistik, buat
                                                                 tagihan, detail, hapus tagihan

  Keuangan          Pembayaran SPP    `admin.spp.payments`       Riwayat pembayaran SPP

  Keuangan          Kategori & Tarif  `admin.spp.setup`          CRUD kategori biaya, tarif,
                                                                 metode & rekening pembayaran

  \-                Guru              `admin.guru.index`         Monitoring peserta ujian, input
                                                                 raport, toggle ranking, cetak
                                                                 ranking

  \-                Kelulusan         `admin.kelulusan.index`    Daftar kelulusan, filter
                                                                 kelas/status, cetak SKL

  \-                Laporan           `admin.laporan.rekap`      Rekap nilai per tes; halaman
                                                                 analisis butir soal

  Sistem            Pengaturan        `admin.pengaturan.index`   Nama aplikasi, informasi,
                    Aplikasi                                     alamat, kepala sekolah, tahun
                                                                 ajaran, KKM

  Sistem            User              `admin.user.index`         CRUD user admin/staff, override
                    Administrator                                menu per akun

  Sistem            Level & Hak Akses `admin.level.index`        CRUD level dan atur menu
                                                                 default per level
  ----------------------------------------------------------------------------------------------

Route tambahan di luar menu:

-   `PUT /admin/password` - ubah password administrator.
-   `GET/PUT /admin/preferensi-tampilan` - preferensi tema dan warna.
-   `POST /admin/sekolah/switch` - ganti konteks sekolah aktif.
-   `GET /admin/soal/{soal}/edit`, `POST /admin/soal/upload`,
    `GET /admin/soal/template`, `POST /admin/soal/import` - operasi
    soal.
-   `GET /admin/spp/tagihan/create`, `POST /admin/spp/tagihan`,
    `GET /admin/spp/tagihan/{bill}`, `DELETE /admin/spp/tagihan/{bill}`,
    `POST /admin/spp/tagihan/{bill}/bayar` - operasi tagihan dan
    pembayaran SPP.
-   `GET /admin/spp/pembayaran`, `GET /admin/spp/pembayaran/{payment}`,
    `GET /admin/spp/pembayaran/{payment}/bukti`,
    `GET /admin/spp/pembayaran/{payment}/kwitansi` - daftar, detail,
    bukti, dan kwitansi pembayaran SPP.
-   `POST /admin/spp/pembayaran/{payment}/verifikasi` (`approve`) dan
    `DELETE /admin/spp/pembayaran/{payment}/batalkan` (`delete`) -
    verifikasi/tolak dan pembatalan pembayaran SPP.
-   CRUD kategori, tarif, metode, dan rekening pembayaran di bawah
    `admin.spp.setup` memakai aksi `add`, `edit`, dan `delete`
    (`POST /admin/spp/metode`, `PUT /admin/spp/metode/{method}`,
    `DELETE /admin/spp/metode/{method}`).
-   `GET /admin/laporan/analisis` - analisis butir soal.
-   `GET /admin/guru/raport`, `POST /admin/guru/raport`,
    `PUT /admin/guru/tes/{tes}/ranking`,
    `GET /admin/guru/tes/{tes}/ranking`,
    `GET /admin/guru/tes/{tes}/ranking/cetak` - raport dan ranking.

### 6.2 Menu Portal Peserta

  ---------------------------------------------------------------------------
  Menu                    Route                       Fungsi
  ----------------------- --------------------------- -----------------------
  Dashboard               `peserta.dashboard`         Daftar tes sesuai
                                                      kelas, status attempt,
                                                      ringkasan, tes dengan
                                                      ranking

  Prestasi                `peserta.prestasi.index`    Riwayat ujian selesai,
                                                      rata-rata, nilai
                                                      tertinggi, grafik
                                                      prestasi per kelas

  Raport                  `peserta.raport.index`      Nilai raport per
                                                      jenjang/tingkat/mata
                                                      pelajaran, rata-rata

  Kelulusan               `peserta.kelulusan.index`   Status kelulusan dan
                                                      cetak SKL (muncul jika
                                                      kelas akhir)

  Ranking                 `peserta.ranking.index`     Daftar ranking tes yang
                                                      diaktifkan (muncul jika
                                                      ada tes ranking
                                                      selesai)

  Ujian                   `peserta.tes.show`,         Detail tes, mulai,
                          `mulai`, `ujian.kerjakan`,  mengerjakan, menjawab,
                          `ujian.jawab`,              ragu, selesai, hasil,
                          `ujian.ragu`,               cetak
                          `ujian.selesai`,            
                          `ujian.hasil`,              
                          `ujian.cetak`               
  ---------------------------------------------------------------------------

### 6.3 Menu Portal Orang Tua

  -----------------------------------------------------------------------------
  Menu                    Route                         Fungsi
  ----------------------- ----------------------------- -----------------------
  Dashboard               `orangtua.dashboard`          Ringkasan absensi,
                                                        tagihan ujian, sisa
                                                        pembayaran, status
                                                        verifikasi

  Absensi Anak            `orangtua.absensi.index`      Riwayat dan rekap
                                                        absensi anak

  Pembayaran Ujian        `orangtua.pembayaran.index`   Daftar tagihan ujian
                                                       CBT, rekening tujuan
                                                       transfer, dan unggah
                                                       bukti pembayaran

  Bukti Ujian             `orangtua.pembayaran.bukti`   Melihat bukti
                                                        pembayaran ujian milik
                                                        anak (dengan otorisasi)

  Tagihan SPP             `orangtua.spp.index`          Ringkasan tagihan,
                                                       rekening tujuan, sisa,
                                                       pembayaran
                                                       menunggu/ditolak, dan
                                                       riwayat

  Detail SPP              `orangtua.spp.show`           Rincian tagihan,
                                                       rekening tujuan, form
                                                       bayar/cicil, dan unggah
                                                       bukti transfer

  Bukti/Kwitansi SPP      `orangtua.spp.bukti`,         Melihat bukti yang
                          `orangtua.spp.kwitansi`       diunggah dan mencetak
                                                        kwitansi pembayaran
                                                        terverifikasi
  -----------------------------------------------------------------------------

Orang tua hanya dapat membuka tagihan, bukti, dan kwitansi milik anak
yang terhubung dengan akunnya. Setiap endpoint memvalidasi `student_id`
terhadap anak aktif.

Orang tua dapat memiliki banyak anak. Anak aktif dipilih melalui query
`?anak={user_id}` dan divalidasi terhadap relasi orang tua.

------------------------------------------------------------------------

## 7. Matriks Role dan Hak Akses

Data default dari `CbtSeeder`:

  -----------------------------------------------------------------------
  Role                                Menu Utama
  ----------------------------------- -----------------------------------
  `admin`                             Semua menu (super administrator)

  `operator-soal`                     modul, topik, soal, guru, absensi

  `operator-tes`                      tes, token, hasil, guru, absensi

  `administrator-sekolah`             dashboard, sekolah, modul, topik,
                                      soal, grup, peserta, orangtua,
                                      absensi, tes, token, hasil,
                                      pembayaran, spp, spp-pembayaran,
                                      spp-pengaturan, guru, kelulusan,
                                      laporan, pengaturan

  `kepala-sekolah`                    dashboard, sekolah, peserta, guru,
                                      absensi, hasil, pembayaran, spp,
                                      spp-pembayaran, kelulusan, laporan

  `guru`                              dashboard (default). Menu lain
                                      diberikan lewat override akun atau
                                      perubahan level access.

  `wali-kelas`                        dashboard, guru, peserta, absensi,
                                      hasil, kelulusan, laporan

  `bendahara`                         dashboard, pembayaran, spp,
                                      spp-pembayaran, spp-pengaturan,
                                      laporan

  `petugas-pembayaran`                dashboard, pembayaran, spp,
                                      spp-pembayaran

  `petugas-kasir`                     dashboard, pembayaran, spp,
                                      spp-pembayaran

  `petugas-spp`                       dashboard, spp, spp-pembayaran

  `operator-sekolah`                  dashboard, sekolah, grup, peserta,
                                      orangtua, guru, laporan

  `petugas-perpustakaan`              dashboard

  `petugas-bk`                        dashboard, peserta, laporan

  `petugas-uks`                       dashboard, peserta, absensi

  `petugas-tata-usaha`                dashboard, peserta, orangtua, grup,
                                      absensi
  -----------------------------------------------------------------------

Hal penting:

-   Role `guru` di seeder hanya mendapat dashboard. Akun sampel
    `gurua`/`gurub`/`guruc` mendapat override personal untuk `soal`,
    `topik`, `grup`, dan `guru`.
-   Untuk memberi akses ke menu lain, gunakan override menu per akun di
    **Sistem -\> User Administrator** atau ubah akses level di **Sistem
    -\> Level & Hak Akses**.
-   Setiap menu dapat diatur per aksi: Lihat, Tambah, Ubah, Hapus, dan
    Setujui. Aksi Hapus/Setujui jatuh ke nilai Ubah jika kolomnya masih
    `null` (kompatibilitas data lama).
-   Override per akun mengalahkan hak akses level untuk menu yang
    di-override.
-   Menu yang disembunyikan **bukan** pengganti pengecekan otorisasi di
    controller/query.
-   Modul dengan nama "Guru" dipakai untuk monitoring ujian, input
    raport, dan ranking. Modul ini dapat diberikan ke role lain (kepala
    sekolah, wali kelas, operator).
-   Data topik/soal dibatasi lagi oleh kepemilikan `topik_guru_id`.

------------------------------------------------------------------------

## 8. Instalasi dan Menjalankan Aplikasi

### 8.1 Prasyarat

-   PHP 8.1+ dengan ekstensi `pdo_mysql`, `mbstring`, `dom`,
    `xmlwriter`, `fileinfo`, `openssl`.
-   Composer 2.
-   MySQL 8+ / MariaDB yang kompatibel.
-   Node.js 18+ dan npm.

### 8.2 Langkah Instalasi

``` bash
# 1. Masuk ke folder proyek
cd cbt-laravel

# 2. Salin environment
cp .env.example .env

# 3. Install dependency PHP
composer install

# 4. Generate application key
php artisan key:generate

# 5. Buat database MySQL
#    Contoh: CREATE DATABASE cbt_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 6. Instalasi baru: jalankan seluruh migration (legacy CBT, core_*, fin_*, akses, preferensi)
php artisan migrate

#    Alternatif kompatibilitas: import baseline lama lalu jalankan migration yang belum tercatat
#    mysql -u root -p cbt_laravel < database/schema/cbt_laravel_schema.sql
#    php artisan migrate

# 8. (Opsional, development saja) Isi data demo
php artisan db:seed

# 9. Build asset frontend
npm ci
npm run build

# 10. Symlink storage publik (untuk bukti pembayaran)
php artisan storage:link

# 11. Jalankan server
php artisan serve
```

Buka `http://127.0.0.1:8000`.

### 8.3 Catatan Penting Instalasi

-   Untuk instalasi baru, `php artisan migrate` sudah cukup karena
    seluruh tabel legacy CBT, `core_*`, `fin_*`,
    `user_menu_user_access`, dan `user_preferences` dibuat oleh
    migration.
-   Baseline `database/schema/cbt_laravel_schema.sql` tetap dapat
    dipakai untuk database lama. File ini sudah memuat baris migration
    history, sehingga `php artisan migrate` hanya menjalankan migration
    yang belum tercatat.
-   `MigrationParityTest` memverifikasi bahwa tabel dan kolom penting
    hasil migration tetap lengkap. Jalankan
    `php artisan test --filter=MigrationParityTest` setelah mengubah
    migration.
-   `cbt_laravel.sql` di root adalah dump lengkap. Jika memakai dump
    ini, database sudah berisi data; jangan mencampur dengan seed tanpa
    memahami status datanya.
-   File `.env` dan dump database tidak boleh di-commit.
-   Untuk produksi, gunakan `APP_ENV=production`, `APP_DEBUG=false`,
    HTTPS, secure cookie, backup terenkripsi, dan kredensial database
    least-privilege.

------------------------------------------------------------------------

## 9. Akun Demo

Dibuat oleh `CbtSeeder` hanya untuk development.

### Administrator / Staff

  Username         Password         Role            Scope
  ---------------- ---------------- --------------- --------------------------
  `admin`          `admin`          admin           system
  `adminyayasan`   `adminyayasan`   admin           foundation
  `adminsekolah`   `adminsekolah`   admin           school (SMA Nusantara 2)
  `operator`       `operator`       operator-tes    system
  `operatorsoal`   `operatorsoal`   operator-soal   system
  `gurua`          `gurua`          guru            system
  `gurub`          `gurub`          guru            system
  `guruc`          `guruc`          guru            system

Akun `gurua`, `gurub`, dan `guruc` mendapat override personal untuk menu
`soal`, `topik`, `grup`, dan `guru` agar dapat mengelola topik/soal
miliknya dan menilai raport. Role `guru` default tetap hanya
`dashboard`.

### Peserta

Username = password.

Contoh: `lutfi`, `joko`, `siti`, `budi`, `rina`, `andi`, `dewi`,
`fajar`, `raka`, `sd1a`, `smp1a`, `sma1a`, `smk1a`, dan akun jenjang
lainnya yang dibuat seeder.

### Orang Tua

  Username      Password      Anak
  ------------- ------------- -----------------
  `orangtua1`   `orangtua1`   `raka`, `lutfi`
  `orangtua2`   `orangtua2`   `siti`

### Token Ujian Demo

-   Token `123456` untuk tes **Ulangan Bahasa Indonesia**.
-   Tes **Try Out Matematika** tidak memakai token.

> Ganti seluruh akun dan password demo sebelum production.

------------------------------------------------------------------------

## 10. Cara Menggunakan Aplikasi

### 10.1 Persiapan Awal oleh Super Admin

1.  Login di `/admin/login` menggunakan akun `admin`.
2.  Buka **Yayasan & Sekolah -\> Yayasan**, tambahkan data yayasan.
3.  Buka **Yayasan & Sekolah -\> Sekolah & Tahun Ajaran**:
    -   Tambahkan sekolah dan pilih jenjang.
    -   Tambahkan tahun ajaran; sistem otomatis membuat semester 1 dan
        2.
    -   Tandai satu tahun ajaran sebagai aktif.
4.  Buka **Peserta -\> Group Peserta**, buat kelas/group dan tentukan
    jenjang serta tingkat.
5.  Buka **Peserta -\> Daftar Peserta**, tambahkan peserta satu per satu
    atau import CSV.
6.  Buka **Peserta -\> Akun Orang Tua**, buat akun orang tua dan
    hubungkan ke anak.
7.  Buka **Sistem -\> User Administrator**, buat akun staff/guru dan
    atur role/level.
8.  Buka **Sistem -\> Level & Hak Akses**, sesuaikan menu default per
    level bila perlu.
9.  Buka **Sistem -\> Pengaturan Aplikasi**, isi nama sekolah, alamat,
    kepala sekolah, tahun ajaran, KKM kelulusan, dan **Wajib Pembayaran
    Sebelum Ujian** (`cbt_gate_pembayaran`).

### 10.2 Menyiapkan Bank Soal

1.  Buka **Bank Soal -\> Modul**, tambahkan mata pelajaran (contoh:
    Matematika).
2.  Buka **Bank Soal -\> Topik**, tambahkan grup soal. Pilih modul, tipe
    ujian, dan guru pemilik.
3.  Buka **Bank Soal -\> Soal**, pilih grup soal lalu tambahkan soal:
    -   Pilihan ganda: isi minimal dua opsi dan pilih satu jawaban
        benar.
    -   Essay: jawaban akan dinilai manual oleh admin/guru.
    -   Jawaban singkat: isi kunci jawaban; sistem mencocokkan teks
        secara case-insensitive.
    -   Atur tingkat kesulitan 1-5.
4.  Untuk import massal:
    -   Unduh template CSV/XLSX dari halaman soal.
    -   Isi kolom sesuai template.
    -   Pilih grup soal tujuan, lalu unggah file.
5.  Untuk media soal, gunakan fitur upload gambar/audio. File disimpan
    di `public/uploads/topik_{id}/`.

### 10.3 Membuat dan Menjalankan Ujian

1.  Buka **Manajemen Ujian -\> Daftar Tes**, klik buat tes.
2.  Isi nama, deskripsi, waktu mulai/selesai, durasi, bobot nilai
    benar/salah/kosong, nilai maksimum, dan biaya ujian (opsional).
3.  Pilih group/kelas peserta yang boleh mengikuti tes.
4.  Tambahkan komposisi soal:
    -   Pilih topik.
    -   Pilih tipe soal dan tingkat kesulitan.
    -   Tentukan jumlah soal.
    -   Atur acak soal dan acak jawaban.
5.  Aktifkan token jika diperlukan, lalu buat token di **Manajemen Ujian
    -\> Token Ujian**.
6.  Jika tes berbayar, orang tua dapat mengunggah bukti pembayaran. Jika
    `cbt_gate_pembayaran = ya`, admin harus memverifikasi pembayaran
    sebelum peserta dapat mulai.
7.  Peserta login di `/peserta/login`, memilih tes, membaca detail, lalu
    klik mulai.
8.  Setelah ujian selesai, admin dapat memantau di **Guru** dan melihat
    hasil di **Manajemen Ujian -\> Hasil & Evaluasi**.
9.  Essay dinilai manual melalui halaman detail hasil.
10. Aktifkan ranking dari **Guru -\> Kontrol Ranking** jika ingin
    menampilkan ranking ke peserta.

### 10.4 Input Raport

1.  Login sebagai admin atau guru yang memiliki akses menu **Guru**.
2.  Buka **Guru -\> Raport**.
3.  Pilih mata pelajaran yang diampu (guru non-admin hanya melihat modul
    yang memiliki topik miliknya).
4.  Pilih kelas.
5.  Isi nilai 0-100 dan catatan untuk setiap peserta.
6.  Simpan. Nilai disimpan per peserta, modul, jenjang, dan tingkat.

### 10.5 Kelulusan

1.  Pastikan peserta berada di kelas akhir (SD kelas 6, SMP/SMA/SMK
    kelas 3).
2.  Pastikan seluruh modul aktif sudah memiliki nilai raport untuk kelas
    tersebut.
3.  Buka **Kelulusan**, filter peserta, lalu cetak SKL.
4.  Peserta dapat melihat status dan mencetak SKL dari portal peserta
    jika memenuhi syarat.

Syarat kelulusan:

-   Berada di kelas akhir.
-   Semua modul aktif memiliki nilai raport.
-   Rata-rata nilai \>= `cbt_nilai_kelulusan` (default 70).

### 10.6 Verifikasi Pembayaran Ujian CBT

1.  Admin menambahkan rekening bank/ATM pada **Keuangan -\> Kategori &
    Tarif -\> Metode & Rekening Pembayaran** dan mengaktifkan opsi
    **Tampilkan ke orang tua**.
2.  Orang tua login di `/orangtua/login`.
3.  Pilih anak, buka **Pembayaran Ujian**; rekening tujuan transfer
    tampil di halaman dan di modal unggah bukti.
4.  Unggah bukti pembayaran (JPG/PNG/PDF, maksimal 4 MB) dan isi
    catatan.
5.  Admin membuka **Manajemen Ujian -\> Pembayaran Ujian**.
6.  Admin memverifikasi atau menolak dengan catatan.
7.  Jika ditolak, orang tua dapat mengunggah ulang bukti.
8.  Pembayaran yang sudah diverifikasi tidak dapat diubah.

Gate pembayaran bersifat opsional. Aktifkan **Sistem -\> Pengaturan
Aplikasi -\> Wajib Pembayaran Sebelum Ujian**
(`cbt_gate_pembayaran = ya`) jika status pembayaran harus menjadi syarat
memulai ujian. Jika aktif dan `tes_biaya > 0`, peserta tanpa pembayaran
`diverifikasi` akan melihat rincian biaya/status dan form mulai diganti
peringatan. Jika nonaktif, verifikasi berfungsi sebagai pencatatan
administratif.

### 10.7 SPP End-to-End

**Admin menyiapkan tagihan**

1.  Buka **Keuangan -\> Kategori & Tarif**.
2.  Tambahkan kategori biaya (contoh: SPP Bulanan).
3.  Tambahkan tarif per kategori, tahun ajaran, dan kode tingkat bila
    perlu.
4.  Tambahkan metode pembayaran dan rekening tujuan (bank/ATM, nomor
    rekening, atas nama) lalu aktifkan **Tampilkan ke orang tua**.
5.  Buka **Keuangan -\> Tagihan SPP**, klik buat tagihan.
6.  Pilih siswa, tanggal terbit, jatuh tempo, lalu tambahkan item
    tagihan (deskripsi, kuantitas, harga satuan).
7.  Simpan; sistem menghitung total dan membuat nomor tagihan otomatis.

**Admin mencatat pembayaran langsung**

8.  Buka detail tagihan, catat pembayaran dengan nominal \<= sisa
    tagihan.
9.  `SppPaymentService::recordManual()` membuat pembayaran `verified`,
    alokasi, dan memperbarui status tagihan menjadi `partial` atau
    `paid`.
10. Riwayat pembayaran dapat dilihat di **Keuangan -\> Pembayaran SPP**.

**Orang tua membayar melalui portal**

11. Orang tua login di `/orangtua/login`, pilih anak, lalu buka
    **Tagihan SPP**.
12. Buka detail tagihan, isi nominal (boleh cicilan), unggah bukti
    JPG/PNG/PDF maksimal 4 MB, dan kirim.
13. Sistem menyimpan pembayaran `pending`; tagihan belum berubah sampai
    admin memverifikasi.
14. Jika masih ada bukti `pending` untuk tagihan yang sama, unggahan
    baru memperbarui bukti tersebut, bukan membuat duplikat.
15. Admin membuka **Keuangan -\> Pembayaran SPP**, memfilter
    status/tanggal, lalu membuka detail.
16. Admin dapat memverifikasi (`verified`) atau menolak dengan alasan
    (`rejected`).
17. Verifikasi mengalokasikan nominal ke tagihan secara transaksional
    dan memperbarui `paid_amount`/status tagihan.
18. Penolakan menyimpan `rejection_reason`; orang tua dapat mengunggah
    ulang bukti baru.
19. Pembayaran `pending`/`rejected` dapat dibatalkan admin. Pembayaran
    `verified` tidak dapat dibatalkan.
20. Kwitansi hanya tersedia untuk pembayaran `verified` dan dapat
    dicetak admin maupun orang tua.

------------------------------------------------------------------------

## 11. Flow / Alur Sistem

### 11.1 Flow Login dan Penentuan Portal

``` mermaid
flowchart TD
    A[User buka /] --> B{Sudah login?}
    B -->|guard peserta| C[/peserta/dashboard]
    B -->|guard web| D[/admin/dashboard]
    B -->|guard orangtua| E[/orangtua/dashboard]
    B -->|belum| F[/peserta/login]
    F --> G{Username ada?}
    G -->|tidak| F
    G -->|ya| H{Password valid? bcrypt/plain/sha1}
    H -->|tidak| F
    H -->|ya| I[Upgrade hash ke bcrypt]
    I --> J[Login guard sesuai]
    J --> K[Regenerate session]
    K --> L[Dashboard sesuai role]
```

### 11.2 Flow Konteks Sekolah dan Otorisasi Admin

``` mermaid
flowchart TD
    A[Request /admin/*] --> B[Middleware admin.access]
    B --> C{Sudah login guard web?}
    C -->|tidak| D[Redirect /admin/login]
    C -->|ya| E{Level admin?}
    E -->|ya| F[Akses penuh]
    E -->|tidak| G{Ada override menu?}
    G -->|ya| H{is_allowed?}
    H -->|tidak| I[Redirect dashboard + error]
    H -->|ya| H2{Cek aksi override: add/edit/delete/approve}
    G -->|tidak| J{Cek user_akses level}
    J -->|tidak ada| I
    J -->|ada| J2{Cek kolom aksi level}
    H2 -->|tidak diizinkan| I
    J2 -->|tidak diizinkan| I
    H2 -->|diizinkan| F
    J2 -->|diizinkan| F
    F --> K[Middleware school.context]
    K --> L[Resolve scope system/foundation/school]
    L --> M[Set activeSchool + foundationId]
    M --> N[Controller query difilter tenant]
    N --> O[Render halaman]
```

### 11.3 Flow Lengkap CBT

``` mermaid
flowchart TD
    subgraph Admin
        A1[Buat Modul] --> A2[Buat Topik + pilih guru]
        A2 --> A3[Tambah Soal PG/Essay/Singkat]
        A3 --> A4[Buat Tes: waktu, durasi, bobot, biaya]
        A4 --> A5[Pilih Group peserta]
        A5 --> A6[Komposisi soal per topik & difficulty]
        A6 --> A7{Aktifkan token?}
        A7 -->|ya| A8[Generate Token]
        A7 -->|tidak| A9[Tes siap]
        A8 --> A9
    end

    subgraph Peserta
        P1[Login peserta] --> P2[Dashboard: tes sesuai group]
        P2 --> P3[Detail tes]
        P3 --> P4{Token diperlukan?}
        P4 -->|ya| P5[Input token]
        P4 -->|tidak| P6[Mulai attempt]
        P5 -->|valid| P6
        P5 -->|invalid| P3
        P6 --> P7[TesUser status 1 berjalan]
        P7 --> P8[Absensi otomatis hadir]
        P8 --> P9[Snapshot soal & opsi jawaban]
        P9 --> P10[Kerjakan soal + timer]
        P10 --> P11[Jawab / ragu / navigasi]
        P11 --> P10
        P10 --> P12{Selesai / waktu habis?}
        P12 -->|selesai| P13[Finalisasi status 4]
        P12 -->|habis| P13
        P13 --> P14[Lihat hasil & ranking]
    end

    A9 --> P2
    P13 --> H1[Admin: Hasil & Evaluasi]
    H1 --> H2[Nilai essay manual]
    H2 --> H3[Ranking & cetak]
    H3 --> H4[Laporan rekap & analisis butir soal]
```

### 11.4 Flow Penilaian dan Finalisasi Ujian

``` mermaid
flowchart TD
    A[Peserta menjawab soal] --> B{Tipe soal?}
    B -->|Pilihan ganda| C{Bobot benar?}
    C -->|benar| D[nilai = tes_score_right]
    C -->|salah| E[nilai = tes_score_wrong]
    B -->|Jawaban singkat| F{Teks = kunci? case-insensitive}
    F -->|ya| D
    F -->|tidak| E
    B -->|Essay| G[nilai = 0, menunggu evaluasi manual]
    D --> H[Simpan tessoal_nilai + waktu ubah]
    E --> H
    G --> H
    H --> I{Selesai / kedaluwarsa?}
    I -->|ya| J[Finalisasi: belum dijawab = tes_score_unanswered]
    J --> K[Status tesuser = 4 selesai]
    K --> L[Nilai total = sum tessoal_nilai]
```

### 11.5 Flow Raport, Prestasi, dan Kelulusan

``` mermaid
flowchart TD
    A[Guru/Admin buka menu Guru -> Raport] --> B[Filter modul yang diampu]
    B --> C[Pilih kelas]
    C --> D[Isi nilai 0-100 + catatan]
    D --> E[Simpan cbt_raport per siswa/modul/jenjang/tingkat]
    E --> F[Peserta melihat raport & prestasi]
    E --> G{Siswa di kelas akhir?}
    G -->|tidak| H[Belum bisa SKL]
    G -->|ya| I{Semua modul aktif punya nilai?}
    I -->|tidak| H
    I -->|ya| J{Rata-rata >= KKM?}
    J -->|tidak| H
    J -->|ya| K[Lulus + cetak SKL]
```

### 11.6 Flow Pembayaran Biaya Ujian CBT

``` mermaid
sequenceDiagram
    participant OT as Orang Tua
    participant SYS as Sistem
    participant AD as Admin

    OT->>SYS: Pilih anak & tes berbayar
    OT->>SYS: Unggah bukti + catatan
    SYS->>SYS: Simpan cbt_pembayaran status menunggu
    SYS-->>OT: Menunggu verifikasi
    AD->>SYS: Buka daftar pembayaran (scope sekolah)
    AD->>SYS: Verifikasi / Tolak + catatan
    SYS->>SYS: Update status + verifikator + waktu
    SYS-->>OT: Status diverifikasi / ditolak
    OT->>SYS: Jika ditolak, unggah ulang bukti
    OT->>SYS: Peserta membuka tes berbayar
    SYS->>SYS: Cek cbt_gate_pembayaran
    alt Gate aktif dan belum ada pembayaran diverifikasi
        SYS-->>OT: Tampilkan rincian biaya, status, catatan admin
        SYS-->>OT: Tolak POST mulai ujian
    else Gate nonaktif atau pembayaran sudah diverifikasi
        SYS-->>OT: Izinkan mulai ujian (token tetap divalidasi)
    end
```

### 11.7 Flow SPP

``` mermaid
flowchart TD
    A[Admin setup kategori biaya] --> B[Admin setup tarif per tahun/tingkat]
    B --> C[Admin setup metode pembayaran]
    C --> D[Admin buat tagihan siswa]
    D --> E[Pilih siswa + item tagihan]
    E --> F[Sistem hitung total & nomor tagihan]
    F --> G[Status draft]
    G --> H{Jalur pembayaran?}
    H -->|Admin/kasir| I[recordManual]
    H -->|Orang tua| J[submitProof]
    I --> K{Nominal <= sisa?}
    K -->|tidak| L[Tolak validasi]
    K -->|ya| M[fin_payments verified + alokasi]
    J --> N[fin_payments pending + proof_path]
    N --> O[Admin buka detail & bukti]
    O --> P{Keputusan admin}
    P -->|verifikasi| Q{Nominal <= sisa?}
    Q -->|tidak| R[Validasi gagal]
    Q -->|ya| S[verified + alokasi]
    P -->|tolak| T[rejected + rejection_reason]
    T --> U[Orang tua unggah ulang]
    U --> N
    M --> V[Update paid_amount]
    S --> V
    V --> W{paid >= total?}
    W -->|ya| X[Status paid / lunas]
    W -->|tidak| Y[Status partial]
    S --> Z[Kwitansi tersedia]
    M --> Z
```

### 11.8 Flow Logout

1.  User menekan tombol keluar.
2.  `POST /admin/logout`, `/peserta/logout`, atau `/orangtua/logout`.
3.  Guard terkait di-logout.
4.  Session di-invalidate dan CSRF token di-regenerate.
5.  Redirect ke halaman login portal masing-masing.

------------------------------------------------------------------------

## 12. Testing dan Pemeriksaan

Perintah yang tersedia:

``` bash
# Menjalankan seluruh test
php artisan test

# Memeriksa route
php artisan route:list --except-vendor

# Format kode (opsional)
./vendor/bin/pint

# Build frontend
npm run build
```

Test yang tersedia di `tests/Feature`:

-   `CbtSmokeTest` - smoke test alur utama CBT.
-   `AuditTest`, `Audit2Test` - pemeriksaan perilaku/audit.
-   `GuruMonitoringTest` - monitoring ujian dan ranking.
-   `GuruOwnershipTest` - kepemilikan topik/soal per guru.
-   `OrangTuaTest` - portal orang tua.
-   `RaportPrestasiTest` - raport dan prestasi.
-   `SoalImportTest` - import soal.
-   `SppTest` - SPP end-to-end: tagihan, cicilan, unggah bukti,
    verifikasi, penolakan, pembatalan, kwitansi.
-   `AdminPermissionTest` - permission granular per aksi dan override
    personal.
-   `PaymentGateTest` - gate pembayaran CBT aktif/nonaktif.
-   `MigrationParityTest` - kelengkapan tabel/kolom hasil migration
    legacy dan finance.

Catatan:

-   Test menggunakan `RefreshDatabase`. Pastikan database test tersedia.
-   Beberapa test membutuhkan ekstensi PHP `dom`, `mbstring`, dan
    `xmlwriter`.
-   Test memakai seeder demo, jadi data awal harus konsisten.

------------------------------------------------------------------------

## 13. Keamanan

-   Jangan commit `.env`, dump database asli, kredensial, `vendor`,
    `node_modules`, log, atau cache.
-   Jika `.env` pernah dibagikan, rotasi `APP_KEY`, password database,
    kredensial SMTP, dan secret lain.
-   Gunakan `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, secure
    cookie, dan backup terenkripsi.
-   Batasi akses file bukti pembayaran melalui controller dengan
    pengecekan tenant dan kepemilikan.
-   Gunakan database user dengan privilege minimum.
-   Ganti seluruh akun demo sebelum production.
-   Audit seluruh controller/query untuk memastikan filter tenant dan
    kepemilikan data.
-   Password legacy (plain/SHA1) hanya untuk kompatibilitas. Setelah
    login, hash di-upgrade ke bcrypt. Tetap lakukan reset password
    massal jika data lama dianggap tidak aman.

------------------------------------------------------------------------

## 14. Batasan dan Rencana Pengembangan

### Batasan saat ini

-   Modul SPP sudah memiliki portal orang tua, unggah bukti, cicilan,
    verifikasi/penolakan, pembatalan, dan kwitansi. Yang **belum**
    tersedia: penerbitan tagihan massal, rekonsiliasi bank/gateway,
    refund/reversal ber-approval, dan pengelolaan akun kas penuh.
-   Gate pembayaran biaya ujian CBT tersedia dan konfigurabel
    (`cbt_gate_pembayaran`), tetapi belum ada payment gateway otomatis;
    verifikasi masih manual.
-   Permission sudah granular per aksi (`view/add/edit/delete/approve`)
    untuk menu yang memakai middleware aksi. Aksi `reverse/refund` dan
    policy domain yang lebih rinci belum ada.
-   Seluruh tabel legacy CBT, `core_*`, `fin_*`, akses user, dan
    preferensi sudah dibuat melalui migration Laravel; baseline SQL
    tetap disediakan untuk kompatibilitas instalasi lama.
    `MigrationParityTest` menjaga kelengkapan tabel/kolom.
-   Akses topik/soal dibatasi kepemilikan `topik_guru_id`, termasuk
    untuk administrator. Administrator harus menjadi pemilik topik atau
    membuat topik baru untuk mengelola soalnya.
-   Role `guru` default hanya memiliki dashboard; akses menu lain harus
    diberikan melalui override akun atau level access. Seeder memberikan
    akses `soal`, `topik`, `grup`, dan `guru` khusus untuk akun sampel
    `gurua`, `gurub`, dan `guruc`.
-   Belum ada API `/api/v1` lengkap, notifikasi, audit enterprise, atau
    laporan konsolidasi lintas sekolah.
-   Modul kurikulum versi, kalender akademik, penawaran mapel, jadwal,
    PPDB, e-learning, perpustakaan, inventaris, BK/UKS, HR/payroll belum
    diimplementasikan.

### Prioritas pengembangan berikutnya

1.  Lengkapi SPP lanjutan: penerbitan tagihan massal, rekonsiliasi
    bank/gateway, refund/reversal ber-approval, dan akun kas.
2.  Terapkan policy per aksi secara konsisten di seluruh modul, termasuk
    aksi `reverse/refund`, dan audit seluruh query tenant.
3.  Pertahankan parity migration melalui test; siapkan strategi
    `migrate:fresh` untuk instalasi baru tanpa baseline SQL.
4.  Integrasikan payment gateway dengan idempotency dan callback
    bertanda tangan untuk CBT dan SPP.
5.  Tambahkan test isolasi yayasan/sekolah yang lebih menyeluruh.
6.  Tambahkan notifikasi dan laporan konsolidasi.
7.  Tinjau dukungan Laravel 10 dan rencanakan upgrade.

------------------------------------------------------------------------

## 15. Dokumen Terkait

-   [`docs/IMPLEMENTASI_ENTERPRISE.md`](docs/IMPLEMENTASI_ENTERPRISE.md) -
    pemetaan role, perubahan keamanan, skema SPP, hasil pemeriksaan, dan
    gap implementasi.
-   [`docs/EXPANSION-SIMS.md`](docs/EXPANSION-SIMS.md) - hak akses per
    akun, preferensi tampilan, role tambahan, dan modul SIMS berikutnya.

------------------------------------------------------------------------

## Ringkasan Alur Singkat

1.  Admin menyiapkan yayasan, sekolah, tahun ajaran, kelas, peserta, dan
    akun.
2.  Admin/guru menyiapkan modul, topik, dan soal.
3.  Admin membuat tes, memilih kelas, menyusun komposisi soal, dan
    membuat token.
4.  Peserta login, memulai tes, menjawab, lalu hasil difinalisasi
    otomatis/manual.
5.  Admin mengevaluasi essay, mengaktifkan ranking, dan mencetak hasil.
6.  Guru mengisi raport; peserta melihat raport, prestasi, ranking, dan
    kelulusan.
7.  Orang tua memantau absensi, membayar biaya ujian, membayar/mencicil
    SPP, dan mengunggah bukti.
8.  Admin memverifikasi pembayaran CBT/SPP, mencetak kwitansi, dan
    mengelola tagihan SPP.

------------------------------------------------------------------------

# SIMS ENTERPRISE --- MASTER REQUIREMENTS & TECHNICAL BLUEPRINT

> **Penting --- status dokumen:** Bagian 1--15 mendeskripsikan aplikasi
> berdasarkan dokumentasi yang tersedia. Bagian 16--26 adalah rancangan
> target pengembangan. Daftar menu, tabel, proses, dan acceptance
> criteria pada bagian target **bukan pernyataan bahwa fitur sudah
> diimplementasikan**. Setiap item harus diturunkan menjadi backlog,
> desain teknis, migration, kode, test, dan bukti UAT.

## 16. Visi Produk dan Ruang Lingkup Enterprise

### 16.1 Visi

Mengembangkan SIMS CBT menjadi **School Information Management System
(SIMS) Enterprise** yang mengelola siklus operasional pendidikan dari
penerimaan peserta didik, kegiatan akademik, pembelajaran, ujian,
penilaian, administrasi keuangan, pengelolaan SDM, operasional sekolah,
komunikasi, sampai pelaporan yayasan.

Sistem ditujukan untuk: - Satu yayasan yang mengelola banyak sekolah. -
Banyak unit pendidikan dan jenjang dalam satu organisasi. - Sekolah
dengan kebijakan akademik dan biaya yang berbeda. - Pengguna dengan
tanggung jawab lintas sekolah maupun khusus satu sekolah. - Portal
terpisah untuk administrator/staff, guru, peserta didik, orang tua/wali,
dan calon peserta didik.

### 16.2 Prinsip produk

1.  **Multi-tenant aware:** setiap data organisasi memiliki batas akses
    yang jelas.
2.  **School configurable:** fitur, kalender, kurikulum, tarif, dan
    kebijakan dapat berbeda antar sekolah.
3.  **Role and scope based:** akses ditentukan oleh permission dan
    cakupan data, bukan hanya nama role.
4.  **Workflow driven:** proses penting memiliki status, validasi,
    persetujuan, dan histori.
5.  **Auditable:** perubahan data kritis dapat ditelusuri.
6.  **API ready:** integrasi eksternal menggunakan kontrak API yang
    terdokumentasi.
7.  **Backward compatible where practical:** pengembangan menjaga data
    dan fungsi lama melalui migrasi terencana.
8.  **Privacy by design:** data anak, keluarga, kesehatan, konseling,
    dan keuangan dibatasi sesuai kebutuhan kerja.
9.  **Accessible and responsive:** aplikasi dapat digunakan pada
    desktop, tablet, dan ponsel.
10. **Configuration over hardcoding:** kebijakan sekolah dibuat sebagai
    konfigurasi dengan default yang aman.

### 16.3 Jenjang dan variasi institusi

Model data perlu mendukung konfigurasi jenjang seperti PAUD/TK, SD/MI,
SMP/MTs, SMA/MA, SMK, serta unit pendidikan lain yang relevan. Struktur
kurikulum, istilah kelas, periode belajar, penilaian, dan dokumen
kelulusan tidak boleh diasumsikan seragam untuk semua jenjang.

### 16.4 Batas ruang lingkup

-   Sistem inti menangani data dan proses sekolah.
-   Integrasi pembayaran, WhatsApp, email, penyimpanan object, tanda
    tangan elektronik, dan layanan eksternal harus melalui
    adapter/provider.
-   Fitur yang memiliki implikasi regulasi atau akuntansi harus ditinjau
    oleh pemilik proses dan ahli terkait sebelum dipakai produksi.
-   Modul opsional dapat diaktifkan per yayasan/sekolah melalui feature
    configuration.

------------------------------------------------------------------------

## 17. Gap Analysis dan Risiko Sistem

Status berikut merupakan analisis terhadap README yang tersedia, bukan
hasil pemeriksaan source code atau database runtime.

  -------------------------------------------------------------------------------------
  Area               Kondisi yang terdokumentasi   Kebutuhan target   Prioritas
  ------------------ ----------------------------- ------------------ -----------------
  Yayasan dan        Multi-yayasan/multi-sekolah   Uji isolasi        P0
  sekolah            telah menjadi konsep inti     tenant,            
                                                   konsistensi scope, 
                                                   konfigurasi fitur  
                                                   dan konsolidasi    

  Identitas dan      Guard dan pengaturan          Permission         P0
  akses              role/level tersedia           granular, scope    
                                                   data, MFA,         
                                                   session/device     
                                                   control, SoD       

  Audit              Belum dijelaskan sebagai      Audit append-only  P0
                     audit trail menyeluruh        untuk aktivitas    
                                                   kritis dan         
                                                   approval history   

  PPDB               Belum menjadi siklus          Pendaftaran,       P1
                     penerimaan lengkap            verifikasi,        
                                                   seleksi,           
                                                   pengumuman, daftar 
                                                   ulang, konversi    

  Data siswa         Manajemen peserta tersedia    Identitas siswa    P0
                                                   terpisah dari      
                                                   akun, enrollment   
                                                   dan histori lintas 
                                                   tahun/sekolah      

  Kurikulum          Modul/topik CBT tersedia      Kurikulum          P1
                                                   berversi, struktur 
                                                   mapel, capaian,    
                                                   alokasi dan        
                                                   penugasan guru     

  Jadwal             Belum terdokumentasi sebagai  Jadwal             P1
                     scheduler akademik lengkap    kelas/guru/ruang   
                                                   dan validasi       
                                                   bentrok            

  Pembelajaran       CBT tersedia                  LMS, materi,       P2
                                                   tugas,             
                                                   pengumpulan,       
                                                   feedback, dan      
                                                   progress           

  CBT                Bank soal, ujian, monitoring  Reliability        P0
                     dan hasil terdokumentasi      autosave, resume,  
                                                   concurrency,       
                                                   incident,          
                                                   moderation dan     
                                                   publikasi          

  Penilaian/raport   Raport dan kelulusan dasar    Workflow nilai,    P1
                     tersedia                      versioning,        
                                                   approval, koreksi  
                                                   dan histori        
                                                   publikasi          

  Absensi            Absensi peserta tersedia      Absensi per        P1
                                                   jadwal/mapel,      
                                                   izin, dispensasi,  
                                                   koreksi dan        
                                                   notifikasi         

  Keuangan           Tagihan, pembayaran dan       Refund/reversal,   P0
                     alokasi dasar tersedia        rekonsiliasi,      
                                                   kas/bank,          
                                                   anggaran, ledger   
                                                   dan tutup buku     

  PPDB/keuangan      Pembayaran CBT terdokumentasi Payment policy     P0
                                                   configurable,      
                                                   verifikasi,        
                                                   idempotency dan    
                                                   rekonsiliasi       

  SDM                Data guru dasar tersedia      Pegawai, kontrak,  P2
                                                   cuti, payroll dan  
                                                   kinerja            

  Operasional        Belum lengkap                 Perpustakaan,      P2
                                                   inventaris, UKS    
                                                   dan surat menyurat 

  Komunikasi         Belum menjadi notification    Template, channel, P1
                     center menyeluruh             retry, delivery    
                                                   status dan         
                                                   preference         

  Dokumen            Upload media/berkas terbatas  Document registry, P1
                     pada fitur tertentu           private storage,   
                                                   versioning dan     
                                                   retention          

  Integrasi          Belum dijelaskan menyeluruh   API versioning,    P1
                                                   webhook signing,   
                                                   idempotency dan    
                                                   integration logs   

  Reliability        Belum dijelaskan sebagai      Backup restore     P0
                     operasi produksi              test, monitoring,  
                                                   queue health, DR   
                                                   dan load test      

  Data analytics     Laporan akademik/CBT dasar    Dashboard lintas   P2
                     tersedia                      sekolah, definisi  
                                                   KPI dan scheduled  
                                                   report             
  -------------------------------------------------------------------------------------

### 17.1 Risiko proses yang harus dicegah

-   Siswa berpindah kelas dengan menimpa histori kelas sebelumnya.
-   Akun login disamakan dengan entitas siswa/guru sehingga satu orang
    tidak dapat memiliki beberapa relasi atau peran.
-   Guru dapat mengubah nilai setelah raport diterbitkan tanpa proses
    koreksi.
-   Pembayaran yang sudah sukses diubah atau dihapus langsung.
-   Callback payment gateway diproses lebih dari satu kali.
-   Pengguna yayasan/sekolah dapat mengakses data tenant lain melalui
    manipulasi ID.
-   Approval dilakukan oleh pembuat transaksi sendiri pada proses yang
    mewajibkan pemisahan tugas.
-   Ujian mengandalkan jam browser atau kehilangan jawaban saat koneksi
    terputus.
-   Penghapusan data master menghilangkan histori transaksi.
-   Import massal menyimpan sebagian data tanpa laporan baris gagal dan
    mekanisme rollback yang jelas.

### 17.2 Prinsip perbaikan

Setiap proses kritis harus memiliki: - Status yang eksplisit dan
transisi status yang tervalidasi. - Actor, waktu, alasan, dan referensi
transaksi. - Validasi tenant dan permission di server. - Idempotency
untuk request/transaksi yang dapat terkirim ulang. - Database
transaction untuk operasi multi-tabel. - Histori perubahan yang tidak
bergantung pada data aktif. - Mekanisme pembatalan yang sesuai domain,
bukan hard delete.

------------------------------------------------------------------------

## 18. Arsitektur Menu Target

Menu di bawah merupakan **target information architecture**. Menu
ditampilkan berdasarkan permission, scope organisasi, feature flag, dan
konteks sekolah aktif. Jangan menampilkan semua menu ke semua role.

### 18.1 Administrator Yayasan / Super Admin

**A. Overview** - Dashboard Yayasan - Executive Summary - KPI
Konsolidasi - Notification Center - Approval Inbox - System Status

**B. Organisasi** - Profil Yayasan - Struktur Organisasi - Daftar
Sekolah - Unit Pendidikan - Jenjang Pendidikan - Tahun Ajaran -
Semester/Periode - Kalender Akademik - Hari Libur - Konfigurasi
Sekolah - Feature Configuration

**C. PPDB** - Dashboard PPDB - Periode dan Gelombang - Jalur
Pendaftaran - Formulir - Calon Peserta Didik - Dokumen Persyaratan -
Verifikasi Berkas - Seleksi dan Tes - Wawancara - Hasil Seleksi -
Pengumuman - Daftar Ulang - Pembayaran PPDB - Kuota - Konversi Menjadi
Siswa - Laporan PPDB

**D. Data Master** - Data Siswa - Data Orang Tua/Wali - Data Guru - Data
Pegawai - Data Alumni - Data Kelas/Rombongan Belajar - Data Ruangan -
Data Mata Pelajaran - Data Referensi - Import Center - Export Center

**E. Akademik** - Kurikulum - Versi Kurikulum - Struktur Kurikulum -
Capaian Pembelajaran - Tujuan Pembelajaran - Mata Pelajaran - Program
Tahunan/Semester - Modul Ajar - Penugasan Guru - Pembagian Beban
Mengajar - Jadwal Pelajaran - Jadwal Ruangan - Kalender Kegiatan -
Kegiatan Belajar - Penilaian - Leger - Raport - Approval Raport -
Kenaikan Kelas - Mutasi Siswa - Kelulusan - Alumni

**F. Kehadiran** - Absensi Siswa - Absensi Per Mata Pelajaran - Absensi
Guru - Absensi Pegawai - Izin dan Sakit - Dispensasi - Koreksi
Kehadiran - Rekap dan Analitik Kehadiran

**G. Pembelajaran** - Kelas Online - Materi Pembelajaran - Video/Media -
Tugas - Pengumpulan Tugas - Penilaian Tugas - Forum Diskusi - Quiz -
Learning Progress - Resource Library

**H. Ujian dan CBT** - Dashboard CBT - Bank Soal -
Kategori/Topik/Kompetensi - Paket Ujian - Blueprint/Komposisi Soal -
Jadwal dan Sesi - Peserta Ujian - Token - Monitoring - Incident Log -
Hasil dan Attempt - Penilaian Otomatis - Penilaian Essay - Moderasi
Nilai - Publikasi Hasil - Analisis Butir Soal - Ranking sesuai
kebijakan - Cetak dan Export

**I. Penilaian dan Raport** - Komponen Penilaian - Bobot Penilaian -
Input Nilai - Rekap Nilai - Validasi Nilai - Pengajuan Raport - Approval
Raport - Publikasi Raport - Koreksi Nilai - Leger - Kenaikan Kelas -
Kelulusan - SKL - Arsip Akademik

**J. Keuangan** - Dashboard Keuangan - Kategori Biaya - Struktur Tarif -
Generate Tagihan - Tagihan Siswa - Tagihan PPDB - Pembayaran - Payment
Allocation - Cicilan - Diskon dan Beasiswa - Pembebasan Biaya - Denda -
Kwitansi - Refund - Reversal - Kas dan Bank - Penerimaan - Pengeluaran -
Permintaan Pembayaran - Approval Pengeluaran - Rekonsiliasi Bank/Payment
Gateway - Anggaran - Tutup Periode - Laporan Tunggakan - Konsolidasi
Keuangan

**K. Akuntansi** - Chart of Accounts - Jurnal Umum - Jurnal Otomatis -
Buku Besar - Neraca Saldo - Laporan Aktivitas/Laba Rugi sesuai entitas -
Neraca - Arus Kas - Periode Fiskal - Rekonsiliasi - Closing - Audit
Keuangan

**L. Kesiswaan** - Prestasi - Pelanggaran - Poin Pembinaan - Bimbingan
Konseling - Ekstrakurikuler - Kegiatan Siswa - Beasiswa - Organisasi
Siswa

**M. SDM** - Data Pegawai - Jabatan dan Unit - Kontrak - Riwayat
Pendidikan - Sertifikasi - Penugasan - Absensi Pegawai - Cuti dan Izin -
Lembur - Payroll - Tunjangan dan Potongan - Slip Gaji - Kinerja -
Pelatihan - Mutasi dan Terminasi

**N. Operasional** - Perpustakaan - Katalog dan Eksemplar -
Peminjaman/Pengembalian - Denda Perpustakaan - Inventaris/Aset -
Pengadaan - Distribusi Aset - Pemeliharaan - Stock Opname - Penghapusan
Aset - Ruangan dan Fasilitas - UKS - Surat Masuk/Keluar - Manajemen
Dokumen

**O. Komunikasi** - Pengumuman Yayasan - Pengumuman Sekolah -
Broadcast - Pesan Internal - Inbox - Template Notifikasi -
Email/WhatsApp/Push Provider - Delivery Log - Notification Preferences

**P. Laporan dan Analitik** - Laporan PPDB - Laporan Siswa - Laporan
Akademik - Laporan Kehadiran - Laporan CBT - Laporan Raport - Laporan
Keuangan - Laporan Akuntansi - Laporan SDM - Laporan Operasional -
Konsolidasi Yayasan - Scheduled Reports - Export Center

**Q. System Administration** - User Management - Role Management -
Permission Catalog - Role Assignment - User Permission Override -
Approval Workflow Configuration - Audit Trail - Login dan Security
Events - Session/Device Management - API Client dan API Key - Webhook -
Integration Settings - Storage Settings - Email Settings -
Queue/Scheduler Monitoring - Application Health - Error Logs -
Backup/Restore Status - Data Retention - Privacy Settings - Maintenance
Configuration

### 18.2 Portal Guru

-   Dashboard Guru
-   Monitoring Absensi Ujian (pra-ujian): daftar peserta terdaftar,
    sudah hadir, belum hadir, di luar radius, dan status validasi
    lokasi.
-   Monitoring Ujian Real-time: status belum mulai, sedang mengerjakan,
    selesai, terputus, dan insiden sesuai hak akses.
-   Rekap Absensi Harian kelas yang diampu: hadir, terlambat, izin,
    sakit, dispensasi, dan alpha.
-   Koreksi absensi dengan alasan dan histori perubahan.
-   Jadwal Mengajar
-   Kelas yang Diampu
-   Daftar Siswa
-   Presensi Kelas
-   Materi Pembelajaran
-   Tugas dan Pengumpulan
-   Input Nilai
-   Penilaian/CBT sesuai penugasan
-   Rekap Nilai
-   Pengajuan Raport
-   Catatan Wali Kelas (khusus wali kelas)
-   Pengumuman
-   Pesan
-   Profil dan Dokumen Pribadi

Guru hanya melihat kelas, siswa, dan mata pelajaran yang ditugaskan pada
periode terkait.

### 18.3 Portal Siswa/Peserta

-   Dashboard
-   Absensi Ujian: wajib melakukan check-in sebelum memulai ujian yang
    menerapkan kebijakan kehadiran lokasi.
-   Absensi Harian: check-in/check-out sesuai kebijakan sekolah, melihat
    riwayat, dan mengajukan izin/sakit.
-   Profil
-   Jadwal Pelajaran
-   Kalender Akademik
-   Materi
-   Tugas
-   Pengumpulan Tugas
-   Ujian/CBT
-   Riwayat Ujian
-   Nilai yang sudah dipublikasikan
-   Raport yang sudah dipublikasikan
-   Absensi
-   Prestasi
-   Pengumuman
-   Perpustakaan
-   Dokumen
-   Notifikasi

### 18.4 Portal Orang Tua/Wali

-   Dashboard Keluarga
-   Pantau absensi harian anak dan status kehadiran ujian sesuai
    kebijakan privasi sekolah.
-   Mengajukan izin/sakit dan memantau status persetujuannya.
-   Pilih Anak
-   Profil dan Dokumen Anak
-   Jadwal
-   Absensi
-   Nilai yang dipublikasikan
-   Raport
-   Tagihan
-   Pembayaran
-   Riwayat Transaksi
-   Kwitansi
-   Pengajuan Izin
-   Pengumuman
-   Pesan Guru/Wali Kelas
-   Konsultasi sesuai kebijakan
-   Notifikasi dan Preferensi

Relasi orang tua dengan anak harus mendukung satu wali untuk beberapa
anak dan beberapa wali untuk satu anak, dengan tipe hubungan serta
status verifikasi.

### 18.5 Portal PPDB

-   Informasi Sekolah dan Program
-   Registrasi Akun Pendaftar
-   Formulir Pendaftaran
-   Data Calon Siswa
-   Data Orang Tua/Wali
-   Upload Dokumen
-   Status Verifikasi
-   Jadwal Tes/Wawancara
-   Hasil Seleksi
-   Tagihan Daftar Ulang
-   Pembayaran
-   Konfirmasi dan Bukti Penerimaan
-   Pusat Bantuan

### 18.6 Prinsip navigasi

-   Sidebar harus dikelompokkan per domain dan mendukung pencarian menu.
-   Menu yang tidak memiliki permission tidak dikirim sebagai navigasi
    aktif; endpoint tetap harus memvalidasi akses.
-   Konteks yayasan/sekolah aktif harus terlihat jelas.
-   Perubahan konteks sekolah harus memvalidasi ulang scope dan
    membersihkan data cache yang terkait.
-   Menu opsional ditampilkan hanya jika modul aktif untuk tenant
    terkait.
-   Portal publik/pendaftar dipisahkan dari panel internal.

------------------------------------------------------------------------

## 19. Business Process dan Workflow

### 19.1 Siklus PPDB

1.  Admin membuat periode, gelombang, jalur, kuota, formulir,
    persyaratan, dan aturan seleksi.
2.  Calon siswa membuat akun dan memilih sekolah/program.
3.  Pendaftar mengisi data calon siswa dan wali serta mengunggah
    dokumen.
4.  Sistem menyimpan pendaftaran dengan status `draft` atau `submitted`.
5.  Petugas memeriksa kelengkapan dan memberi status `verified`,
    `revision_required`, atau `rejected`.
6.  Pendaftar melengkapi revisi jika diminta.
7.  Panitia menjalankan tes/wawancara dan menginput hasil.
8.  Pejabat berwenang mengesahkan hasil seleksi.
9.  Sistem memublikasikan hasil sesuai jadwal.
10. Calon siswa yang diterima melakukan daftar ulang dan memenuhi
    kebijakan pembayaran.
11. Setelah seluruh syarat terpenuhi, petugas mengonversi pendaftaran
    menjadi enrollment siswa.
12. Sistem membuat nomor induk sesuai aturan sekolah, menghubungkan
    wali, dan menyiapkan akun portal.
13. Seluruh histori pendaftaran tetap disimpan dan dapat ditelusuri.

**Kontrol:** pendaftaran tidak boleh menjadi siswa aktif hanya karena
formulir tersimpan. Konversi harus idempotent agar tidak membuat siswa
ganda.

### 19.2 Siklus siswa dan tahun ajaran

1.  Identitas siswa dibuat satu kali dan memiliki identifier internal
    yang stabil.
2.  Enrollment menghubungkan siswa ke sekolah, jenjang, program, dan
    periode.
3.  Penempatan kelas disimpan sebagai histori, bukan menimpa kelas lama.
4.  Pada akhir tahun, sekolah menjalankan rapat/keputusan kenaikan
    kelas.
5.  Sistem membuat daftar usulan: naik, tinggal, lulus, pindah, atau
    status lain sesuai aturan.
6.  Wali kelas dan pejabat terkait melakukan review.
7.  Keputusan disetujui dan diposting.
8.  Sistem membuat enrollment/keanggotaan kelas periode berikutnya.
9.  Siswa yang pindah/lulus tetap memiliki histori akademik dan
    transaksi.
10. Koreksi keputusan menggunakan proses koreksi terotorisasi dan audit.

### 19.3 Siklus akademik

1.  Admin menetapkan tahun ajaran dan semester.
2.  Admin memilih versi kurikulum yang berlaku untuk
    sekolah/jenjang/program.
3.  Sekolah memetakan struktur kurikulum, mata pelajaran, capaian, dan
    alokasi jam.
4.  Admin membentuk kelas/rombongan belajar.
5.  Admin menugaskan guru ke mata pelajaran, kelas, dan periode.
6.  Penyusun jadwal mengalokasikan slot waktu, guru, kelas, dan ruang.
7.  Sistem mendeteksi bentrok guru, kelas, ruang, serta batas beban
    mengajar.
8.  Jadwal disahkan dan dipublikasikan.
9.  Guru mengelola kegiatan belajar, materi, tugas, dan presensi.
10. Guru memasukkan penilaian berdasarkan komponen dan kebijakan
    kurikulum.
11. Nilai divalidasi, diajukan, disetujui, dan dipublikasikan.
12. Raport dibuat dari data nilai dan catatan yang sudah tervalidasi.
13. Raport dikunci setelah publikasi; koreksi memakai workflow khusus.

### 19.4 Siklus absensi harian

1.  Sistem memperoleh daftar peserta dari enrollment, kelas aktif,
    kalender sekolah, dan sesi/jadwal yang berlaku.
2.  Sekolah mengatur metode presensi: guru/petugas, mandiri dengan GPS,
    atau kombinasi; check-in/check-out dapat diaktifkan terpisah.
3.  Untuk presensi mandiri berbasis lokasi, perangkat meminta izin
    lokasi dan mengirim latitude, longitude, akurasi, serta waktu
    pengambilan.
4.  Server menghitung jarak ke titik lokasi sekolah yang aktif.
    Kebijakan awal radius adalah **50 meter**, dapat diubah oleh admin
    sekolah sesuai kebutuhan.
5.  Jika lokasi valid dan berada dalam radius, sistem menerima presensi.
    Jika di luar radius, sistem menolak check-in GPS dan menampilkan
    alasan serta jarak perkiraan.
6.  Guru/wali kelas/petugas dapat melakukan presensi manual atau koreksi
    sesuai permission; alasan, actor, waktu, dan nilai sebelum/sesudah
    wajib dicatat.
7.  Status kehadiran: hadir, terlambat, izin, sakit, dispensasi, alpha,
    atau status konfigurasi lain.
8.  Pengajuan izin orang tua masuk ke antrean persetujuan jika kebijakan
    mengharuskannya.
9.  Rekap menghitung kehadiran berdasarkan kalender efektif dan sesi
    yang sah.
10. Notifikasi dikirim sesuai preference dan aturan sekolah.
11. Koreksi rekap tidak menghapus histori presensi awal.

### 19.4.1 Kebijakan lokasi dan validasi GPS absensi harian

-   Admin sekolah menetapkan titik lokasi (latitude/longitude) melalui
    peta atau input koordinat yang diverifikasi.
-   Radius default adalah **50 meter** dari titik sekolah; radius
    disimpan sebagai konfigurasi lokasi, bukan angka yang ditanam
    permanen di kode.
-   Satu sekolah dapat memiliki beberapa titik presensi (misalnya
    kampus/gedung berbeda) bila memang diperlukan dan ditetapkan admin.
-   Validasi jarak dilakukan di server dengan perhitungan
    geodesik/Haversine; jangan mempercayai hasil perhitungan jarak dari
    browser.
-   Simpan koordinat, akurasi GPS, jarak hasil hitung, waktu server,
    metode, status validasi, dan referensi lokasi.
-   Jika akurasi GPS lebih buruk dari ambang yang dikonfigurasi, minta
    peserta mengambil lokasi ulang atau gunakan jalur bantuan petugas.
-   Browser GPS tidak menjamin anti-spoofing. Tandai anomali yang
    terdeteksi dan sediakan verifikasi petugas; jangan menyatakan lokasi
    browser sebagai bukti anti-pemalsuan mutlak.
-   Jika perangkat tidak mendukung/menolak GPS, peserta menghubungi
    petugas untuk verifikasi manual sesuai SOP. Jangan otomatis
    mengubahnya menjadi hadir.
-   Kebijakan privasi harus membatasi pengambilan lokasi hanya saat
    proses presensi, bukan pelacakan terus-menerus.

2.  Guru/petugas melakukan presensi pada sesi yang benar.
3.  Status kehadiran: hadir, terlambat, izin, sakit, dispensasi, alpha,
    atau status konfigurasi lain.
4.  Perubahan setelah submit harus mencatat alasan dan actor.
5.  Pengajuan izin orang tua masuk ke antrean persetujuan jika kebijakan
    mengharuskannya.
6.  Rekap menghitung kehadiran berdasarkan kalender efektif dan sesi
    yang sah.
7.  Notifikasi dikirim sesuai preference dan aturan sekolah.
8.  Koreksi rekap tidak menghapus histori presensi awal.

### 19.5 Siklus ujian CBT

1.  Guru/admin menyusun soal dengan metadata mapel, topik, kompetensi,
    tingkat kesulitan, dan versi.
2.  Soal direview sebelum digunakan.
3.  Paket ujian menyimpan snapshot soal dan opsi agar perubahan bank
    soal tidak mengubah ujian yang sudah disiapkan.
4.  Admin menetapkan jadwal, durasi, peserta, attempt policy, token, dan
    aturan akses.
5.  Sistem memvalidasi prasyarat peserta, termasuk kebijakan pembayaran
    bila diberlakukan.
6.  Peserta memulai attempt; server mencatat waktu mulai dan deadline.
7.  Jawaban disimpan secara berkala dengan nomor versi/urutan dan
    mekanisme retry.
8.  Request simpan/finalisasi harus aman terhadap pengiriman ulang.
9.  Jika koneksi terputus, peserta dapat melanjutkan sesuai aturan dan
    sisa waktu server.
10. Sistem mencatat insiden teknis dan aktivitas keamanan yang relevan.
11. Jawaban objektif dinilai otomatis; essay menunggu penilai.
12. Hasil melewati review/moderasi jika diperlukan.
13. Nilai dipublikasikan sesuai jadwal dan hak akses.
14. Analisis butir soal dibuat dari data attempt yang valid.
15. Attempt, snapshot, jawaban, dan log tidak dihapus ketika ujian
    ditutup.

**Aturan penting:** waktu resmi ujian ditentukan server, bukan jam
perangkat peserta. Kebijakan fullscreen, pembatasan perangkat, atau
deteksi perilaku hanya menjadi kontrol pendukung dan tidak boleh
dianggap bukti kecurangan tunggal.

### 19.5.1 Siklus absensi sebelum ujian CBT (geofencing)

1.  Admin sekolah memverifikasi titik lokasi ujian/sekolah pada peta dan
    menetapkan radius default **50 meter**.
2.  Saat membuat atau mengatur sesi ujian, admin memilih apakah absensi
    pra-ujian diwajibkan, lokasi yang digunakan, rentang waktu check-in,
    dan kebijakan bantuan manual.
3.  Peserta membuka detail ujian dan melakukan check-in sebelum tombol
    mulai ujian diaktifkan.
4.  Browser/perangkat meminta izin GPS; peserta mengirim koordinat dan
    akurasi lokasi.
5.  Server memvalidasi tenant/sekolah, sesi ujian, waktu check-in,
    identitas peserta, status attempt, akurasi GPS, dan jarak dari titik
    lokasi.
6.  Jika jarak \<= radius 50 meter dan seluruh prasyarat valid, sistem
    mencatat `present` dan membuka prasyarat mulai ujian.
7.  Jika di luar radius, GPS tidak tersedia, atau akurasi tidak memenuhi
    kebijakan, sistem menolak check-in otomatis dan menampilkan
    instruksi tindak lanjut. Petugas berwenang dapat memberi override
    manual dengan alasan dan audit.
8.  Guru memonitor peserta per sesi: belum check-in, hadir valid, di
    luar radius/ditolak, override manual, sudah mulai, sedang
    mengerjakan, terputus, dan selesai.
9.  Dashboard guru menampilkan jumlah dan daftar peserta, waktu
    check-in, jarak, akurasi GPS, metode check-in, serta status ujian.
    Lokasi rinci hanya terlihat oleh petugas yang diberi kewenangan.
10. Peserta yang tidak memenuhi syarat absensi tidak dapat memulai ujian
    jika kebijakan wajib aktif. Ujian yang tidak mewajibkan geofencing
    tetap mengikuti aturan akses CBT yang berlaku.
11. Semua keputusan check-in, penolakan, percobaan ulang, dan override
    dicatat pada audit log.

**Aturan keamanan:** absensi pra-ujian dan status attempt CBT adalah dua
data berbeda. Check-in tidak otomatis membuat attempt berjalan; attempt
baru dibuat saat peserta benar-benar memulai ujian. Pengiriman check-in
berulang harus idempotent untuk kombinasi sesi ujian dan peserta.

### 19.6 Siklus pembayaran sekolah

1.  Admin membuat komponen biaya, tarif, periode, aturan jatuh tempo,
    dan penerima.
2.  Sistem menghasilkan tagihan berdasarkan enrollment dan kebijakan
    siswa.
3.  Tagihan memiliki item, diskon/beasiswa, denda, dan saldo.
4.  Orang tua memilih kanal pembayaran.
5.  Transaksi dibuat dengan idempotency key dan status awal.
6.  Pembayaran gateway diverifikasi melalui callback bertanda tangan
    atau rekonsiliasi resmi.
7.  Pembayaran manual diverifikasi petugas yang memiliki permission.
8.  Sistem mengalokasikan dana ke satu atau lebih item tagihan secara
    konsisten.
9.  Kwitansi diterbitkan setelah status pembayaran sah.
10. Rekonsiliasi membandingkan transaksi internal, provider, dan mutasi
    bank.
11. Refund/reversal dilakukan sebagai transaksi baru yang mereferensikan
    transaksi asal, disertai alasan dan approval.
12. Tutup kas/periode mengunci transaksi sesuai kebijakan.
13. Semua perubahan memiliki audit dan laporan.

**Invariant keuangan:** - Total alokasi tidak boleh melebihi jumlah
pembayaran yang dapat dialokasikan. - Callback duplikat tidak boleh
menggandakan pembayaran. - Pembayaran sukses tidak boleh diedit/hard
delete. - Refund/reversal harus mengacu pada transaksi asal dan tidak
boleh melebihi nilai yang dapat dikembalikan. - Jurnal yang sudah
diposting dikoreksi dengan jurnal pembalik, bukan dihapus.

### 19.7 Approval workflow generik

Status dasar yang dapat dikonfigurasi:
`draft -> submitted -> in_review -> approved/rejected -> published/posted`.

Tidak semua modul harus menggunakan seluruh status. Setiap workflow
harus mendefinisikan: - Jenis dokumen/transaksi. - Pemohon dan unit
pemilik. - Tahap dan approver. - Batas nominal/aturan eskalasi bila
relevan. - SLA dan reminder. - Delegasi approver. - Alasan penolakan. -
Conflict-of-interest / self-approval policy. - Riwayat keputusan. -
Aturan perubahan setelah approval.

### 19.8 Notifikasi

Event penting dapat memicu notifikasi, misalnya pendaftaran perlu
revisi, tagihan terbit, pembayaran diterima, siswa tidak hadir, tugas
jatuh tempo, jadwal berubah, nilai/raport dipublikasikan, dan approval
menunggu.

Notifikasi harus: - Memiliki template dan channel. - Menghormati
preferensi penerima dan kebijakan sekolah. - Menyimpan delivery
status. - Mendukung retry dan dead-letter handling. - Tidak mengirim
data sensitif berlebihan pada pesan eksternal.

------------------------------------------------------------------------

## 20. Role, Permission, dan Data Scope

### 20.1 Role target

  -----------------------------------------------------------------------
  Role                    Scope default           Tanggung jawab utama
  ----------------------- ----------------------- -----------------------
  Platform Super Admin    Platform                Operasional teknis
                                                  lintas tenant; akses
                                                  data bisnis dibatasi
                                                  dan diaudit

  Admin Yayasan           Yayasan                 Konfigurasi dan
                                                  monitoring sekolah
                                                  dalam yayasan

  Pimpinan Yayasan        Yayasan                 Dashboard, kebijakan,
                                                  approval strategis

  Admin Sekolah           Satu/sejumlah sekolah   Administrasi
                          yang ditugaskan         operasional sekolah

  Kepala Sekolah          Sekolah                 Supervisi, persetujuan,
                                                  dan monitoring

  Wakil Kepala Sekolah    Bidang/sekolah          Pengelolaan bidang yang
                                                  ditugaskan

  Tata Usaha              Sekolah                 Administrasi data dan
                                                  dokumen

  Operator                Sekolah/modul           Input dan pemeliharaan
                                                  data sesuai penugasan

  Panitia PPDB            Periode/sekolah         Pendaftaran dan seleksi

  Guru                    Penugasan               Kelas, mapel, presensi,
                                                  materi, nilai

  Wali Kelas              Kelas/periode           Administrasi kelas dan
                                                  raport

  Bendahara               Sekolah/yayasan         Keuangan dan laporan
                                                  sesuai otorisasi

  Kasir                   Lokasi kas/sekolah      Penerimaan pembayaran

  Approver Keuangan       Batas kewenangan        Approval
                                                  pengeluaran/refund

  Guru BK                 Siswa/kasus yang        Konseling dengan akses
                          ditugaskan              sangat terbatas

  Petugas UKS             Sekolah                 Data layanan UKS sesuai
                                                  kebutuhan

  Pustakawan              Perpustakaan/sekolah    Sirkulasi buku

  Petugas Inventaris      Sekolah                 Aset dan stock opname

  HRD                     Yayasan/sekolah         Kepegawaian dan payroll

  Orang Tua/Wali          Anak yang terverifikasi Informasi dan pengajuan
                                                  terkait anak

  Siswa                   Identitas/enrollment    Aktivitas pembelajaran
                          sendiri                 pribadi

  Auditor                 Scope penugasan         Read-only audit dan
                                                  evidence
  -----------------------------------------------------------------------

Role adalah kumpulan permission; role tidak boleh menjadi satu-satunya
kontrol akses.

### 20.2 Permission granular

Gunakan permission berbasis resource dan action, misalnya: -
`students.view`, `students.create`, `students.update`,
`students.archive`, `students.export` - `admissions.verify`,
`admissions.evaluate`, `admissions.publish`, `admissions.convert` -
`grades.view`, `grades.create`, `grades.submit`, `grades.approve`,
`grades.publish`, `grades.correct` - `payments.view`,
`payments.receive`, `payments.verify`, `payments.refund`,
`payments.reverse` - `reports.view`, `reports.export` -
`users.assign_role`, `roles.manage`, `permissions.manage`

Action standar yang dapat dipakai: `view`, `create`, `update`,
`archive`, `delete` (sangat terbatas), `import`, `export`, `submit`,
`approve`, `reject`, `verify`, `publish`, `print`, `refund`, `reverse`,
`manage`.

### 20.3 Data scope

Permission harus dikombinasikan dengan scope: - Platform. - Yayasan. -
Sekolah. - Unit pendidikan. - Tahun ajaran/periode. - Kelas. - Mata
pelajaran/penugasan. - Siswa tertentu. - Kas/lokasi. - Kasus/dokumen
yang ditugaskan.

Scope harus diterapkan di server pada setiap query dan endpoint.
Menyembunyikan tombol atau menu bukan kontrol keamanan.

### 20.4 Separation of Duties (SoD)

Contoh kebijakan: - Petugas kasir menerima pembayaran; approver berbeda
menangani refund bila diwajibkan. - Guru memasukkan nilai; approver
memvalidasi/publikasi sesuai struktur sekolah. - Pemohon pengeluaran
tidak menyetujui permintaannya sendiri. - Admin yang mengubah role
sensitif tidak boleh menghapus jejak perubahan tersebut. - Pengelola
data konseling tidak otomatis memperoleh akses ke seluruh catatan
kesehatan.

### 20.5 Matriks akses ringkas

  ---------------------------------------------------------------------------------------------------------------
  Modul        Admin Yayasan          Admin Sekolah  Guru          Bendahara/Kasir   Orang Tua        Siswa
  ------------ ---------------------- -------------- ------------- ----------------- ---------------- -----------
  Organisasi   Kelola scope yayasan   Lihat/kelola   Lihat         Lihat terbatas    \-               \-
                                      sekolah        terbatas                                         

  PPDB         Monitoring/approval    Kelola sekolah Sesuai        Verifikasi biaya  Pendaftar/wali   \-
                                                     penugasan                                        

  Data siswa   Scope yayasan          Sekolah        Kelas         Data minimum      Anak terhubung   Diri
                                                     ditugaskan    untuk billing                      sendiri

  Akademik     Monitoring             Kelola         Penugasan     \-                Lihat publikasi  Lihat milik
                                                                                                      sendiri

  CBT          Monitoring             Kelola         Buat/kelola   Verifikasi biaya  Status terbatas  Attempt
                                                     sesuai        jika aktif                         sendiri
                                                     assignment                                       

  Keuangan     Konsolidasi/approval   Scope sekolah  \-            Sesuai fungsi     Tagihan anak     Informasi
                                                                                                      terbatas

  BK/UKS       Akses khusus sesuai    Akses khusus   Tidak         \-                Data yang        Data
               kebijakan                             otomatis                        diizinkan        sendiri
                                                                                                      sesuai
                                                                                                      kebijakan

  System       Kelola platform/tenant User sekolah   Profil        Profil sendiri    Profil sendiri   Profil
               sesuai tugas                          sendiri                                          sendiri
  ---------------------------------------------------------------------------------------------------------------

Matriks ini hanya baseline. Permission final harus disusun per use case,
bukan diterapkan sebagai akses otomatis.

------------------------------------------------------------------------

## 21. Target Domain dan Database

### 21.1 Prinsip desain database

1.  Gunakan identifier internal yang stabil; jangan menjadikan NIS/NISN
    sebagai primary key.
2.  Pisahkan `users` (akun autentikasi) dari entitas `students`,
    `guardians`, `teachers`, dan `employees`.
3.  Relasi orang tua-anak harus many-to-many dengan atribut hubungan dan
    verifikasi.
4.  Simpan enrollment dan histori kelas berdasarkan periode.
5.  Simpan versi kurikulum, komponen nilai, snapshot ujian, dan
    publikasi raport.
6.  Gunakan foreign key, unique constraint, index, dan transaksi
    database.
7.  Data tenant harus mempunyai jalur relasi yang dapat divalidasi.
8.  Jangan menambahkan `foundation_id`/`school_id` secara membabi buta
    ke semua tabel; tentukan ownership dan inheritance setiap domain.
9.  Gunakan soft delete hanya untuk data yang memang boleh diarsipkan;
    transaksi finansial dan audit tidak dihapus.
10. Semua waktu disimpan konsisten (umumnya UTC di storage) dan
    ditampilkan berdasarkan timezone sekolah; tetapkan kebijakan ini
    sebelum implementasi.

### 21.2 Domain dan kandidat tabel

Nama berikut adalah usulan naming, bukan tabel yang sudah tersedia.

  -----------------------------------------------------------------------------
  Domain                  Prefix                  Kandidat entitas
  ----------------------- ----------------------- -----------------------------
  Identity & Access       `iam_`                  users, roles, permissions,
                                                  role_assignments,
                                                  user_scopes, sessions,
                                                  devices, MFA factors

  Organization            `org_`                  foundations, schools,
                                                  school_units,
                                                  education_levels,
                                                  academic_years, terms,
                                                  calendar_events

  Master Data             `mst_`                  addresses, regions,
                                                  religions/references only if
                                                  legally required,
                                                  document_types, lookup_values

  Admission               `adm_`                  periods, waves, pathways,
                                                  applicants, applications,
                                                  application_documents,
                                                  verifications, selections,
                                                  interviews, results,
                                                  re-enrollments

  Student                 `stu_`                  students, guardians,
                                                  guardian_student,
                                                  enrollments,
                                                  class_memberships, transfers,
                                                  promotions, graduations,
                                                  student_documents

  Curriculum              `cur_`                  curricula,
                                                  curriculum_versions,
                                                  curriculum_structures,
                                                  subjects, learning_outcomes,
                                                  learning_objectives,
                                                  subject_allocations

  Academic                `acd_`                  classes, class_members,
                                                  teacher_assignments,
                                                  schedules, rooms,
                                                  teaching_sessions,
                                                  grade_components, grades,
                                                  report_cards,
                                                  report_approvals

  Attendance              `att_`                  attendance_locations,
                                                  attendance_policies,
                                                  attendance_sessions,
                                                  attendance_records,
                                                  attendance_location_checks,
                                                  attendance_requests,
                                                  attendance_corrections,
                                                  attendance_audit_events

  Learning                `lms_`                  courses, lessons,
                                                  learning_materials,
                                                  assignments, submissions,
                                                  feedback, discussions,
                                                  progress

  Examination             `exam_`                 question_banks, questions,
                                                  question_versions, options,
                                                  exam_blueprints, exams,
                                                  exam_sessions, attempts,
                                                  answers, incidents, results

  Assessment              `asm_`                  assessment_schemes,
                                                  assessment_components,
                                                  score_entries,
                                                  moderation_records,
                                                  publications

  Finance                 `fin_`                  fee_types, fee_structures,
                                                  bills, bill_items, payments,
                                                  allocations, refunds,
                                                  reversals, cash_accounts,
                                                  reconciliations

  Accounting              `acc_`                  chart_of_accounts,
                                                  fiscal_periods, journals,
                                                  journal_lines,
                                                  account_balances, budgets

  HR                      `hr_`                   employees, contracts,
                                                  positions, assignments,
                                                  leave_requests,
                                                  payroll_periods,
                                                  payroll_items, payslips,
                                                  performance_reviews

  Library                 `lib_`                  books, copies, members,
                                                  loans, returns, reservations,
                                                  fines

  Inventory               `inv_`                  assets, categories,
                                                  locations, procurements,
                                                  assignments, maintenance,
                                                  stocktakes, disposals

  Counseling              `cns_`                  cases, appointments, notes,
                                                  interventions, follow_ups
                                                  with restricted access

  Health/UKS              `uks_`                  visits, care_records,
                                                  referrals, consents with
                                                  restricted access

  Communication           `com_`                  announcements, messages,
                                                  templates, notifications,
                                                  deliveries, preferences

  Document                `doc_`                  documents, versions, links,
                                                  classifications,
                                                  retention_policies

  Audit                   `aud_`                  activity_logs, data_changes,
                                                  approval_histories,
                                                  security_events

  Integration             `int_`                  api_clients,
                                                  webhook_endpoints,
                                                  webhook_deliveries,
                                                  provider_transactions,
                                                  sync_jobs

  System                  `sys_`                  settings, feature_flags,
                                                  numbering_sequences,
                                                  job_runs, backup_records
  -----------------------------------------------------------------------------

### 21.3 Relasi domain penting

-   `org_foundations 1..n org_schools`.
-   `org_schools 1..n org_academic_years`.
-   `stu_students 1..n stu_enrollments`.
-   `stu_students n..m stu_guardians` melalui `stu_guardian_student`.
-   `stu_enrollments 1..n stu_class_memberships` atau histori penempatan
    yang dibatasi periode.
-   `cur_curriculum_versions 1..n cur_curriculum_structures`.
-   `acd_teacher_assignments` menghubungkan guru, mapel, kelas, sekolah,
    dan periode.
-   `acd_schedules` menghubungkan assignment, slot waktu, dan ruang.
-   `exam_attempts` mengacu pada ujian dan snapshot/versi soal.
-   `att_attendance_locations` dimiliki sekolah/unit dan menyimpan nama
    lokasi, latitude, longitude, radius meter (default 50), status
    aktif, serta masa berlaku.
-   `att_attendance_policies` menyimpan aturan per sekolah/periode/jenis
    presensi, termasuk metode GPS/manual, check-in/out, jam layanan,
    toleransi keterlambatan, ambang akurasi, dan apakah presensi wajib.
-   `att_attendance_sessions` merepresentasikan sesi harian/kelas atau
    sesi ujian; jenis sesi membedakan `daily` dan `exam`.
-   `att_attendance_records` menyimpan status final kehadiran per
    peserta dan sesi, metode, waktu check-in/out, serta actor.
-   `att_attendance_location_checks` menyimpan hasil pemeriksaan lokasi
    (latitude, longitude, accuracy_meters, distance_meters, location_id,
    validation_status, checked_at); batasi akses dan retensi koordinat.
-   `att_attendance_corrections` menyimpan nilai lama/baru, alasan,
    pengaju, penyetuju, dan waktu.
-   Satu peserta hanya boleh memiliki satu record aktif per sesi
    presensi; gunakan unique constraint sesuai tenant, sesi, dan
    student.
-   Check-in ujian harus memiliki unique key
    `(exam_session_id, student_id)` agar retry tidak menggandakan
    presensi.
-   Data check-in pra-ujian tidak boleh menggantikan record absensi
    harian.
-   `fin_payments 1..n fin_payment_allocations`; allocation mengacu ke
    bill/item.
-   `acc_journals 1..n acc_journal_lines`.
-   `com_notifications 1..n com_deliveries`.
-   `doc_documents 1..n doc_versions`.
-   Approval dan audit menyimpan actor serta referensi resource yang
    stabil.

### 21.4 Aturan integritas

-   Unique nomor siswa minimal dalam scope sekolah sesuai kebijakan.
-   Unique nomor dokumen transaksi dalam scope organisasi dan periode.
-   Constraint status dan foreign key harus menjaga referential
    integrity.
-   Nilai uang menggunakan tipe decimal dengan presisi yang disepakati,
    bukan floating point.
-   Simpan currency code jika ada kemungkinan multi-currency.
-   Simpan snapshot nama/keterangan pada dokumen transaksi bila
    dibutuhkan untuk histori.
-   Index semua foreign key dan kolom pencarian/filter utama berdasarkan
    query nyata.
-   Hindari satu tabel polymorphic tanpa validasi referensi jika
    integritas relasi kritis diperlukan.
-   Data sensitif dienkripsi atau dilindungi sesuai threat model; kunci
    dikelola di luar source code.
-   Audit log tidak menyimpan password, token, OTP, secret, atau payload
    sensitif secara mentah.

### 21.5 Strategi migrasi dari struktur lama

1.  Inventarisasi seluruh tabel, kolom, index, constraint, view,
    trigger, dan stored procedure yang benar-benar ada.
2.  Petakan tabel legacy ke domain target; jangan mengasumsikan nama
    tabel usulan sudah ada.
3.  Buat data dictionary dan mapping sumber-ke-target.
4.  Tentukan canonical ID dan aturan deduplikasi siswa/user.
5.  Buat migration bertahap yang dapat diuji pada salinan database.
6.  Jalankan dry-run, validasi jumlah record, orphan, duplikasi, dan
    saldo.
7.  Siapkan backup, rollback, serta cutover plan.
8.  Pertahankan compatibility layer sementara bila modul lama belum
    dapat dipindahkan sekaligus.
9.  Hapus struktur lama hanya setelah semua consumer dipindahkan dan
    rekonsiliasi data disetujui.

------------------------------------------------------------------------

## 22. Arsitektur Teknis dan Standar Implementasi

### 22.1 Pola arsitektur

Untuk tahap sekarang, gunakan **modular monolith** dengan batas domain
yang tegas. Jangan memecah aplikasi menjadi banyak microservices sebelum
kebutuhan scaling, ownership tim, deployment, dan observability
membenarkannya.

Lapisan yang disarankan: - Presentation: Blade/views, controllers, API
resources. - Application: use cases, commands, queries, DTO,
validators. - Domain: entity/model, business rules, policies, domain
services, events. - Infrastructure: Eloquent repositories bila
diperlukan, database, cache, queue, storage, provider adapters. - Shared
kernel: primitive dan helper lintas domain yang benar-benar generik.

Contoh struktur konseptual:

``` text
app/
├── Modules/
│   ├── Organization/
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   └── Presentation/
│   ├── Admission/
│   ├── Student/
│   ├── Academic/
│   ├── Attendance/
│   ├── Learning/
│   ├── Examination/
│   ├── Finance/
│   ├── Accounting/
│   ├── HumanResources/
│   ├── Communication/
│   └── Reporting/
├── Shared/
│   ├── Contracts/
│   ├── Exceptions/
│   ├── Support/
│   └── Tenancy/
└── Providers/
```

Struktur ini adalah target organisasi kode. Penyesuaian harus mengikuti
struktur aktual repository dan tidak perlu memindahkan seluruh file
sekaligus.

### 22.2 Komponen infrastruktur

  Komponen                 Tanggung jawab
  ------------------------ -----------------------------------------------------
  MySQL                    Data transaksi dan master
  Redis                    Cache, rate limit, distributed lock bila diperlukan
  Queue worker             Email, notifikasi, import/export, report berat
  Scheduler                Job periodik dan pengingat
  Private object storage   Dokumen, media soal, lampiran
  Mail provider            Email transaksional
  Payment adapter          Gateway dan rekonsiliasi
  WhatsApp adapter         Pesan berdasarkan persetujuan dan kebijakan
  Centralized logging      Error, correlation ID, dan operational log
  Monitoring               Availability, latency, queue, database dan resource
  CI/CD                    Test, build, deploy, migration terkontrol

### 22.3 Standar backend

-   Semua input divalidasi melalui request validator.
-   Authorization diperiksa di server melalui policy/gate atau service
    authorization.
-   Controller tipis; aturan bisnis berada di use case/domain service.
-   Operasi multi-tabel menggunakan transaction.
-   Gunakan database constraint sebagai pertahanan kedua.
-   API memakai versioning dan response contract konsisten.
-   Pagination/filter/sort dibatasi dan divalidasi.
-   Error response tidak membocorkan stack trace atau data internal.
-   External provider dipanggil melalui adapter dan timeout/retry yang
    terukur.
-   Gunakan idempotency pada payment callback, import job, dan operasi
    yang bisa diulang.
-   Hindari N+1 query; gunakan eager loading dan query profiling.
-   Perubahan schema memakai migration yang dapat direview dan diuji.

### 22.4 Standar frontend dan UX

-   Responsive untuk desktop, tablet, dan mobile.
-   Konsisten dalam design tokens, spacing, typography, warna, dan
    komponen.
-   Tampilkan loading, empty, error, success, dan validation state.
-   Form panjang dibagi menjadi section/wizard dengan autosave bila
    sesuai.
-   Tabel memiliki pencarian, filter, pagination, sorting, dan export
    terkontrol.
-   Aksi destruktif meminta konfirmasi dan alasan bila diperlukan.
-   Status workflow harus terlihat dan dapat dipahami pengguna.
-   Hindari animasi berlebihan yang mengganggu aksesibilitas dan
    performa.
-   Gunakan keyboard navigation, label form, kontras, dan semantic HTML.
-   Data sensitif tidak ditampilkan pada dashboard atau notifikasi tanpa
    kebutuhan.

### 22.5 API dan integrasi

-   API menggunakan prefix versi, misalnya `/api/v1`.
-   Gunakan autentikasi yang sesuai jenis client; token memiliki expiry,
    scope, dan revocation.
-   Rate limit per client/user dan endpoint sensitif.
-   Webhook diverifikasi dengan signature dan timestamp.
-   Simpan delivery attempt, response code, retry count, dan dead-letter
    state.
-   Gunakan correlation ID untuk menelusuri request lintas sistem.
-   Jangan mengirim data anak yang tidak diperlukan ke pihak ketiga.
-   Dokumentasikan request, response, error, permission, dan contoh
    payload melalui OpenAPI.
-   Sediakan sandbox/test mode untuk integrasi yang mendukungnya.

### 22.6 File dan dokumen

-   File privat disimpan di private storage, bukan direktori publik.
-   Akses melalui authorization dan signed URL berumur pendek bila
    diperlukan.
-   Validasi MIME type, ukuran, ekstensi, dan nama file.
-   Lakukan malware scanning jika tersedia.
-   Simpan metadata pemilik, klasifikasi, versi, dan retention.
-   Jangan menggunakan nama file asli sebagai path langsung.
-   Backup database dan file harus memiliki kebijakan serta uji restore.

------------------------------------------------------------------------

## 23. Non-Functional Requirements

Target awal berikut harus divalidasi melalui load test dan kebutuhan
organisasi; angka bukan klaim performa aplikasi saat ini.

  -----------------------------------------------------------------------
  Area                                Target kebutuhan
  ----------------------------------- -----------------------------------
  Availability                        Tetapkan SLO berdasarkan jam
                                      operasional dan kritikalitas ujian

  Performance                         Halaman operasional umum
                                      ditargetkan responsif; ukur p95 per
                                      endpoint

  Scalability                         Uji pertumbuhan sekolah, siswa,
                                      transaksi, dan ujian serentak

  Security                            OWASP ASVS sebagai acuan kontrol
                                      aplikasi; threat modeling untuk
                                      modul kritis

  Privacy                             Data minimization, purpose
                                      limitation, access control,
                                      retention, dan audit

  Tenant isolation                    Tidak ada akses lintas tenant tanpa
                                      kewenangan eksplisit

  Reliability                         Retry terbatas, idempotency,
                                      timeout, circuit breaker untuk
                                      provider bila perlu

  Backup                              Backup otomatis terenkripsi dan
                                      retensi terdefinisi

  Disaster Recovery                   Tetapkan RPO/RTO dan lakukan
                                      simulasi restore berkala

  Observability                       Structured logs, metrics,
                                      tracing/correlation ID, alert

  Maintainability                     Automated test, code review, static
                                      analysis, coding standard

  Accessibility                       Target WCAG 2.2 AA untuk alur utama
                                      bila memungkinkan

  Compatibility                       Browser modern yang ditetapkan dan
                                      diuji

  Data integrity                      FK, unique, transaction,
                                      reconciliation dan invariant bisnis

  Localization                        Bahasa, format tanggal/angka/mata
                                      uang, timezone sekolah

  Retention                           Retensi dokumen, log, dan data
                                      akademik mengikuti kebijakan serta
                                      regulasi yang berlaku
  -----------------------------------------------------------------------

### 23.1 Security baseline

-   Password di-hash menggunakan algoritma password hashing yang
    didukung framework.
-   MFA diwajibkan untuk role administrator dan role berisiko tinggi
    sesuai kebijakan.
-   Login dan endpoint sensitif menggunakan rate limit.
-   Session cookie menggunakan Secure, HttpOnly, SameSite sesuai
    deployment.
-   CSRF protection aktif untuk session-based form.
-   Validasi object-level authorization mencegah IDOR.
-   Query menggunakan parameter binding/ORM yang aman.
-   Output di-escape untuk mencegah XSS.
-   Upload dibatasi dan disimpan privat.
-   Secret tidak disimpan di repository atau log.
-   Enkripsi transport menggunakan HTTPS.
-   Audit untuk login gagal, perubahan permission, export data,
    approval, dan transaksi finansial.
-   Uji dependency vulnerability dan patch keamanan secara berkala.
-   Hak akses support/administrator lintas tenant harus dibatasi,
    memiliki alasan, dan tercatat.

### 23.2 Data privacy

-   Kumpulkan data hanya untuk tujuan pendidikan/operasional yang sah.
-   Batasi akses data kesehatan, konseling, keluarga, dan identitas
    anak.
-   Tentukan dasar pemrosesan, pemberitahuan privasi, masa simpan, serta
    prosedur permintaan subjek data.
-   Hindari data sensitif dalam email, WhatsApp, log, URL, dan
    analytics.
-   Data export harus memiliki permission, audit, dan pembatasan volume.
-   Retensi/penghapusan harus mempertimbangkan kewajiban arsip akademik,
    keuangan, dan ketentuan hukum yang berlaku.

------------------------------------------------------------------------

## 24. Testing, Acceptance Criteria, dan Production Readiness

### 24.1 Jenis pengujian

-   Unit test untuk aturan domain dan kalkulasi.
-   Feature test untuk alur HTTP dan permission.
-   Integration test untuk database, queue, storage, dan provider
    adapter.
-   Contract test untuk API dan webhook.
-   End-to-end test untuk alur PPDB, akademik, CBT, pembayaran, dan
    raport.
-   Security test untuk tenant isolation, IDOR, privilege escalation,
    upload, session, dan rate limit.
-   Concurrency test untuk pembayaran, finalisasi ujian, dan proses
    batch.
-   Load/stress test untuk login, dashboard, ujian serentak, dan report.
-   Backup/restore test.
-   User Acceptance Test per role dan per sekolah percontohan.

### 24.2 Acceptance criteria minimum

  -----------------------------------------------------------------------
  Area                                Kriteria lulus
  ----------------------------------- -----------------------------------
  Tenant isolation                    User sekolah A tidak dapat
                                      membaca/mengubah data sekolah B
                                      dengan mengganti ID/request

  Role/permission                     Endpoint menolak action tanpa
                                      permission meskipun UI dimanipulasi

  Scope                               Guru hanya mengakses kelas/mapel
                                      penugasannya pada periode aktif

  PPDB                                Konversi pendaftar menjadi siswa
                                      idempotent dan histori PPDB tetap
                                      tersedia

  Student lifecycle                   Perubahan kelas/sekolah
                                      mempertahankan histori enrollment

  Curriculum                          Versi kurikulum lama tetap dapat
                                      dibaca untuk histori akademik

  Schedule                            Bentrok guru, kelas, dan ruang
                                      terdeteksi sebelum publikasi

  Attendance                          Koreksi presensi menyimpan nilai
                                      lama, nilai baru, alasan, dan actor

  Daily attendance GPS                Server menghitung jarak; check-in
                                      otomatis hanya diterima jika lokasi
                                      aktif, akurasi memenuhi kebijakan,
                                      dan jarak \<= radius yang berlaku
                                      (default 50 meter)

  Daily attendance                    Siswa hanya dapat check-in untuk
                                      dirinya, sekolah/enrollment aktif,
                                      tanggal, dan sesi yang sah;
                                      duplikasi ditolak/idempotent

  Exam attendance                     Jika absensi pra-ujian diwajibkan,
                                      peserta di luar radius atau belum
                                      check-in tidak dapat memulai
                                      attempt

  Exam monitoring                     Guru hanya melihat sesi/kelas yang
                                      ditugaskan; status check-in dan
                                      status attempt ditampilkan terpisah

  Manual attendance override          Override memerlukan permission,
                                      alasan wajib, actor, timestamp, dan
                                      audit trail

  Location privacy                    Lokasi hanya dikumpulkan saat
                                      check-in; akses koordinat dibatasi
                                      dan retensi dapat dikonfigurasi

  CBT                                 Jawaban tersimpan dan dapat
                                      dipulihkan sesuai aturan setelah
                                      gangguan koneksi

  CBT timing                          Deadline ditentukan server dan
                                      finalisasi duplikat tidak
                                      menggandakan hasil

  Assessment                          Nilai memiliki status dan histori
                                      koreksi

  Report card                         Raport tidak dapat diedit langsung
                                      setelah publikasi

  Payment                             Callback berulang tidak membuat
                                      pembayaran ganda

  Allocation                          Alokasi tidak melebihi jumlah yang
                                      dapat dialokasikan

  Refund                              Refund memiliki referensi transaksi
                                      asal, alasan, approval, dan audit

  Accounting                          Total debit sama dengan total
                                      kredit pada setiap jurnal posted

  Documents                           File privat tidak dapat diakses
                                      tanpa authorization

  Audit                               Aktivitas kritis memiliki actor,
                                      waktu, resource, action, dan
                                      correlation ID

  Notification                        Kegagalan provider tercatat dan
                                      retry tidak mengirim duplikat tanpa
                                      kontrol

  Backup                              Restore berhasil pada lingkungan
                                      terpisah dan hasilnya diverifikasi

  Performance                         Load test memenuhi SLO yang
                                      disepakati pada beban realistis

  Deployment                          Migration, rollback/forward
                                      recovery, health check, dan smoke
                                      test terdokumentasi
  -----------------------------------------------------------------------

### 24.3 Production readiness checklist

-   [ ] Tidak ada kredensial demo/default aktif di production.
-   [ ] `.env` dan secret manager dikonfigurasi aman.
-   [ ] Debug mode nonaktif.
-   [ ] HTTPS dan security headers dikonfigurasi.
-   [ ] Tenant isolation test lulus.
-   [ ] Permission test lulus untuk setiap role utama.
-   [ ] Backup otomatis berjalan dan restore sudah diuji.
-   [ ] Queue worker dan scheduler dipantau.
-   [ ] Log tidak mengandung password, token, OTP, atau data sensitif.
-   [ ] Error monitoring dan alert aktif.
-   [ ] Payment reconciliation tersedia sebelum pembayaran online
    diaktifkan.
-   [ ] Audit trail aktif untuk transaksi dan perubahan kritis.
-   [ ] Load test ujian serentak selesai.
-   [ ] UAT disetujui per role dan sekolah pilot.
-   [ ] SOP incident, rollback, dan disaster recovery tersedia.
-   [ ] Kebijakan privasi, retensi, dan akses data disetujui pemilik
    proses.

------------------------------------------------------------------------

## 25. Roadmap Implementasi

Urutan berikut mempertimbangkan dependency dan risiko. Estimasi waktu
harus dibuat setelah audit source code, jumlah developer, dan ruang
lingkup sekolah pilot diketahui.

### Fase 0 --- Discovery dan audit

-   Inventaris route, controller, model, migration, tabel, dan
    permission yang benar-benar ada.
-   Petakan fitur README ke bukti implementasi.
-   Identifikasi bug, duplikasi, data integrity issue, dan technical
    debt.
-   Susun ERD aktual dan data dictionary.
-   Buat daftar test yang sudah ada dan coverage awal.
-   Putuskan model tenancy, identitas, dan histori siswa.

**Output:** audit report, current-state architecture, gap backlog, risk
register.

### Fase 1 --- Core foundation dan keamanan (P0)

-   Perbaiki tenant scope dan authorization.
-   Konsolidasikan user, role, permission, dan assignment scope.
-   Tambahkan audit trail dan approval foundation.
-   Rapikan migration dan strategi database baru.
-   Tambahkan monitoring, queue, scheduler, backup, dan restore test.
-   Terapkan API foundation dan standar error.

**Exit criteria:** isolasi tenant dan permission lulus pengujian;
database dapat dibangun ulang secara reproducible.

### Fase 2 --- Student lifecycle dan akademik (P1)

-   PPDB end-to-end.
-   Konversi calon siswa.
-   Student/guardian registry.
-   Enrollment dan histori kelas/sekolah.
-   Kurikulum berversi.
-   Penugasan guru dan jadwal.
-   Kalender akademik.
-   Absensi harian GPS/manual, titik lokasi sekolah, geofencing radius
    default 50 meter, izin/sakit, koreksi, rekap wali kelas, dan
    notifikasi orang tua.

**Exit criteria:** satu siklus siswa dari pendaftaran sampai penempatan
kelas dapat diselesaikan dan diaudit.

### Fase 3 --- Learning, assessment, dan CBT hardening

-   Materi, tugas, submission, feedback.
-   Penyempurnaan bank soal dan versioning.
-   Autosave, resume, server timer, idempotent finalization.
-   Absensi pra-ujian CBT dengan geofencing 50 meter, gate mulai ujian,
    dan dashboard monitoring guru.
-   Incident handling dan monitoring.
-   Penilaian, moderasi, publikasi nilai.
-   Raport dan approval.
-   Kenaikan kelas, mutasi, kelulusan.

**Exit criteria:** simulasi ujian serentak dan siklus raport lulus UAT.

### Fase 4 --- Finance dan accounting (P0/P1 sesuai risiko operasional)

-   Billing engine dan aturan tarif.
-   Pembayaran manual dan gateway.
-   Payment allocation dan idempotency.
-   Kwitansi, refund, reversal.
-   Rekonsiliasi.
-   Cash/bank management.
-   Chart of accounts, journal, ledger, budget dan closing.

**Exit criteria:** saldo tagihan, pembayaran, alokasi, kas,
rekonsiliasi, dan jurnal dapat ditelusuri serta seimbang.

### Fase 5 --- Operasional, SDM, dan komunikasi

-   HR dan payroll.
-   Perpustakaan.
-   Inventaris dan pengadaan.
-   UKS dan BK dengan pembatasan data.
-   Dokumen dan surat menyurat.
-   Notification center dan parent portal enhancement.

**Exit criteria:** modul operasional memiliki owner, SOP, permission,
dan laporan.

### Fase 6 --- Enterprise optimization dan rollout

-   Executive analytics dan konsolidasi yayasan.
-   Performance/load tuning.
-   Security assessment dan penetration test.
-   Disaster recovery drill.
-   CI/CD dan release governance.
-   Pilot satu sekolah, evaluasi, lalu rollout bertahap.
-   Training, SOP, support desk, dan change management.

**Exit criteria:** production readiness checklist disetujui dan proses
support operasional tersedia.

### 25.1 Prioritas backlog

-   **P0 --- wajib sebelum scale/produksi kritis:** tenant isolation,
    authorization, audit, integritas transaksi, backup/restore, keamanan
    CBT, idempotency pembayaran.
-   **P1 --- inti operasional sekolah:** PPDB, student lifecycle,
    kurikulum, jadwal, absensi, penilaian, raport, rekonsiliasi.
-   **P2 --- perluasan platform:** LMS, HR/payroll, perpustakaan,
    inventaris, UKS/BK, komunikasi lanjutan, analytics.
-   **P3 --- enhancement:** automation lanjutan, integrasi tambahan,
    mobile app native, advanced BI, dan fitur AI setelah tata kelola
    data siap.

------------------------------------------------------------------------

## 26. Keputusan Arsitektur yang Harus Ditetapkan

Keputusan berikut harus dicatat sebagai Architecture Decision Record
(ADR), bukan dibiarkan menjadi asumsi implementasi.

  -----------------------------------------------------------------------------
  ID                Keputusan         Opsi yang perlu         Dampak
                                      dievaluasi              
  ----------------- ----------------- ----------------------- -----------------
  ADR-001           Model tenant      Satu                    Isolasi data,
                                      yayasan/multi-sekolah   billing platform,
                                      atau SaaS multi-yayasan deployment

  ADR-002           Database tenancy  Shared schema,          Biaya, backup,
                                      schema/database per     query,
                                      tenant, atau hybrid     skalabilitas

  ADR-003           Identitas         Satu user global dengan Login lintas
                                      banyak role/relasi atau portal, guardian,
                                      akun terpisah           guru sekaligus
                                                              orang tua

  ADR-004           Histori siswa     Enrollment dan class    Mutasi, promosi,
                                      membership per periode  alumni, laporan
                                                              historis

  ADR-005           Kurikulum         Versioning dan mapping  Kompatibilitas
                                      per                     akademik
                                      sekolah/jenjang/tahun   

  ADR-006           Keuangan          Cash-basis sederhana    Audit, laporan,
                                      atau accounting ledger  konsolidasi
                                      penuh                   

  ADR-007           Payment           Manual, gateway,        Callback,
                                      virtual account, atau   rekonsiliasi,
                                      kombinasi               refund

  ADR-008           API               Internal-only atau      Auth, versioning,
                                      integrasi partner       rate limit

  ADR-009           File storage      Local private storage   Skalabilitas dan
                                      atau object storage     disaster recovery

  ADR-010           Deployment        Single instance,        Availability dan
                                      horizontal web/worker,  biaya
                                      atau managed platform   

  ADR-011           Laravel upgrade   Pertahankan Laravel 10  Security,
                                      sementara atau upgrade  dependency, biaya
                                      ke versi supported      migrasi

  ADR-012           Reporting         Query langsung,         Beban database
                                      replica, atau warehouse transaksi dan
                                                              analytics
  -----------------------------------------------------------------------------

### 26.1 Rekomendasi keputusan awal

-   Mulai dengan modular monolith.
-   Pertahankan satu database bersama hanya jika tenant isolation dapat
    dibuktikan melalui test dan desain ownership yang konsisten.
-   Pisahkan user account dari profil siswa/guru/orang tua.
-   Simpan histori enrollment dan penempatan kelas.
-   Gunakan ledger untuk akuntansi jika laporan keuangan formal menjadi
    kebutuhan.
-   Gunakan adapter untuk payment, email, WhatsApp, dan storage.
-   Jangan mengaktifkan integrasi pembayaran produksi sebelum
    idempotency, signature validation, reconciliation, dan
    refund/reversal diuji.
-   Rencanakan upgrade dari Laravel 10 ke versi yang masih didukung;
    verifikasi compatibility PHP dan dependency sebelum menentukan
    target final.

------------------------------------------------------------------------

## Lampiran --- Definisi Status Implementasi

Gunakan label status yang konsisten pada issue tracker dan README:

  -----------------------------------------------------------------------
  Status                              Makna
  ----------------------------------- -----------------------------------
  `IMPLEMENTED`                       Kode tersedia dan alur utama telah
                                      diuji

  `PARTIAL`                           Sebagian fungsi tersedia; ada gap
                                      yang diketahui

  `DOCUMENTED`                        Tercantum dalam dokumentasi,
                                      implementasi belum diverifikasi

  `PLANNED`                           Masuk target/backlog, belum
                                      dikerjakan

  `IN_PROGRESS`                       Sedang dikembangkan

  `BLOCKED`                           Terhambat dependency/keputusan

  `DEPRECATED`                        Tidak disarankan untuk penggunaan
                                      baru

  `VERIFIED`                          Telah melewati acceptance test yang
                                      ditetapkan
  -----------------------------------------------------------------------

Setiap modul sebaiknya memiliki metadata: owner, status, scope tenant,
permission, tabel, route/API, workflow, audit requirement, test
evidence, dan dependensi modul.
