# Implementasi Enterprise — Catatan Pengembangan

## Baseline

PRD acuan: `PRD_Enterprise.docx` (Laravel 10, PHP 8.1+, MySQL 8+, Blade + Alpine, shared database, multi-foundation dan multi-school).

Project awal adalah aplikasi CBT yang sudah memiliki:
- Tiga guard login terpisah: administrator (`web`), peserta (`peserta`), dan orang tua (`orangtua`).
- Konteks yayasan/sekolah pada akun administrator.
- Modul ujian, bank soal, peserta, guru, absensi, raport, kelulusan, dan pembayaran biaya ujian CBT.
- Relasi orang tua ke banyak peserta.

## Perubahan pada paket ini

1. Menambahkan role administrator/staff yang lebih eksplisit pada seeder: Administrator Sekolah, Kepala Sekolah, Guru, Wali Kelas, Bendahara, Petugas Pembayaran, dan Petugas Tata Usaha.
2. Menetapkan daftar menu untuk tiap role dengan prinsip least privilege. Role `admin` tetap merupakan role super administrator lama.
3. Memperbaiki pembatasan tenant daftar, verifikasi, dan akses bukti pembayaran CBT berdasarkan sekolah/yayasan siswa. Record pembayaran lama yang belum memiliki tenant ID menggunakan relasi peserta sebagai sumber scope.
4. Memastikan daftar tes pada halaman pembayaran ikut scope sekolah/yayasan.
5. Memproses verifikasi/penolakan pembayaran dalam transaksi database dengan row lock, mencegah pemrosesan ulang status yang sudah selesai, dan mencatat aktivitas.
6. Saat orang tua mengunggah bukti biaya ujian, tenant yayasan/sekolah siswa ikut disimpan pada record pembayaran.
7. Menambahkan migration dan model fondasi ledger SPP terpisah: kategori biaya, struktur biaya, tagihan siswa, item tagihan, metode pembayaran, pembayaran, alokasi, dan akun kas. Ledger SPP ini sengaja tidak dicampur dengan tabel `cbt_pembayaran`.
8. Menambahkan animasi dashboard dan micro-interaction, dengan dukungan `prefers-reduced-motion`.
9. Mengatur locale `id` dan timezone `Asia/Jakarta`.

## Pemetaan role yang tersedia

| Role | Hak menu utama |
|---|---|
| admin | Semua menu lama (super administrator) |
| administrator-sekolah | Operasional sekolah, peserta, guru, CBT, absensi, laporan, pengaturan |
| kepala-sekolah | Monitoring peserta/guru, absensi, hasil, pembayaran CBT, kelulusan, laporan |
| guru | Guru, absensi, hasil sesuai konfigurasi menu lama |
| wali-kelas | Guru, peserta, absensi, hasil, kelulusan, laporan |
| bendahara | Dashboard, verifikasi pembayaran CBT, laporan |
| petugas-pembayaran | Dashboard dan verifikasi pembayaran CBT |
| petugas-tata-usaha | Dashboard, peserta, orang tua, grup, absensi |
| operator-soal / operator-tes | Role CBT lama tetap tersedia |

**Catatan penting:** menu `pembayaran` yang sudah berjalan di project awal adalah pembayaran biaya ujian CBT, bukan SPP. Role bendahara/petugas-pembayaran saat ini hanya mendapat akses ke fitur CBT tersebut. Permission project existing masih berbasis kode menu (modul), belum sampai granular per aksi `view/create/update/approve/reverse` seperti matriks PRD; aksi sensitif tambahan harus diberi Policy/permission tersendiri. Migration `fin_*` adalah fondasi database SPP terpisah (tenant columns dan unique key tersedia, tetapi konsistensi lintas foundation-school-bill-payment tetap harus dijaga oleh service/policy dan constraint komposit pada tahap berikutnya); workflow SPP (penerbitan tagihan, cicilan, verifikasi, kwitansi, rekonsiliasi, refund/reversal, dan portal orang tua) masih perlu controller/service/policy/UI dan pengujian tersendiri sebelum dipakai operasional.

## Bagian PRD yang belum boleh dianggap selesai

Project ini belum merupakan implementasi penuh seluruh PRD. Modul kurikulum versi, kalender akademik, penawaran mapel, jadwal belajar, PPDB, e-learning, perpustakaan, inventaris, BK/UKS, HR/payroll, notifikasi, audit enterprise, API `/api/v1` lengkap, dan laporan konsolidasi masih memerlukan implementasi bertahap. Struktur dan migration SPP yang ditambahkan juga belum memiliki UI transaksi produksi.

## Langkah instalasi lokal

1. Salin `.env.example` menjadi `.env`, isi kredensial database lokal sendiri, dan buat `APP_KEY` baru.
2. Jalankan `composer install`.
3. Jalankan `php artisan key:generate`.
4. Buat database MySQL 8+ dengan charset `utf8mb4`.
5. Import `database/schema/cbt_laravel_schema.sql` ke database kosong terlebih dahulu. File ini berisi struktur legacy CBT dan migration history, tetapi tidak berisi data pengguna/sample.
6. Jalankan `php artisan migrate` untuk menerapkan migration tambahan `2026_11_01_000014_create_school_fee_finance_tables`.
7. Jalankan `php artisan db:seed` hanya untuk lingkungan development; seeder berisi akun demo dengan password sederhana yang wajib diganti dan jangan digunakan di production.
8. Jalankan `npm ci` lalu `npm run build`.
9. Jalankan `php artisan storage:link` jika memakai public storage untuk aset yang memang boleh publik.

**Catatan schema legacy:** tabel CBT lama (`cbt_user`, `cbt_tes`, dan tabel pendukung) berasal dari baseline SQL. Migration lama di repository hanya menambah/mengubah sebagian tabel tersebut, sehingga jangan menjalankan `migrate:fresh` pada database kosong sebelum baseline schema legacy dikonversi menjadi migration Laravel penuh.

Paket distribusi ini sengaja tidak menyertakan `.env`, dump database berisi data pengguna, `vendor`, `node_modules`, log, atau cache. File `database/schema/cbt_laravel_schema.sql` adalah schema-only baseline, bukan dump data.

## Hasil pemeriksaan dalam lingkungan pengerjaan

- `php -l` untuk file PHP di `app`, `routes`, `database`, dan `tests`: lulus sebelum dan sesudah perubahan.
- `php artisan route:list --except-vendor`: berhasil, 126 route terdaftar pada source awal.
- `php artisan test`: belum dapat dijalankan di container pemeriksaan karena ekstensi PHP `dom`, `mbstring`, dan `xmlwriter` tidak tersedia.
- `npm run build`: belum dapat diverifikasi di container ini karena `node_modules` di ZIP berasal dari Windows (`@esbuild/win32-x64`), sedangkan container Linux membutuhkan binary Linux. Hapus `node_modules`, jalankan `npm ci`, lalu `npm run build` pada mesin target.
- Migration MySQL belum dieksekusi karena container pemeriksaan tidak menyediakan driver PDO MySQL/SQLite maupun server database. Jalankan migration di database staging setelah backup.

## Sebelum production

- Ganti semua akun demo dan password; nonaktifkan akun demo.
- Atur APP_DEBUG=false, APP_ENV=production, HTTPS, secure cookies, rate limiting, backup terenkripsi.
- Audit seluruh controller/query/file/export terhadap tenant scope dan Policy; patch pembayaran ini bukan pengganti audit seluruh domain.
- Tambahkan automated tests untuk isolasi yayasan/sekolah, role/permission, alokasi pembayaran, webhook idempotency, dan akses dokumen.
- Tinjau status dukungan Laravel 10 dan rencanakan upgrade sesuai catatan PRD.

## Catatan keamanan arsip sumber

Arsip sumber yang diterima menyertakan `.env` dan dump SQL berisi data akun/contoh. Keduanya tidak disalin ke paket hasil. Karena `.env` pernah berada di arsip yang dibagikan, rotasi `APP_KEY`, password database, SMTP/provider secrets, dan kredensial lain yang pernah tersimpan di sana sebelum deployment atau commit ke repository.
