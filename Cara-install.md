# Cara Install & Pasang Sistem SIMS CBT Laravel

Panduan ini menjelaskan langkah-langkah memasang **SIMS CBT Laravel** sampai sistem bisa diakses di browser. Sistem ini adalah aplikasi **Sistem Informasi Sekolah (SIMS) + Computer Based Test (CBT)** berbasis **Laravel 10**, **PHP 8.1+**, **MySQL 8+**, dengan frontend **Blade + Alpine.js + Bootstrap 5 + Chart.js**, build oleh **Vite 4**.

> Berdasarkan dokumentasi yang ada di repository (README.md). Bagian yang diminta "cara sampai sistem jalan" adalah bagian 1–8, 14, 15 di bawah.

---

## 1. Prasyarat

Pastikan perangkat lunak berikut sudah terpasang dan bisa dijalankan di command line.

| Komponen | Versi minimal | Cek perintah |
|---|---|---|
| PHP | 8.1+ | `php -v` |
| Composer | 2 | `composer --version` |
| MySQL / MariaDB | 8+ | `mysql --version` (tidak wajib untuk instalasi, cukup servernya) |
| Node.js | 18+ | `node -v` |
| npm | — | `npm -v` |

Catatan lingkungan di mesin ini (untuk referensi):
- PHP 8.3.3
- Node v24.22.0
- npm 11.19.1
- MySQL CLI tidak tersedia sebagai CLI, tetapi MySQL server cukup diinstal dan diakses via GUI/tool lain.

Jika PHP belum lengkap, pastikan ada ekstensi berikut:
`pdo_mysql`, `mbstring`, `dom`, `xmlwriter`, `fileinfo`, `openssl`.

---

## 2. Siapkan folder proyek

Buka terminal / command prompt, lalu pindah ke folder tempat proyek akan diletakkan.

```bash
cd /c/Users/Admin/Desktop/cbt-laravel-sims-updated/cbt-laravel
```

Atau jika cloning dulu dari repository:

```bash
git clone <repository-url> cbt-laravel
cd cbt-laravel
```

Isi folder seharusnya punya file-file ini:

```
artisan
composer.json
composer.lock
.env
package.json
vite.config.js
database/
public/
resources/
routes/
src/ (jika ada)
vendor/          # sudah ada dari composer install
node_modules/    # sudah ada dari npm install
```

---

## 3. Salin file .env

Sistem membutuhkan file `.env` untuk konfigurasi koneksi database, key aplikasi, dan pengaturan lainnya. File `example.env` ada di repository, namakan hasil salinannya menjadi `.env`.

```bash
cp .env.example .env
```

Ini penting karena `.env` berisi data rahasia (database credentials, keys) dan **tidak boleh di-commitske** ke repository.

---

## 4. Isi konfigurasi database di .env

Buka file `.env` dengan editor teks, lalu sesuaikan bagian database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cbt_laravel
DB_USERNAME=root
DB_PASSWORD=
```

Penjelasan:
- `DB_CONNECTION=mysql` — pakai MySQL.
- `DB_HOST` — alamat MySQL. Biasanya `127.0.0.1` bila MySQL di lokal, atau `db` bila pakai Docker/Sail.
- `DB_PORT` — port MySQL, default `3306`.
- `DB_DATABASE` — nama database. Bisa `cbt_laravel` atau nama lain.
- `DB_USERNAME` — user MySQL. Ganti dari `root` kalau mau pakai user biasa.
- `DB_PASSWORD` — password user MySQL. Kosongkan kalau tanpa password.

Contoh koneksi via Terminal bila MySQL butuh password:

```bash
mysql -h 127.0.0.1 -P 3306 -u root -p
```

Masukkan password yang sesuai.

---

## 5. Install dependensi PHP

Pastikan kamu sudah berada di folder proyek (`cbt-laravel`), lalu jalankan:

```bash
composer install
```

Atau jika tidak punya `composer install` global, pakai:

```bash
composer.phar install
```

Proses ini akan mendownload library-library yang tercantum di `composer.json` dan memasangnya ke folder `vendor/`.

---

## 6. Generate application key

Laravel butuh key aplikasi untuk enkripsi session, token, dan data sensitif lainnya.

```bash
php artisan key:generate
```

Setelah berhasil, file `.env` akan terisi `APP_KEY` seperti:

```env
APP_KEY=base64:...
```

---

## 7. Buat database MySQL

Buka koneksi ke MySQL (via terminal, Workbench, atau tool lain) dan buat database baru. Contoh:

```sql
CREATE DATABASE cbt_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Atau kalau inginnya pake name lain, ganti `cbt_laravel` dengan nama yang sesuai, lalu sesuaikan di `.env`.

**Jangan lupa** pastikan database-nya `utf8mb4` (untuk karakter emotikon/skrup, sebab aplikasi ini pakai `utf8mb4`).

---

## 8. Jalankan migration (buat tabel)

Lanjut ke folder proyek:

```bash
php artisan migrate
```

Perintah ini akan membuat seluruh tabel:
- Tabel legacy CBT (seperti `cbt_user`, `cbt_topik`, `cbt_soal`, `cbt_tes`, `cbt_absensi`, `cbt_pembayaran`, dll).
- Tabel core (yayasan/sekolah/tahun ajaran/semester).
- Tabel fin_ (SPP/keuangan).
- Tabel akses user, menu, preferensi.

Setelah selesai, kamu akan melihat pesan bahwa tabel-tabel berhasil dibuat.

---

## 9. (Opsional) Isi data demo

Guide ini aman, tapi untuk development/local saja, kamu bisa isi data demo lewat seeder:

```bash
php artisan db:seed
```

Data demo:
- Admin: `admin` / `admin`, `adminyayasan` / `adminyayasan`, `adminsekolah` / `adminsekolah`, `operator`, `operatorsoal`, dsb.
- Peserta: `lutfi`, `joko`, `siti`, `budi`, `rina`, `andi`, `dewi`, `fajar`, `raka`, `sd1a`, `smp1a`, `sma1a`, `smk1a`, dst.
- Orang tua: `orangtua1` / `orangtua1`, `orangtua2` / `orangtua2`.
- Akun peserta: username = password (misal login `lutfi` pakai password `lutfi`).
- Token ujian demo: `123456` untuk tes **Ulangan Bahasa Indonesia**; **Try Out Matematika** tidak pakai token.

> **PENTING:** Akun demo ini **harus diganti** sebelum dipakai produksi.

---

## 10. Install dan build asset frontend (Vite)

Untuk pakai tampilan dan JavaScript (Bootstrap, Alpine.js, Chart.js, AOS, dsb):

```bash
npm ci
npm run build
```

- `npm ci` — install package dari `package-lock.json` (lebih konsisten).
- `npm run build` — compile frontend lewat Vite, hasilnya ke `public/build/`.

Jika kamu masih developing dan ingin LiveReload:

```bash
npm run dev
```

---

## 11. Buat symlink storage public

File bukti pembayaran dan upload media disimpan di `storage/`, harus di-link ke `public/` supaya bisa diakses:

```bash
php artisan storage:link
```

Jika sudah benar, muncul pesan:

```
The [public/storage] link has been connected.
```

---

## 12. Jalankan Laravel server

```bash
php artisan serve
```

Defaultnya:
- Alamat: `http://127.0.0.1:8000`
- Port: `8000`

Buka di browser:

```
http://127.0.0.1:8000
```

### Nama route/login
- Login admin/staff: `http://127.0.0.1:8000/admin/login`
- Login peserta: `http://127.0.0.1:8000/peserta/login`
- Login orang tua: `http://127.0.0.1:8000/orangtua/login`

---

## 13. Cara login pertama kali

1. Buka login admin: `/admin/login`
2. Login dengan akun demo, misalnya:
   - Username: `admin`
   - Password: `admin`
3. Buka menu **Sistem -> Pengaturan Aplikasi** untuk isi data nama sekolah, alamat, kepala sekolah, tahun ajaran, KKM, dan **Wajib Pembayaran Sebelum Ujian** (`cbt_gate_pembayaran`).
4. Lakukan setup master data:
   - Yayasan
   - Sekolah & Tahun Ajaran
   - Group/Kelas
   - Peserta
   - Akun Orang Tua
   - User Administrator & Level/Hak Akses
   - Modul, Topik, dan Soal

---

## 14. Verifikasi sistem sudah jalan

Cek hal-hal berikut:

1. Buka `http://127.0.0.1:8000` — tidak boleh muncul error PHP (seperti `Class not found`, `SQLSTATE[HY000] [1045] Access denied`, `PDOException`).
2. Login ke admin. Menu sesuai role muncul.
3. Lakukan migrasi: `php artisan migrate --force` kalau production.

---

## 15. Penyelesaian masalah (troubleshooting)

### 15.1 Error: `SQLSTATE[HY000] [1045] Access denied for user 'root'@'127.0.0.1'`
- Periksa `DB_USERNAME`, `DB_PASSWORD`, `DB_HOST`, `DB_PORT` di `.env`.
- Pastikan MySQL berjalan: `sudo systemctl status mysql` (Linux/macOS) atau layanan MySQL di Windows.

### 15.2 Error: `Class not found` atau `vendor/` kosong
- Jalankan ulang: `composer install`
- Cek apakah `composer.json` tidak rusak.

### 15.3 Error: `SQLSTATE[HY000] [1049] Unknown database 'cbt_laravel'`
- Buat database dulu: `CREATE DATABASE cbt_laravel ...;`
- Atau sesuaikan `DB_DATABASE` di `.env` dengan nama yang sudah ada.

### 15.4 Error: `PDOException: could not find driver`
- PHP tidak punya ekstensi `pdo_mysql`.
- Instal ulang/phpize atau aktifkan ekstensi `pdo_mysql`.

### 15.5 Halaman white screen / nothing berubah
- Hapus cache: 
  ```bash
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  ```
- Setelah build frontend, jalankan `npm run build` lagi.

### 15.6 File upload media/bukti tidak muncul
- Pastikan `php artisan storage:link` sudah berhasil.
- Cek permission folder `storage/app/public/`.

### 15.7 `npm run build` gagal
- Periksa Node.js dan npm versi (minimal 18+).
- Jalankan `npm install` dulu, baru `npm run build`.

---

## 16. Konfigurasi pemasangan (setup setelah jalan)

Setelah sistem jalan, lakukan urutan berikut untuk yang baru dipakai:

### 16.1 Siapkan master data

1. **Sistem -> Yayasan**: tambah yayasan.
2. **Sistem -> Sekolah & Tahun Ajaran**: tambah sekolah, jenjang, tahun ajaran, semester (sistem otomatis buat semester 1 & 2).
3. **Peserta -> Group Peserta**: buat kelas/group + jenjang + tingkat.
4. **Peserta -> Daftar Peserta**: buat peserta atau import CSV.
5. **Peserta -> Akun Orang Tua**: buat akun orang tua + hubungkan anak.
6. **Sistem -> User Administrator**: buat staff/guru.
7. **Sistem -> Level & Hak Akses**: atur menu default per level.
8. **Sistem -> Pengaturan Aplikasi**: isi data sekolah, KKM, dan gate pembayaran.

### 16.2 Siapkan bank soal

1. **Bank Soal -> Modul**: tambah mata pelajaran/modul.
2. **Bank Soal -> Topik**: tambah grup soal, pilih tipe ujian (UM/UAS/UTS), pilih guru pemilik.
3. **Bank Soal -> Soal**: buat soal (PG/Essay/Jawaban singkat), atur kesulitan 1–5.
4. (Opsional) import CSV/XLSX lewat template halaman soal.
5. (Opsional) upload media soal via fitur upload.

### 16.3 Buat ujian

1. **Manajemen Ujian -> Daftar Tes**: buat tes, isi waktu, durasi, bobot nilai, biaya, token.
2. Pilih group/kelas peserta yang boleh ikut.
3. Tambah komposisi soal: topik, tipe soal, tingkat kesulitan, jumlah soal, atur acak soal/jawaban.
4. (Opsional) aktifkan token, lalu generate token di **Token Ujian**.

### 16.4 Peserta mulai ujian

1. Login peserta: `/peserta/login`.
2. Pilih tes, baca detail, klik mulai.
3. (Jika ada token) isi token.
4. Kerjakan, simpan, ragu, selesai.
5. Hasil finalisasi otomatis; essay dinilai manual oleh admin.

### 16.5 Orang tua bayar biaya ujian

1. Admin buat metode & rekening pembayaran: **Keuangan -> Kategori & Tarif -> Metode & Rekening Pembayaran**, aktifkan "Tampilkan ke orang tua".
2. Orang tua login `/orangtua/login`, pilih anak, buka pembayaran ujian.
3. Unggah bukti pembayaran (JPG/PNG/PDF, maks 4 MB).
4. Admin verifikasi di **Manajemen Ujian -> Pembayaran Ujian**.
5. (Opsional) Aktifkan gate: **Sistem -> Pengaturan Aplikasi -> Wajib Pembayaran Sebelum Ujian** (`cbt_gate_pembayaran = ya`), maka peserta harus ada pembayaran diverifikasi baru bisa mulai ujian.

---

## 17. Pengaturan untuk produksi (production)

Untuk dipakai di lingkungan produksi, ubah minimal beberapa hal di `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example.com

SESSION_DRIVER=database  # atau memcached/redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true

CACHE_DRIVER=file  # atau redis/memcached
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cbt_laravel
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

Rekomendasi keamanan:
- Jangan commit `.env`, `cbt_laravel.sql`, `vendor/`, `node_modules/`, log, cache.
- Gunakan user MySQL dengan hak minimum.
- Ganti seluruh akun demo sebelum produksi.
- Aktifkan HTTPS, `APP_DEBUG=false`, cookie secure.
- Backup database terenkripsi.
- Hapus `cbt_laravel.sql` (dump local) dari server kalau tidak dibutuhkan, karena berisi data lokal.

---

## 18. Perintah yang sering dipakai

```bash
# Status koneksi database
php artisan migrate:status

# Jalankan migrasi ulang (development)
php artisan migrate:fresh --seed

# Jalankan test
php artisan test

# Cek route
php artisan route:list --except-vendor

# Format kode
./vendor/bin/pint

# Build frontend
npm run build
```

---

## 19. Referensi

- README utama: `README.md`
- Dock dokumen: `docs/EXPANSION-SIMS.md`, `docs/IMPLEMENTASI_ENTERPRISE.md`
- Baseline schema: `database/schema/cbt_laravel_schema.sql`
- Script start: `start_laravel.sh` (untuk start dev server dengan Tailscale; file ini sudah ada di folder).

---

**Ringkasan singkat:**  
1. `cp .env.example .env`  
2. isi `DB_*` di `.env`  
3. `composer install`  
4. `php artisan key:generate`  
5. buat database `cbt_laravel` (utf8mb4)  
6. `php artisan migrate`  
7. (opsional) `php artisan db:seed`  
8. `npm ci && npm run build`  
9. `php artisan storage:link`  
10. `php artisan serve`  
11. buka `http://127.0.0.1:8000`, login admin.

**Sistem sudah jalan** — selanjutnya lakukan setup master data, bank soal, pengaturan app, dan ujian sesuai panduan bagian 16.
