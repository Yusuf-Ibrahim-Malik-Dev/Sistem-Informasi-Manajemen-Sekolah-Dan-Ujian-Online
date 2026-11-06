<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\AcademicYear;
use App\Models\Access;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\AttendanceAuditEvent;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceLocation;
use App\Models\AttendanceLocationCheck;
use App\Models\AttendancePolicy;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Foundation;
use App\Models\Jawaban;
use App\Models\Konfigurasi;
use App\Models\Level;
use App\Models\Menu;
use App\Models\Modul;
use App\Models\OrangTua;
use App\Models\PaymentAllocation;
use App\Models\Pembayaran;
use App\Models\Peserta;
use App\Models\Raport;
use App\Models\School;
use App\Models\SchoolLevel;
use App\Models\SchoolPayment;
use App\Models\SchoolPaymentMethod;
use App\Models\Semester;
use App\Models\Soal;
use App\Models\StudentBill;
use App\Models\StudentBillItem;
use App\Models\Tes;
use App\Models\TesSoal;
use App\Models\TesSoalJawaban;
use App\Models\TesToken;
use App\Models\TesUser;
use App\Models\Topik;
use App\Models\UserGroup;
use App\Models\UserMenuAccess;
use App\Models\UserPreference;
use App\Support\AttendanceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CbtSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedKonfigurasi();
        $this->seedEnterprise();
        $this->seedAttendanceGeofence();
        $this->seedLevelDanMenu();
        $this->seedAdmin();
        $this->seedPeserta();
        $this->seedPesertaSemuaJenjang();
        $this->seedAkunRiwayat();
        $this->seedBankSoal();
        $this->seedGuruSampel();
        $this->seedAksesGuruSampel();
        $this->seedRaport();
        $this->seedRiwayatRaport();
        $this->seedTes();
        $this->seedRiwayat();
        $this->seedOrangTuaDanPembayaran();
        $this->sinkronkanSekolah();
        $this->seedContohSinkron();
        $this->seedGeocoderAndLocationDefaults();
    }

    private function seedKonfigurasi(): void
    {
        Konfigurasi::set('cbt_nama', 'Sistem Informasi Sekolah');
        Konfigurasi::set('cbt_keterangan', 'Platform Terpadu Manajemen dan Layanan Sekolah');
        Konfigurasi::set('cbt_informasi', '<p>Silakan pilih tes yang tersedia di bawah ini. Jika tes tidak muncul, hubungi operator yang bertugas.</p>');
        Konfigurasi::set('link_login_operator', 'ya');
        Konfigurasi::set('cbt_mobile_lock_xambro', 'tidak');
        Konfigurasi::set('cbt_alamat', 'Jl. Pendidikan No. 1, Jakarta');
        Konfigurasi::set('cbt_kepala_sekolah', 'Drs. Ahmad Subarjo, M.Pd.');
        Konfigurasi::set('cbt_nip_kepala_sekolah', '19700101 199501 1 001');
        Konfigurasi::set('cbt_tahun_ajaran', '2025/2026');
        Konfigurasi::set('cbt_nilai_kelulusan', '70');
    }

    private function seedGeocoderAndLocationDefaults(): void
    {
        $defaults = [
            'geocode_limit' => ['8', 'Jumlah hasil maksimal saat pencarian lokasi (geocode).'],
            'geocode_country' => ['id', 'Kode negara untuk membatasi hasil geocode, misal "id".'],
            'default_radius_meters' => ['50', 'Radius presensi bawaan (meter) saat tidak ada lokasi tersedia.'],
            'default_max_accuracy_meters' => ['100', 'Akurasi GPS maksimum bawaan (meter).'],
        ];

        foreach ($defaults as $kode => [$isi, $keterangan]) {
            Konfigurasi::set($kode, (string) $isi, $keterangan);
        }
    }

    private function seedEnterprise(): void
    {
        $levels = [
            ['level_code' => 'SD', 'level_name' => 'Sekolah Dasar', 'education_category' => 'Dasar'],
            ['level_code' => 'SMP', 'level_name' => 'Sekolah Menengah Pertama', 'education_category' => 'Menengah'],
            ['level_code' => 'SMA', 'level_name' => 'Sekolah Menengah Atas', 'education_category' => 'Menengah'],
            ['level_code' => 'SMK', 'level_name' => 'Sekolah Menengah Kejuruan', 'education_category' => 'Menengah'],
            ['level_code' => 'MI', 'level_name' => 'Madrasah Ibtidaiyah', 'education_category' => 'Dasar'],
            ['level_code' => 'MTs', 'level_name' => 'Madrasah Tsanawiyah', 'education_category' => 'Menengah'],
            ['level_code' => 'MA', 'level_name' => 'Madrasah Aliyah', 'education_category' => 'Menengah'],
        ];

        foreach ($levels as $level) {
            SchoolLevel::updateOrCreate(['level_code' => $level['level_code']], $level);
        }

        $foundation = Foundation::updateOrCreate(
            ['foundation_code' => 'YPN'],
            [
                'foundation_name' => 'Yayasan Pendidikan Nusantara',
                'legal_name' => 'Yayasan Pendidikan Nusantara',
                'address' => 'Jl. Pendidikan No. 1, Jakarta',
                'phone' => '021-5550100',
                'email' => 'admin@ypn.example',
                'is_active' => true,
            ]
        );

        $sekolah = [
            ['SD', 'SD Nusantara 1', 'SD-N1', '20101001'],
            ['SMP', 'SMP Nusantara 1', 'SMP-N1', '20201001'],
            ['SMA', 'SMA Nusantara 2', 'SMA-N2', '20301001'],
            ['SMK', 'SMK Nusantara 3', 'SMK-N3', '20401001'],
        ];

        foreach ($sekolah as [$kodeJenjang, $nama, $kode, $npsn]) {
            $level = SchoolLevel::where('level_code', $kodeJenjang)->firstOrFail();

            $school = School::updateOrCreate(
                ['foundation_id' => $foundation->id, 'school_code' => $kode],
                [
                    'school_level_id' => $level->id,
                    'npsn' => $npsn,
                    'school_name' => $nama,
                    'address' => 'Jl. Pendidikan No. 1, Jakarta',
                    'phone' => '021-5550100',
                    'email' => strtolower($kode).'@ypn.example',
                    'is_active' => true,
                ]
            );

            $year = AcademicYear::updateOrCreate(
                ['school_id' => $school->id, 'year_name' => '2026/2027'],
                [
                    'foundation_id' => $foundation->id,
                    'start_date' => '2026-07-01',
                    'end_date' => '2027-06-30',
                    'is_active' => true,
                ]
            );

            Semester::updateOrCreate(
                ['academic_year_id' => $year->id, 'semester_number' => 1],
                [
                    'school_id' => $school->id,
                    'start_date' => '2026-07-01',
                    'end_date' => '2026-12-31',
                    'is_active' => true,
                ]
            );

            Semester::updateOrCreate(
                ['academic_year_id' => $year->id, 'semester_number' => 2],
                [
                    'school_id' => $school->id,
                    'start_date' => '2027-01-01',
                    'end_date' => '2027-06-30',
                    'is_active' => false,
                ]
            );

            foreach ([
                ['BCA', 'Bank BCA', '1234567890', 1],
                ['MANDIRI', 'Bank Mandiri', '0987654321', 2],
            ] as [$kodeMetode, $namaBank, $nomorRekening, $urutan]) {
                SchoolPaymentMethod::updateOrCreate(
                    ['school_id' => $school->id, 'method_code' => $kodeMetode],
                    [
                        'method_name' => $namaBank,
                        'bank_name' => $namaBank,
                        'account_number' => $nomorRekening,
                        'account_holder' => 'Yayasan Pendidikan Nusantara',
                        'is_active' => true,
                        'show_to_guardian' => true,
                        'sort_order' => $urutan,
                    ]
                );
            }
        }
    }

    private function seedAttendanceGeofence(): void
    {
        foreach (School::all() as $school) {
            AttendanceLocation::updateOrCreate(
                ['school_id' => $school->id, 'name' => 'Gedung Utama'],
                [
                    'foundation_id' => $school->foundation_id,
                    'latitude' => -6.2000000,
                    'longitude' => 106.8166667,
                    'radius_meters' => 50,
                    'max_accuracy_meters' => 100,
                    'is_active' => true,
                ]
            );

            AttendancePolicy::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'policy_type' => AttendancePolicy::TYPE_DAILY,
                    'academic_year_id' => null,
                ],
                [
                    'foundation_id' => $school->foundation_id,
                    'require_location' => true,
                    'allow_manual' => true,
                    'require_checkin' => true,
                    'require_checkout' => false,
                    'service_start' => '07:00:00',
                    'service_end' => '15:00:00',
                    'late_tolerance_minutes' => 15,
                    'max_accuracy_meters' => 100,
                    'is_required' => false,
                ]
            );
        }
    }

    private function seedLevelDanMenu(): void
    {
        $levels = [
            ['level' => 'admin', 'keterangan' => 'Administrator'],
            ['level' => 'operator-soal', 'keterangan' => 'Operator Soal'],
            ['level' => 'operator-tes', 'keterangan' => 'Operator Tes'],
            ['level' => 'administrator-sekolah', 'keterangan' => 'Administrator Sekolah'],
            ['level' => 'kepala-sekolah', 'keterangan' => 'Kepala Sekolah'],
            ['level' => 'guru', 'keterangan' => 'Guru Mata Pelajaran'],
            ['level' => 'wali-kelas', 'keterangan' => 'Wali Kelas'],
            ['level' => 'bendahara', 'keterangan' => 'Bendahara Sekolah'],
            ['level' => 'petugas-pembayaran', 'keterangan' => 'Petugas Pembayaran'],
            ['level' => 'petugas-kasir', 'keterangan' => 'Kasir Seluruh Pembayaran'],
            ['level' => 'petugas-spp', 'keterangan' => 'Petugas Pembayaran SPP'],
            ['level' => 'operator-sekolah', 'keterangan' => 'Operator Data Sekolah'],
            ['level' => 'petugas-perpustakaan', 'keterangan' => 'Petugas Perpustakaan'],
            ['level' => 'petugas-bk', 'keterangan' => 'Guru Bimbingan Konseling'],
            ['level' => 'petugas-uks', 'keterangan' => 'Petugas UKS'],
            ['level' => 'petugas-tata-usaha', 'keterangan' => 'Petugas Tata Usaha'],
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(['level' => $level['level']], $level);
        }

        $menus = [
            ['tipe' => 0, 'parent' => null, 'kode_menu' => 'dashboard', 'nama_menu' => 'Dashboard', 'url' => 'admin.dashboard', 'icon' => 'bi-speedometer2', 'urutan' => 1],
            ['tipe' => 0, 'parent' => null, 'kode_menu' => 'enterprise', 'nama_menu' => 'Yayasan & Sekolah', 'url' => '#', 'icon' => 'bi-buildings', 'urutan' => 2],
            ['tipe' => 1, 'parent' => 'enterprise', 'kode_menu' => 'yayasan', 'nama_menu' => 'Yayasan', 'url' => 'admin.yayasan.index', 'icon' => 'bi-bank', 'urutan' => 1],
            ['tipe' => 1, 'parent' => 'enterprise', 'kode_menu' => 'sekolah', 'nama_menu' => 'Sekolah & Tahun Ajaran', 'url' => 'admin.sekolah.index', 'icon' => 'bi-building-gear', 'urutan' => 2],
            ['tipe' => 0, 'parent' => null, 'kode_menu' => 'data-bank', 'nama_menu' => 'Bank Soal', 'url' => '#', 'icon' => 'bi-collection', 'urutan' => 3],
            ['tipe' => 1, 'parent' => 'data-bank', 'kode_menu' => 'modul', 'nama_menu' => 'Modul', 'url' => 'admin.modul.index', 'icon' => 'bi-folder2-open', 'urutan' => 1],
            ['tipe' => 1, 'parent' => 'data-bank', 'kode_menu' => 'topik', 'nama_menu' => 'Topik', 'url' => 'admin.topik.index', 'icon' => 'bi-diagram-3', 'urutan' => 2],
            ['tipe' => 1, 'parent' => 'data-bank', 'kode_menu' => 'soal', 'nama_menu' => 'Soal', 'url' => 'admin.soal.index', 'icon' => 'bi-patch-question', 'urutan' => 3],
            ['tipe' => 0, 'parent' => null, 'kode_menu' => 'data-peserta', 'nama_menu' => 'Peserta', 'url' => '#', 'icon' => 'bi-people', 'urutan' => 4],
            ['tipe' => 1, 'parent' => 'data-peserta', 'kode_menu' => 'grup', 'nama_menu' => 'Group Peserta', 'url' => 'admin.grup.index', 'icon' => 'bi-collection', 'urutan' => 1],
            ['tipe' => 1, 'parent' => 'data-peserta', 'kode_menu' => 'peserta', 'nama_menu' => 'Daftar Peserta', 'url' => 'admin.peserta.index', 'icon' => 'bi-person-vcard', 'urutan' => 2],
            ['tipe' => 1, 'parent' => 'data-peserta', 'kode_menu' => 'orangtua', 'nama_menu' => 'Akun Orang Tua', 'url' => 'admin.orangtua.index', 'icon' => 'bi-people', 'urutan' => 3],
            ['tipe' => 1, 'parent' => 'data-peserta', 'kode_menu' => 'absensi', 'nama_menu' => 'Absensi Peserta', 'url' => 'admin.absensi.index', 'icon' => 'bi-calendar-check', 'urutan' => 4],
            ['tipe' => 1, 'parent' => 'data-peserta', 'kode_menu' => 'absensi-lokasi', 'nama_menu' => 'Lokasi & Kebijakan Absensi', 'url' => 'admin.absensi.lokasi', 'icon' => 'bi-geo-alt', 'urutan' => 5],
            ['tipe' => 1, 'parent' => 'data-peserta', 'kode_menu' => 'absensi-monitoring', 'nama_menu' => 'Monitoring Kehadiran', 'url' => 'admin.absensi.monitoring', 'icon' => 'bi-broadcast', 'urutan' => 6],
            ['tipe' => 0, 'parent' => null, 'kode_menu' => 'manajemen-ujian', 'nama_menu' => 'Manajemen Ujian', 'url' => '#', 'icon' => 'bi-clipboard-check', 'urutan' => 5],
            ['tipe' => 1, 'parent' => 'manajemen-ujian', 'kode_menu' => 'tes', 'nama_menu' => 'Daftar Tes', 'url' => 'admin.tes.index', 'icon' => 'bi-journal-text', 'urutan' => 1],
            ['tipe' => 1, 'parent' => 'manajemen-ujian', 'kode_menu' => 'token', 'nama_menu' => 'Token Ujian', 'url' => 'admin.token.index', 'icon' => 'bi-key', 'urutan' => 2],
            ['tipe' => 1, 'parent' => 'manajemen-ujian', 'kode_menu' => 'hasil', 'nama_menu' => 'Hasil & Evaluasi', 'url' => 'admin.hasil.index', 'icon' => 'bi-bar-chart', 'urutan' => 3],
            ['tipe' => 1, 'parent' => 'manajemen-ujian', 'kode_menu' => 'pembayaran', 'nama_menu' => 'Pembayaran Ujian', 'url' => 'admin.pembayaran.index', 'icon' => 'bi-wallet2', 'urutan' => 4],
            ['tipe' => 0, 'parent' => null, 'kode_menu' => 'keuangan', 'nama_menu' => 'Keuangan', 'url' => '#', 'icon' => 'bi-cash-stack', 'urutan' => 6],
            ['tipe' => 1, 'parent' => 'keuangan', 'kode_menu' => 'spp', 'nama_menu' => 'Tagihan SPP', 'url' => 'admin.spp.index', 'icon' => 'bi-receipt', 'urutan' => 1],
            ['tipe' => 1, 'parent' => 'keuangan', 'kode_menu' => 'spp-pembayaran', 'nama_menu' => 'Pembayaran SPP', 'url' => 'admin.spp.payments', 'icon' => 'bi-cash-coin', 'urutan' => 2],
            ['tipe' => 1, 'parent' => 'keuangan', 'kode_menu' => 'spp-pengaturan', 'nama_menu' => 'Kategori & Tarif', 'url' => 'admin.spp.setup', 'icon' => 'bi-sliders2', 'urutan' => 3],
            ['tipe' => 0, 'parent' => null, 'kode_menu' => 'guru', 'nama_menu' => 'Guru', 'url' => 'admin.guru.index', 'icon' => 'bi-person-video3', 'urutan' => 7],
            ['tipe' => 0, 'parent' => null, 'kode_menu' => 'kelulusan', 'nama_menu' => 'Kelulusan', 'url' => 'admin.kelulusan.index', 'icon' => 'bi-award', 'urutan' => 8],
            ['tipe' => 0, 'parent' => null, 'kode_menu' => 'laporan', 'nama_menu' => 'Laporan', 'url' => 'admin.laporan.rekap', 'icon' => 'bi-graph-up-arrow', 'urutan' => 9],
            ['tipe' => 0, 'parent' => null, 'kode_menu' => 'sistem', 'nama_menu' => 'Sistem', 'url' => '#', 'icon' => 'bi-gear', 'urutan' => 10],
            ['tipe' => 1, 'parent' => 'sistem', 'kode_menu' => 'pengaturan', 'nama_menu' => 'Pengaturan Aplikasi', 'url' => 'admin.pengaturan.index', 'icon' => 'bi-sliders', 'urutan' => 1],
    ['tipe' => 1, 'parent' => 'sistem', 'kode_menu' => 'default-lokasi', 'nama_menu' => 'Default Lokasi & Pencarian', 'url' => 'admin.pengaturan.lokasi', 'icon' => 'bi-geo-alt', 'urutan' => 4],
            ['tipe' => 1, 'parent' => 'sistem', 'kode_menu' => 'user', 'nama_menu' => 'User Administrator', 'url' => 'admin.user.index', 'icon' => 'bi-person-gear', 'urutan' => 2],
            ['tipe' => 1, 'parent' => 'sistem', 'kode_menu' => 'level', 'nama_menu' => 'Level & Hak Akses', 'url' => 'admin.level.index', 'icon' => 'bi-shield-lock', 'urutan' => 3],
        ];

        foreach ($menus as $menu) {
            Menu::updateOrCreate(['kode_menu' => $menu['kode_menu']], $menu);
        }

        $akses = [
            'admin' => Menu::pluck('kode_menu')->all(),
            'operator-soal' => ['modul', 'topik', 'soal', 'guru', 'absensi', 'absensi-lokasi', 'absensi-monitoring'],
            'operator-tes' => ['tes', 'token', 'hasil', 'guru', 'absensi', 'absensi-lokasi', 'absensi-monitoring'],
            // Role sekolah mengikuti prinsip least privilege. Menu pembayaran
            // saat ini khusus biaya ujian CBT; modul SPP disiapkan terpisah.
            'administrator-sekolah' => ['dashboard', 'sekolah', 'modul', 'topik', 'soal', 'grup', 'peserta', 'orangtua', 'absensi', 'absensi-lokasi', 'absensi-monitoring', 'tes', 'token', 'hasil', 'pembayaran', 'spp', 'spp-pembayaran', 'spp-pengaturan', 'guru', 'kelulusan', 'laporan', 'pengaturan', 'default-lokasi'],
            'kepala-sekolah' => ['dashboard', 'sekolah', 'peserta', 'guru', 'absensi', 'absensi-lokasi', 'absensi-monitoring', 'hasil', 'pembayaran', 'spp', 'spp-pembayaran', 'kelulusan', 'laporan'],
            'guru' => ['dashboard', 'absensi-monitoring'],
            'wali-kelas' => ['dashboard', 'guru', 'peserta', 'absensi', 'absensi-lokasi', 'absensi-monitoring', 'hasil', 'kelulusan', 'laporan'],
            'bendahara' => ['dashboard', 'pembayaran', 'spp', 'spp-pembayaran', 'spp-pengaturan', 'laporan'],
            'petugas-pembayaran' => ['dashboard', 'pembayaran', 'spp', 'spp-pembayaran'],
            // Kasir dapat menerima seluruh kategori pembayaran yang sudah terdaftar.
            'petugas-kasir' => ['dashboard', 'pembayaran', 'spp', 'spp-pembayaran'],
            // Petugas SPP hanya menangani tagihan dan transaksi SPP.
            'petugas-spp' => ['dashboard', 'spp', 'spp-pembayaran'],
            'operator-sekolah' => ['dashboard', 'sekolah', 'grup', 'peserta', 'orangtua', 'guru', 'laporan'],
            'petugas-perpustakaan' => ['dashboard'],
            'petugas-bk' => ['dashboard', 'peserta', 'laporan'],
            'petugas-uks' => ['dashboard', 'peserta', 'absensi', 'absensi-monitoring'],
            'petugas-tata-usaha' => ['dashboard', 'peserta', 'orangtua', 'grup', 'absensi', 'absensi-monitoring'],
        ];

        foreach ($akses as $level => $codes) {
            Access::where('level', $level)->delete();

            foreach ($codes as $code) {
                Access::create(['level' => $level, 'kode_menu' => $code, 'add' => 1, 'edit' => 1]);
            }
        }
    }

    private function seedAdmin(): void
    {
        $foundation = Foundation::where('foundation_code', 'YPN')->firstOrFail();
        $sma = School::where('school_code', 'SMA-N2')->firstOrFail();

        $admins = [
            ['username' => 'admin', 'password' => 'admin', 'nama' => 'Administrator', 'level' => 'admin', 'keterangan' => 'Akun administrator utama', 'scope_type' => Admin::SCOPE_SYSTEM],
            ['username' => 'adminyayasan', 'password' => 'adminyayasan', 'nama' => 'Admin Yayasan', 'level' => 'admin', 'keterangan' => 'Admin dengan scope satu yayasan', 'scope_type' => Admin::SCOPE_FOUNDATION, 'foundation_id' => $foundation->id],
            ['username' => 'adminsekolah', 'password' => 'adminsekolah', 'nama' => 'Admin SMA Nusantara 2', 'level' => 'admin', 'keterangan' => 'Admin dengan scope satu sekolah', 'scope_type' => Admin::SCOPE_SCHOOL, 'foundation_id' => $foundation->id, 'school_id' => $sma->id],
            ['username' => 'operator', 'password' => 'operator', 'nama' => 'Operator Ujian', 'level' => 'operator-tes', 'keterangan' => 'Operator pelaksanaan ujian', 'scope_type' => Admin::SCOPE_SYSTEM],
            ['username' => 'operatorsoal', 'password' => 'operatorsoal', 'nama' => 'Operator Soal', 'level' => 'operator-soal', 'keterangan' => 'Operator pengelola bank soal', 'scope_type' => Admin::SCOPE_SYSTEM],
            ['username' => 'gurua', 'password' => 'gurua', 'nama' => 'Guru A (Matematika)', 'level' => 'guru', 'keterangan' => 'Akun guru contoh Matematika; menu ditentukan lewat penugasan akun', 'scope_type' => Admin::SCOPE_SYSTEM],
            ['username' => 'gurub', 'password' => 'gurub', 'nama' => 'Guru B (Bahasa Indonesia)', 'level' => 'guru', 'keterangan' => 'Akun guru contoh Bahasa Indonesia; menu ditentukan lewat penugasan akun', 'scope_type' => Admin::SCOPE_SYSTEM],
            ['username' => 'guruc', 'password' => 'guruc', 'nama' => 'Guru C (IPA)', 'level' => 'guru', 'keterangan' => 'Akun guru contoh IPA; menu ditentukan lewat penugasan akun', 'scope_type' => Admin::SCOPE_SYSTEM],
        ];

        foreach ($admins as $admin) {
            Admin::updateOrCreate(
                ['username' => $admin['username']],
                [
                    'password' => Hash::make($admin['password']),
                    'nama' => $admin['nama'],
                    'level' => $admin['level'],
                    'keterangan' => $admin['keterangan'],
                    'scope_type' => $admin['scope_type'],
                    'foundation_id' => $admin['foundation_id'] ?? $foundation->id,
                    'school_id' => $admin['school_id'] ?? null,
                    'ts' => now(),
                ]
            );
        }
    }

    private function seedPeserta(): void
    {
        $grups = ['XI MM', 'XI TKJ'];

        foreach ($grups as $nama) {
            UserGroup::updateOrCreate(
                ['grup_nama' => $nama],
                ['grup_jenjang' => UserGroup::JENJANG_SMA, 'grup_tingkat' => 2]
            );
        }

        $grupMm = UserGroup::where('grup_nama', 'XI MM')->first();
        $grupTkj = UserGroup::where('grup_nama', 'XI TKJ')->first();

        $pesertas = [
            ['user_name' => 'lutfi', 'user_firstname' => 'Lutfi Hakim', 'grup' => $grupMm, 'user_detail' => 'Ruang 1, Sesi 1'],
            ['user_name' => 'joko', 'user_firstname' => 'Joko Susilo', 'grup' => $grupTkj, 'user_detail' => 'Ruang 1, Sesi 1'],
            ['user_name' => 'siti', 'user_firstname' => 'Siti Aminah', 'grup' => $grupMm, 'user_detail' => 'Ruang 1, Sesi 2'],
            ['user_name' => 'budi', 'user_firstname' => 'Budi Santoso', 'grup' => $grupTkj, 'user_detail' => 'Ruang 2, Sesi 1'],
            ['user_name' => 'rina', 'user_firstname' => 'Rina Wulandari', 'grup' => $grupMm, 'user_detail' => 'Ruang 2, Sesi 1'],
            ['user_name' => 'andi', 'user_firstname' => 'Andi Pratama', 'grup' => $grupTkj, 'user_detail' => 'Ruang 2, Sesi 2'],
            ['user_name' => 'dewi', 'user_firstname' => 'Dewi Lestari', 'grup' => $grupMm, 'user_detail' => 'Ruang 3, Sesi 1'],
            ['user_name' => 'fajar', 'user_firstname' => 'Fajar Nugroho', 'grup' => $grupTkj, 'user_detail' => 'Ruang 3, Sesi 1'],
        ];

        foreach ($pesertas as $item) {
            Peserta::updateOrCreate(
                ['user_name' => $item['user_name']],
                [
                    'user_grup_id' => $item['grup']->grup_id,
                    'user_password' => Hash::make($item['user_name']),
                    'user_firstname' => $item['user_firstname'],
                    'user_email' => $item['user_name'].'@example.com',
                    'user_detail' => $item['user_detail'],
                    'user_level' => 1,
                    'user_regdate' => now(),
                ]
            );
        }
    }

    private function seedPesertaSemuaJenjang(): void
    {
        $kelas = [
            [UserGroup::JENJANG_SD, 1, [['sd1a', 'Aisyah Putri'], ['sd1b', 'Bagas Pratama']]],
            [UserGroup::JENJANG_SD, 2, [['sd2a', 'Citra Lestari'], ['sd2b', 'Dimas Saputra']]],
            [UserGroup::JENJANG_SD, 3, [['sd3a', 'Elang Nugraha'], ['sd3b', 'Fitri Handayani']]],
            [UserGroup::JENJANG_SD, 4, [['sd4a', 'Galih Pratama'], ['sd4b', 'Hana Safitri']]],
            [UserGroup::JENJANG_SD, 5, [['sd5a', 'Indra Kusuma'], ['sd5b', 'Jasmine Aulia']]],
            [UserGroup::JENJANG_SD, 6, [['sd6a', 'Kurniawan Adi'], ['sd6b', 'Laila Rahma']]],
            [UserGroup::JENJANG_SMP, 1, [['smp1a', 'Miko Saputra'], ['smp1b', 'Nabila Putri']]],
            [UserGroup::JENJANG_SMP, 2, [['smp2a', 'Oscar Pratama'], ['smp2b', 'Putri Ayu']]],
            [UserGroup::JENJANG_SMP, 3, [['smp3a', 'Qori Ramadhan'], ['smp3b', 'Rani Safitri']]],
            [UserGroup::JENJANG_SMA, 1, [['sma1a', 'Satria Wijaya'], ['sma1b', 'Tania Lestari']]],
            [UserGroup::JENJANG_SMA, 2, [['sma2a', 'Umar Hidayat'], ['sma2b', 'Vina Oktaviani']]],
            [UserGroup::JENJANG_SMA, 3, [['sma3a', 'Wawan Setiawan'], ['sma3b', 'Yuni Kartika']]],
            [UserGroup::JENJANG_SMK, 1, [['smk1a', 'Adrian Maulana'], ['smk1b', 'Bella Anjani']]],
            [UserGroup::JENJANG_SMK, 2, [['smk2a', 'Candra Wijaya'], ['smk2b', 'Dinda Permata']]],
            [UserGroup::JENJANG_SMK, 3, [['smk3a', 'Zaki Firmansyah'], ['smk3b', 'Zahra Amelia']]],
        ];

        foreach ($kelas as [$jenjang, $tingkat, $pesertas]) {
            $grup = UserGroup::updateOrCreate(
                ['grup_nama' => $jenjang.' '.$tingkat],
                ['grup_jenjang' => $jenjang, 'grup_tingkat' => $tingkat]
            );

            foreach ($pesertas as $index => [$username, $nama]) {
                $usia = match ($jenjang) {
                    UserGroup::JENJANG_SD => 6 + $tingkat,
                    UserGroup::JENJANG_SMP => 12 + $tingkat,
                    default => 15 + $tingkat,
                };

                Peserta::updateOrCreate(
                    ['user_name' => $username],
                    [
                        'user_grup_id' => $grup->grup_id,
                        'user_password' => Hash::make($username),
                        'user_firstname' => $nama,
                        'user_email' => $username.'@example.com',
                        'user_birthplace' => 'Jakarta',
                        'user_birthdate' => now()->subYears($usia)->startOfYear()->addMonths(($index * 4) + 1)->toDateString(),
                        'user_detail' => 'Ruang '.(($index % 3) + 1).', Sesi '.(($index % 2) + 1),
                        'user_level' => 1,
                        'user_regdate' => now(),
                    ]
                );
            }
        }
    }

    private function seedAkunRiwayat(): void
    {
        $grup = UserGroup::updateOrCreate(
            ['grup_nama' => 'SMA 3 (Riwayat)'],
            ['grup_jenjang' => UserGroup::JENJANG_SMA, 'grup_tingkat' => 3]
        );

        Peserta::updateOrCreate(
            ['user_name' => 'raka'],
            [
                'user_grup_id' => $grup->grup_id,
                'user_password' => Hash::make('raka'),
                'user_firstname' => 'Raka Pratama',
                'user_email' => 'raka@example.com',
                'user_birthplace' => 'Jakarta',
                'user_birthdate' => now()->subYears(18)->startOfYear()->addMonths(3)->toDateString(),
                'user_detail' => 'Ruang 1, Sesi 1',
                'user_level' => 1,
                'user_regdate' => now(),
            ]
        );
    }

    private function seedBankSoal(): void
    {
        $matematika = Modul::updateOrCreate(['modul_nama' => 'Matematika'], ['modul_aktif' => true]);
        $bahasa = Modul::updateOrCreate(['modul_nama' => 'Bahasa Indonesia'], ['modul_aktif' => true]);
        $adminId = Admin::where('username', 'admin')->value('id');

        $aljabar = Topik::updateOrCreate(
            ['topik_nama' => 'Aljabar', 'topik_modul_id' => $matematika->modul_id],
            [
                'topik_detail' => 'Persamaan, pertidaksamaan, dan operasi aljabar.',
                'topik_tipe_ujian' => Topik::TIPE_UTS,
                'topik_guru_id' => $adminId,
                'topik_aktif' => true,
            ]
        );

        $geometri = Topik::updateOrCreate(
            ['topik_nama' => 'Geometri', 'topik_modul_id' => $matematika->modul_id],
            [
                'topik_detail' => 'Bangun datar dan bangun ruang.',
                'topik_tipe_ujian' => Topik::TIPE_UAS,
                'topik_guru_id' => $adminId,
                'topik_aktif' => true,
            ]
        );

        $eksposisi = Topik::updateOrCreate(
            ['topik_nama' => 'Teks Eksposisi', 'topik_modul_id' => $bahasa->modul_id],
            [
                'topik_detail' => 'Struktur, kaidah, dan isi teks eksposisi.',
                'topik_tipe_ujian' => Topik::TIPE_UM,
                'topik_guru_id' => $adminId,
                'topik_aktif' => true,
            ]
        );

        $soalAljabar = [
            ['Tentukan nilai x dari persamaan 2x + 6 = 14.', ['2', '3', '4', '5'], 2, 2],
            ['Hasil dari (x + 3)(x - 2) adalah ...', ['xÂ² + x - 6', 'xÂ² - x - 6', 'xÂ² + 5x - 6', 'xÂ² - 5x + 6'], 0, 2],
            ['Tentukan nilai x dari 3x - 5 = 10.', ['3', '4', '5', '6'], 2, 2],
            ['Jika a = 2 dan b = 3, nilai aÂ² + bÂ² adalah ...', ['10', '12', '13', '15'], 2, 2],
            ['Bentuk sederhana dari 4x + 3y - 2x + y adalah ...', ['2x + 4y', '2x + 2y', '6x + 4y', '6x + 2y'], 0, 2],
            ['Himpunan penyelesaian dari xÂ² - 9 = 0 adalah ...', ['{-3, 3}', '{-9, 9}', '{-3, 0}', '{0, 3}'], 0, 2],
        ];

        $soalGeometri = [
            ['Luas segitiga dengan alas 10 cm dan tinggi 6 cm adalah ...', ['30 cmÂ²', '40 cmÂ²', '50 cmÂ²', '60 cmÂ²'], 0, 1],
            ['Keliling persegi dengan panjang sisi 7 cm adalah ...', ['21 cm', '28 cm', '35 cm', '49 cm'], 1, 1],
            ['Jumlah besar sudut dalam sebuah segitiga adalah ...', ['90Â°', '180Â°', '270Â°', '360Â°'], 1, 1],
            ['Volume kubus dengan panjang rusuk 4 cm adalah ...', ['16 cmÂ³', '32 cmÂ³', '48 cmÂ³', '64 cmÂ³'], 3, 1],
        ];

        $soalEksposisi = [
            ['Kalimat utama pada paragraf eksposisi umumnya terletak di ...', ['awal paragraf', 'tengah paragraf', 'akhir paragraf', 'judul teks'], 0, 2],
            ['Tujuan utama teks eksposisi adalah ...', ['menghibur pembaca', 'menjelaskan informasi', 'membujuk pembaca', 'menceritakan kisah'], 1, 2],
            ['Penulisan kata baku yang benar adalah ...', ['apotik', 'apotek', 'apotiek', 'aphotek'], 1, 2],
            ['Sinonim dari kata "cermat" adalah ...', ['teliti', 'cepat', 'ceroboh', 'ragu'], 0, 2],
            ['Antonim dari kata "optimal" adalah ...', ['maksimal', 'terbaik', 'minimal', 'efisien'], 2, 2],
        ];

        foreach ($soalAljabar as $item) {
            $this->buatSoal($aljabar->topik_id, $item[0], $item[1], $item[2], $item[3]);
        }

        foreach ($soalGeometri as $item) {
            $this->buatSoal($geometri->topik_id, $item[0], $item[1], $item[2], $item[3]);
        }

        foreach ($soalEksposisi as $item) {
            $this->buatSoal($eksposisi->topik_id, $item[0], $item[1], $item[2], $item[3]);
        }

        $this->buatSoal($aljabar->topik_id, 'Hasil dari 5 + 3 Ã— 2 adalah ...', [], 0, 2, 3, '11');
        $this->buatSoal($aljabar->topik_id, 'Jelaskan langkah-langkah menyelesaikan persamaan linear satu variabel.', [], 0, 2, 2);
    }

    private function seedGuruSampel(): void
    {
        $guruA = Admin::where('username', 'gurua')->firstOrFail();
        $guruB = Admin::where('username', 'gurub')->firstOrFail();
        $guruC = Admin::where('username', 'guruc')->firstOrFail();

        $matematika = Modul::where('modul_nama', 'Matematika')->firstOrFail();
        $bahasa = Modul::where('modul_nama', 'Bahasa Indonesia')->firstOrFail();
        $ipa = Modul::updateOrCreate(['modul_nama' => 'IPA'], ['modul_aktif' => true]);

        $grupA = Topik::updateOrCreate(
            ['topik_nama' => 'Aljabar - Guru A', 'topik_modul_id' => $matematika->modul_id],
            [
                'topik_detail' => 'Contoh grup soal milik Guru A.',
                'topik_tipe_ujian' => Topik::TIPE_UTS,
                'topik_guru_id' => $guruA->id,
                'topik_aktif' => true,
            ]
        );

        $grupB = Topik::updateOrCreate(
            ['topik_nama' => 'Teks Deskripsi - Guru B', 'topik_modul_id' => $bahasa->modul_id],
            [
                'topik_detail' => 'Contoh grup soal milik Guru B.',
                'topik_tipe_ujian' => Topik::TIPE_UAS,
                'topik_guru_id' => $guruB->id,
                'topik_aktif' => true,
            ]
        );

        $grupC = Topik::updateOrCreate(
            ['topik_nama' => 'Sistem Tata Surya - Guru C', 'topik_modul_id' => $ipa->modul_id],
            [
                'topik_detail' => 'Contoh grup soal milik Guru C.',
                'topik_tipe_ujian' => Topik::TIPE_UM,
                'topik_guru_id' => $guruC->id,
                'topik_aktif' => true,
            ]
        );

        $soalGuruA = [
            ['Nilai x dari 4x - 8 = 12 adalah ...', ['3', '4', '5', '6'], 2, 2],
            ['Bentuk sederhana dari 2a + 3b - a + 2b adalah ...', ['a + 5b', '3a + 5b', 'a + b', '3a + b'], 0, 2],
            ['Jika x = 3, nilai 2xAï¿½ + 1 adalah ...', ['13', '17', '18', '19'], 3, 2],
        ];

        $soalGuruB = [
            ['Tujuan teks deskripsi adalah ...', ['menggambarkan objek secara rinci', 'menceritakan urutan peristiwa', 'membujuk pembaca', 'menjelaskan sebab akibat'], 0, 1],
            ['Ciri khas teks deskripsi adalah ...', ['menggunakan pancaindra', 'berisi langkah-langkah', 'berupa dialog', 'berisi argumen'], 0, 1],
            ['Sinonim dari kata "indah" adalah ...', ['buruk', 'cantik', 'ramai', 'sunyi'], 1, 1],
        ];

        $soalGuruC = [
            ['Planet yang paling dekat dengan Matahari adalah ...', ['Merkurius', 'Venus', 'Bumi', 'Mars'], 0, 1],
            ['Satelit alami Bumi adalah ...', ['Bulan', 'Matahari', 'Mars', 'Venus'], 0, 1],
            ['Planet terbesar di tata surya adalah ...', ['Bumi', 'Saturnus', 'Jupiter', 'Neptunus'], 2, 2],
        ];

        foreach ($soalGuruA as $item) {
            $this->buatSoal($grupA->topik_id, $item[0], $item[1], $item[2], $item[3], 1, null, $guruA->id);
        }

        foreach ($soalGuruB as $item) {
            $this->buatSoal($grupB->topik_id, $item[0], $item[1], $item[2], $item[3], 1, null, $guruB->id);
        }

        foreach ($soalGuruC as $item) {
            $this->buatSoal($grupC->topik_id, $item[0], $item[1], $item[2], $item[3], 1, null, $guruC->id);
        }

        $this->buatSoal($grupA->topik_id, 'Hasil dari 5 + 3 A- 2 adalah ...', [], 0, 2, 3, '11', $guruA->id);
        $this->buatSoal($grupB->topik_id, 'Kata baku dari "aktifitas" adalah ...', [], 0, 1, 3, 'aktivitas', $guruB->id);
        $this->buatSoal($grupC->topik_id, 'Planet yang dikenal sebagai planet merah adalah ...', [], 0, 1, 3, 'Mars', $guruC->id);
    }

    private function seedAksesGuruSampel(): void
    {
        $menus = ['soal', 'topik', 'grup', 'guru'];

        foreach (['gurua', 'gurub', 'guruc'] as $username) {
            $guru = Admin::where('username', $username)->first();

            if ($guru === null) {
                continue;
            }

            foreach ($menus as $kodeMenu) {
                UserMenuAccess::updateOrCreate(
                    ['user_id' => $guru->id, 'kode_menu' => $kodeMenu],
                    [
                        'is_allowed' => true,
                        'can_add' => true,
                        'can_edit' => true,
                        'can_delete' => true,
                        'can_approve' => false,
                    ]
                );
            }
        }
    }

    private function buatSoal(int $topikId, string $detail, array $opsi, int $benar, int $difficulty, int $tipe = 1, ?string $kunci = null, ?int $creatorId = null): void
    {
        $soal = Soal::updateOrCreate(
            [
                'soal_topik_id' => $topikId,
                'soal_detail' => $detail,
            ],
            [
                'soal_creator_id' => $creatorId ?? Admin::where('username', 'admin')->value('id'),
                'soal_tipe' => $tipe,
                'soal_kunci' => $kunci,
                'soal_difficulty' => $difficulty,
                'soal_aktif' => true,
            ]
        );

        if (! $soal->wasRecentlyCreated) {
            return;
        }

        foreach ($opsi as $index => $teks) {
            Jawaban::create([
                'jawaban_soal_id' => $soal->soal_id,
                'jawaban_detail' => $teks,
                'jawaban_benar' => $index === $benar,
                'jawaban_aktif' => true,
            ]);
        }
    }

    private function seedRaport(): void
    {
        $guruA = Admin::where('username', 'gurua')->firstOrFail();
        $guruB = Admin::where('username', 'gurub')->firstOrFail();
        $guruC = Admin::where('username', 'guruc')->firstOrFail();

        $moduls = Modul::where('modul_aktif', true)->orderBy('modul_id')->get();

        $guruPerModul = [
            'Matematika' => $guruA,
            'Bahasa Indonesia' => $guruB,
            'IPA' => $guruC,
        ];

        $nilaiEksplisit = [
            'lutfi' => [88, 85, 90],
            'joko' => [80, 82, 84],
            'siti' => [92, 90, 94],
            'budi' => [76, 78, 80],
            'rina' => [86, 88, 87],
            'andi' => [72, 75, 74],
            'dewi' => [90, 91, 89],
            'fajar' => [78, 80, 82],
        ];

        $catatan = [
            'lutfi' => 'Konsisten dan aktif dalam pembelajaran.',
            'siti' => 'Sangat baik, pertahankan prestasi.',
            'rina' => 'Pemahaman materi sudah baik.',
            'dewi' => 'Teliti dan bertanggung jawab.',
        ];

        foreach (Peserta::with('grup')->orderBy('user_id')->get() as $peserta) {
            $jenjang = $peserta->grup?->grup_jenjang ?? UserGroup::JENJANG_SMA;
            $tingkat = (int) ($peserta->grup?->grup_tingkat ?? 1);

            foreach ($moduls->values() as $index => $modul) {
                $skor = $nilaiEksplisit[$peserta->user_name][$index]
                    ?? 78 + (($peserta->user_id + ($index * 3)) % 18);

                Raport::updateOrCreate(
                    [
                        'raport_user_id' => $peserta->user_id,
                        'raport_modul_id' => $modul->modul_id,
                        'raport_jenjang' => $jenjang,
                        'raport_tingkat' => $tingkat,
                    ],
                    [
                        'raport_guru_id' => ($guruPerModul[$modul->modul_nama] ?? $guruA)->id,
                        'raport_nilai' => $skor,
                        'raport_catatan' => $catatan[$peserta->user_name] ?? 'Hasil belajar tercatat dengan baik.',
                    ]
                );
            }
        }
    }

    private function seedRiwayatRaport(): void
    {
        $peserta = Peserta::where('user_name', 'raka')->first();

        if (! $peserta) {
            return;
        }

        $guruA = Admin::where('username', 'gurua')->firstOrFail();
        $guruB = Admin::where('username', 'gurub')->firstOrFail();
        $guruC = Admin::where('username', 'guruc')->firstOrFail();

        $guruPerModul = [
            'Matematika' => $guruA,
            'Bahasa Indonesia' => $guruB,
            'IPA' => $guruC,
        ];

        $moduls = Modul::where('modul_aktif', true)->orderBy('modul_id')->get();

        $levels = [];

        foreach (UserGroup::jenjangOptions() as $jenjang => $label) {
            if ($jenjang === UserGroup::JENJANG_SMK) {
                continue;
            }

            foreach (UserGroup::tingkatOptions($jenjang) as $tingkat => $labelKelas) {
                $levels[] = [$jenjang, $tingkat];
            }
        }

        foreach ($levels as $levelIndex => [$jenjang, $tingkat]) {
            foreach ($moduls->values() as $index => $modul) {
                $nilai = 78 + (($levelIndex * 3 + $index * 2) % 19);

                Raport::updateOrCreate(
                    [
                        'raport_user_id' => $peserta->user_id,
                        'raport_modul_id' => $modul->modul_id,
                        'raport_jenjang' => $jenjang,
                        'raport_tingkat' => $tingkat,
                    ],
                    [
                        'raport_guru_id' => ($guruPerModul[$modul->modul_nama] ?? $guruA)->id,
                        'raport_nilai' => $nilai,
                        'raport_catatan' => 'Nilai raport '.$jenjang.' kelas '.$tingkat.'.',
                    ]
                );
            }
        }
    }

    private function seedTes(): void
    {
        $aljabar = Topik::where('topik_nama', 'Aljabar')->first();
        $geometri = Topik::where('topik_nama', 'Geometri')->first();
        $eksposisi = Topik::where('topik_nama', 'Teks Eksposisi')->first();
        $grups = UserGroup::pluck('grup_id')->all();

        $matematika = Tes::updateOrCreate(
            ['tes_nama' => 'Try Out Matematika'],
            [
                'tes_detail' => 'Try out mata pelajaran Matematika untuk kelas XI. Bacalah setiap soal dengan teliti.',
                'tes_begin_time' => now()->subDay(),
                'tes_end_time' => now()->addDays(30),
                'tes_duration_time' => 60,
                'tes_ip_range' => '*.*.*.*',
                'tes_results_to_users' => true,
                'tes_detail_to_users' => true,
                'tes_ranking_to_users' => false,
                'tes_score_right' => 10,
                'tes_score_wrong' => 0,
                'tes_score_unanswered' => 0,
                'tes_max_score' => 100,
                'tes_biaya' => 75000,
                'tes_token' => false,
            ]
        );

        $matematika->groups()->sync($grups);
        $matematika->topikSets()->delete();
        $matematika->topikSets()->create([
            'tset_topik_id' => $aljabar->topik_id,
            'tset_tipe' => 1,
            'tset_difficulty' => 2,
            'tset_jumlah' => 6,
            'tset_jawaban' => 0,
            'tset_acak_jawaban' => 1,
            'tset_acak_soal' => 1,
        ]);
        $matematika->topikSets()->create([
            'tset_topik_id' => $geometri->topik_id,
            'tset_tipe' => 1,
            'tset_difficulty' => 1,
            'tset_jumlah' => 4,
            'tset_jawaban' => 0,
            'tset_acak_jawaban' => 1,
            'tset_acak_soal' => 1,
        ]);

        $bahasa = Tes::updateOrCreate(
            ['tes_nama' => 'Ulangan Bahasa Indonesia'],
            [
                'tes_detail' => 'Ulangan harian Bahasa Indonesia materi teks eksposisi.',
                'tes_begin_time' => now()->subDay(),
                'tes_end_time' => now()->addDays(14),
                'tes_duration_time' => 45,
                'tes_ip_range' => '*.*.*.*',
                'tes_results_to_users' => true,
                'tes_detail_to_users' => false,
                'tes_ranking_to_users' => true,
                'tes_score_right' => 20,
                'tes_score_wrong' => 0,
                'tes_score_unanswered' => 0,
                'tes_max_score' => 100,
                'tes_biaya' => 50000,
                'tes_token' => true,
            ]
        );

        $bahasa->groups()->sync($grups);
        $bahasa->topikSets()->delete();
        $bahasa->topikSets()->create([
            'tset_topik_id' => $eksposisi->topik_id,
            'tset_tipe' => 1,
            'tset_difficulty' => 2,
            'tset_jumlah' => 5,
            'tset_jawaban' => 0,
            'tset_acak_jawaban' => 1,
            'tset_acak_soal' => 1,
        ]);

        TesToken::where('token_isi', '123456')->whereDate('token_ts', now()->toDateString())->delete();
        TesToken::generate('123456', Admin::where('username', 'admin')->first()->id, $bahasa->tes_id, 1);
    }

    private function seedOrangTuaDanPembayaran(): void
    {
        $raka = Peserta::where('user_name', 'raka')->first();
        $siti = Peserta::where('user_name', 'siti')->first();
        $lutfi = Peserta::where('user_name', 'lutfi')->first();

        if (! $raka || ! $siti) {
            return;
        }

        $ortuRaka = OrangTua::updateOrCreate(
            ['ortu_username' => 'orangtua1'],
            [
                'ortu_nama' => 'Bapak Raka Pratama',
                'ortu_password' => Hash::make('orangtua1'),
                'ortu_email' => 'orangtua1@example.com',
                'ortu_telepon' => '081234567890',
            ]
        );

        $ortuSiti = OrangTua::updateOrCreate(
            ['ortu_username' => 'orangtua2'],
            [
                'ortu_nama' => 'Ibu Aminah',
                'ortu_password' => Hash::make('orangtua2'),
                'ortu_email' => 'orangtua2@example.com',
                'ortu_telepon' => '081298765432',
            ]
        );

        $anakOrtuRaka = [$raka->user_id];
        if ($lutfi) {
            $anakOrtuRaka[] = $lutfi->user_id;
        }

        $ortuRaka->pesertas()->sync($anakOrtuRaka);
        $ortuSiti->pesertas()->sync([$siti->user_id]);

        $matematika = Tes::where('tes_nama', 'Try Out Matematika')->first();
        $bahasa = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->first();
        $admin = Admin::where('username', 'admin')->first();

        $bukti = 'bukti-pembayaran/demo-bukti.svg';

        if (! app()->environment('testing')) {
            $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="720" height="360" viewBox="0 0 720 360">
  <rect width="720" height="360" fill="#f8fafc"/>
  <rect x="24" y="24" width="672" height="312" rx="18" fill="#ffffff" stroke="#c7d2fe" stroke-width="3"/>
  <text x="360" y="120" font-family="Arial, sans-serif" font-size="30" font-weight="bold" fill="#4338ca" text-anchor="middle">BUKTI PEMBAYARAN DEMO</text>
  <text x="360" y="175" font-family="Arial, sans-serif" font-size="20" fill="#334155" text-anchor="middle">Pembayaran biaya ujian CBT Online</text>
  <text x="360" y="230" font-family="Arial, sans-serif" font-size="18" fill="#64748b" text-anchor="middle">File ini hanya contoh untuk data seeder.</text>
</svg>
SVG;

            Storage::disk('public')->put($bukti, $svg);
        }

        $pembayarans = [
            [
                'peserta' => $raka,
                'tes' => $matematika,
                'ortu' => $ortuRaka,
                'status' => Pembayaran::DIVERIFIKASI,
                'catatan' => 'Transfer dari rekening Bapak Raka.',
                'catatan_admin' => 'Pembayaran sesuai.',
                'verified' => true,
            ],
            [
                'peserta' => $raka,
                'tes' => $bahasa,
                'ortu' => $ortuRaka,
                'status' => Pembayaran::MENUNGGU,
                'catatan' => 'Mohon diverifikasi.',
                'catatan_admin' => null,
                'verified' => false,
            ],
            [
                'peserta' => $siti,
                'tes' => $matematika,
                'ortu' => $ortuSiti,
                'status' => Pembayaran::DITOLAK,
                'catatan' => 'Sudah transfer.',
                'catatan_admin' => 'Nominal belum sesuai. Mohon unggah ulang bukti yang benar.',
                'verified' => true,
            ],
        ];

        foreach ($pembayarans as $item) {
            if (! $item['tes']) {
                continue;
            }

            Pembayaran::updateOrCreate(
                [
                    'pembayaran_user_id' => $item['peserta']->user_id,
                    'pembayaran_tes_id' => $item['tes']->tes_id,
                ],
                [
                    'pembayaran_ortu_id' => $item['ortu']->ortu_id,
                    'pembayaran_nominal' => $item['tes']->tes_biaya,
                    'pembayaran_status' => $item['status'],
                    'pembayaran_bukti' => $item['verified'] || $item['status'] === Pembayaran::MENUNGGU ? $bukti : null,
                    'pembayaran_catatan' => $item['catatan'],
                    'pembayaran_catatan_admin' => $item['catatan_admin'],
                    'pembayaran_verified_by' => $item['verified'] ? $admin?->id : null,
                    'pembayaran_verified_at' => $item['verified'] ? now()->subDay() : null,
                ]
            );
        }

        $absensis = [
            ['peserta' => $raka, 'tes' => $matematika, 'tanggal' => now()->subDays(4)->toDateString(), 'status' => Absensi::HADIR, 'sumber' => Absensi::SUMBER_UJIAN, 'keterangan' => null],
            ['peserta' => $siti, 'tes' => $bahasa, 'tanggal' => now()->subDays(2)->toDateString(), 'status' => Absensi::HADIR, 'sumber' => Absensi::SUMBER_UJIAN, 'keterangan' => null],
            ['peserta' => $raka, 'tes' => null, 'tanggal' => now()->subDays(3)->toDateString(), 'status' => Absensi::IZIN, 'sumber' => Absensi::SUMBER_MANUAL, 'keterangan' => 'Acara keluarga.'],
            ['peserta' => $siti, 'tes' => null, 'tanggal' => now()->subDay()->toDateString(), 'status' => Absensi::SAKIT, 'sumber' => Absensi::SUMBER_MANUAL, 'keterangan' => 'Surat keterangan dokter.'],
        ];

        foreach ($absensis as $item) {
            $kunci = [
                'absensi_user_id' => $item['peserta']->user_id,
                'absensi_tes_id' => $item['tes']?->tes_id,
            ];

            if (! $item['tes']) {
                $kunci['absensi_tanggal'] = $item['tanggal'];
            }

            Absensi::updateOrCreate($kunci, [
                'absensi_tanggal' => $item['tanggal'],
                'absensi_status' => $item['status'],
                'absensi_sumber' => $item['sumber'],
                'absensi_keterangan' => $item['keterangan'],
            ]);
        }
    }

    private function seedRiwayat(): void
    {
        $tes = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->first();
        $topik = Topik::where('topik_nama', 'Teks Eksposisi')->first();
        $soal = Soal::where('soal_topik_id', $topik->topik_id)->where('soal_tipe', 1)->orderBy('soal_id')->get();

        $riwayat = [
            ['user_name' => 'siti', 'benar' => 4, 'hari' => 4],
            ['user_name' => 'budi', 'benar' => 3, 'hari' => 3],
            ['user_name' => 'rina', 'benar' => 5, 'hari' => 2],
            ['user_name' => 'andi', 'benar' => 2, 'hari' => 1],
        ];

        foreach ($riwayat as $data) {
            $peserta = Peserta::where('user_name', $data['user_name'])->first();

            if (! $peserta || TesUser::where('tesuser_tes_id', $tes->tes_id)->where('tesuser_user_id', $peserta->user_id)->exists()) {
                continue;
            }

            $waktu = now()->subDays($data['hari'])->setTime(9, 0);

            $attempt = TesUser::create([
                'tesuser_tes_id' => $tes->tes_id,
                'tesuser_user_id' => $peserta->user_id,
                'tesuser_status' => TesUser::STATUS_SELESAI,
                'tesuser_creation_time' => $waktu,
                'tesuser_token' => '123456',
            ]);

            $nomor = 1;

            foreach ($soal as $index => $item) {
                $tesSoal = TesSoal::create([
                    'tessoal_tesuser_id' => $attempt->tesuser_id,
                    'tessoal_soal_id' => $item->soal_id,
                    'tessoal_creation_time' => $waktu,
                    'tessoal_display_time' => $waktu,
                    'tessoal_change_time' => $waktu->copy()->addMinutes($nomor),
                    'tessoal_order' => $nomor++,
                    'tessoal_reaction_time' => 60,
                ]);

                $jawabans = $item->jawabans()->get();
                $pilihBenar = $index < $data['benar'];
                $benarId = $jawabans->firstWhere('jawaban_benar', 1)?->jawaban_id;
                $salahId = $jawabans->firstWhere('jawaban_benar', 0)?->jawaban_id;
                $dipilih = $pilihBenar ? $benarId : $salahId;

                foreach ($jawabans as $urutan => $jawaban) {
                    TesSoalJawaban::create([
                        'soaljawaban_tessoal_id' => $tesSoal->tessoal_id,
                        'soaljawaban_jawaban_id' => $jawaban->jawaban_id,
                        'soaljawaban_selected' => $jawaban->jawaban_id === $dipilih ? 1 : -1,
                        'soaljawaban_order' => $urutan + 1,
                    ]);
                }

                $tesSoal->update([
                    'tessoal_nilai' => $pilihBenar ? $tes->tes_score_right : $tes->tes_score_wrong,
                ]);
            }
        }
    }

    private function sinkronkanSekolah(): void
    {
        $foundation = Foundation::where('foundation_code', 'YPN')->firstOrFail();
        $schools = School::with('level')
            ->where('foundation_id', $foundation->id)
            ->get()
            ->keyBy(fn (School $school) => strtoupper((string) $school->level?->level_code));

        $default = $schools->get('SMA') ?? $schools->first();
        $tahunAktif = AcademicYear::whereIn('school_id', $schools->pluck('id'))
            ->where('is_active', true)
            ->pluck('id', 'school_id');

        foreach (UserGroup::all() as $grup) {
            $school = $schools->get(strtoupper((string) $grup->grup_jenjang)) ?? $default;

            $grup->update([
                'grup_foundation_id' => $foundation->id,
                'grup_school_id' => $school->id,
                'grup_academic_year_id' => $tahunAktif->get($school->id),
            ]);
        }

        foreach (Peserta::with('grup')->get() as $peserta) {
            $schoolId = $peserta->grup?->grup_school_id ?? $default->id;

            $peserta->update([
                'user_foundation_id' => $foundation->id,
                'user_school_id' => $schoolId,
                'user_nis' => $peserta->user_nis ?: $peserta->user_name,
            ]);
        }

        foreach (Tes::with('groups')->get() as $tes) {
            $schoolId = $tes->groups->first()?->grup_school_id ?? $default->id;

            $tes->update([
                'tes_foundation_id' => $foundation->id,
                'tes_school_id' => $schoolId,
                'tes_academic_year_id' => $tahunAktif->get($schoolId),
            ]);
        }

        foreach (Absensi::with('peserta')->get() as $absensi) {
            $schoolId = $absensi->peserta?->user_school_id ?? $default->id;

            $absensi->update([
                'absensi_foundation_id' => $foundation->id,
                'absensi_school_id' => $schoolId,
            ]);
        }

        foreach (Pembayaran::with('peserta')->get() as $pembayaran) {
            $schoolId = $pembayaran->peserta?->user_school_id ?? $default->id;

            $pembayaran->update([
                'pembayaran_foundation_id' => $foundation->id,
                'pembayaran_school_id' => $schoolId,
            ]);
        }
    }

    /**
     * Data contoh lintas fitur yang saling tersinkron:
     * absensi harian, tagihan & pembayaran SPP, pembayaran ujian CBT,
     * raport, serta relasi orang tua ke peserta.
     */
    private function seedContohSinkron(): void
    {
        $this->bersihkanRantaiTesYatim();
        $this->sinkronKebijakanAbsensi();
        $this->sinkronAbsensiHarian();
        $this->buatBerkasBuktiDemo();
        $this->sinkronKategoriDanTarifSpp();
        $this->sinkronTagihanDanPembayaranSpp();
        $this->sinkronPembayaranCbt();
        $this->sinkronRaportPeserta();
        $this->sinkronRelasiOrangTua();
    }

    private function bersihkanRantaiTesYatim(): void
    {
        $orphans = DB::table('cbt_tes_user as tu')
            ->leftJoin('cbt_user as u', 'u.user_id', '=', 'tu.tesuser_user_id')
            ->leftJoin('cbt_tes as t', 't.tes_id', '=', 'tu.tesuser_tes_id')
            ->whereNull('u.user_id')
            ->orWhereNull('t.tes_id')
            ->pluck('tu.tesuser_id')
            ->all();

        foreach ($orphans as $tesUserId) {
            $soalIds = DB::table('cbt_tes_soal')
                ->where('tessoal_tesuser_id', $tesUserId)
                ->pluck('tessoal_id')
                ->all();

            if ($soalIds !== []) {
                DB::table('cbt_tes_soal_jawaban')->whereIn('soaljawaban_tessoal_id', $soalIds)->delete();
            }

            DB::table('cbt_tes_soal')->where('tessoal_tesuser_id', $tesUserId)->delete();
            DB::table('cbt_tes_user')->where('tesuser_id', $tesUserId)->delete();
        }
    }

    private function sinkronKebijakanAbsensi(): void
    {
        foreach (School::all() as $school) {
            AttendancePolicy::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'policy_type' => AttendancePolicy::TYPE_DAILY,
                    'academic_year_id' => null,
                ],
                [
                    'foundation_id' => $school->foundation_id,
                    'require_location' => true,
                    'allow_manual' => true,
                    'require_checkin' => true,
                    'require_checkout' => false,
                    'service_start' => '07:00:00',
                    'service_end' => '15:00:00',
                    'late_tolerance_minutes' => 15,
                    'max_accuracy_meters' => 100,
                    'is_required' => false,
                ]
            );

            AttendanceLocation::updateOrCreate(
                ['school_id' => $school->id, 'name' => 'Gedung Utama'],
                [
                    'foundation_id' => $school->foundation_id,
                    'latitude' => -6.2000000,
                    'longitude' => 106.8166667,
                    'radius_meters' => 50,
                    'max_accuracy_meters' => 100,
                    'is_active' => true,
                ]
            );
        }
    }

    private function sinkronAbsensiHarian(): void
    {
        foreach (Peserta::with('grup')->get() as $peserta) {
            $schoolId = $peserta->user_school_id ?: $peserta->grup?->grup_school_id;

            if (! $schoolId) {
                continue;
            }

            $school = School::find($schoolId);
            $location = AttendanceLocation::where('school_id', $schoolId)
                ->where('is_active', true)
                ->orderBy('id')
                ->first();

            for ($offset = 6; $offset >= 1; $offset--) {
                $date = now()->subDays($offset);

                if ($date->isWeekend()) {
                    continue;
                }

                $session = AttendanceSession::firstOrCreate(
                    [
                        'school_id' => $schoolId,
                        'exam_id' => null,
                        'session_type' => AttendanceSession::TYPE_DAILY,
                        'session_date' => $date->toDateString(),
                    ],
                    [
                        'foundation_id' => $school?->foundation_id,
                        'academic_year_id' => $peserta->grup?->grup_academic_year_id,
                        'starts_at' => $date->copy()->setTime(7, 0),
                        'ends_at' => $date->copy()->setTime(15, 0),
                        'location_id' => $location?->id,
                        'requires_checkin' => true,
                        'allow_manual' => true,
                    ]
                );

                $exists = AttendanceRecord::where('session_id', $session->id)
                    ->where('student_id', $peserta->user_id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $seed = ((int) $peserta->user_id + $offset) % 12;
                $status = match (true) {
                    $seed < 9 => AttendanceRecord::HADIR,
                    $seed === 9 => AttendanceRecord::TERLAMBAT,
                    $seed === 10 => AttendanceRecord::IZIN,
                    default => AttendanceRecord::SAKIT,
                };

                $checkIn = null;

                if (in_array($status, [AttendanceRecord::HADIR, AttendanceRecord::TERLAMBAT], true)) {
                    $hour = $status === AttendanceRecord::TERLAMBAT ? 7 : 6;
                    $minute = ($status === AttendanceRecord::TERLAMBAT ? 30 : 35) + ($seed % 25);
                    $checkIn = $date->copy()->setTime($hour, $minute);
                }

                AttendanceRecord::create([
                    'session_id' => $session->id,
                    'foundation_id' => $session->foundation_id,
                    'school_id' => $session->school_id,
                    'student_id' => $peserta->user_id,
                    'status' => $status,
                    'method' => AttendanceRecord::METHOD_GPS,
                    'check_in_at' => $checkIn,
                    'check_out_at' => $checkIn?->copy()->setTime(15, 5 + ($seed % 40)),
                    'location_id' => $session->location_id,
                    'distance_meters' => $checkIn ? 3 + ($seed % 30) : null,
                    'accuracy_meters' => $checkIn ? 5 + ($seed % 20) : null,
                    'is_override' => false,
                    'notes' => null,
                    'actor_id' => null,
                ]);
            }
        }
    }

    private function buatBerkasBuktiDemo(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="720" height="360" viewBox="0 0 720 360">
  <rect width="720" height="360" fill="#f8fafc"/>
  <rect x="24" y="24" width="672" height="312" rx="18" fill="#ffffff" stroke="#c7d2fe" stroke-width="3"/>
  <text x="360" y="120" font-family="Arial, sans-serif" font-size="30" font-weight="bold" fill="#4338ca" text-anchor="middle">BUKTI PEMBAYARAN DEMO</text>
  <text x="360" y="175" font-family="Arial, sans-serif" font-size="20" fill="#334155" text-anchor="middle">Pembayaran sekolah / biaya ujian CBT</text>
  <text x="360" y="230" font-family="Arial, sans-serif" font-size="18" fill="#64748b" text-anchor="middle">File ini hanya contoh untuk data seeder.</text>
</svg>
SVG;

        Storage::disk('public')->put('bukti-pembayaran/demo-bukti.svg', $svg);
        Storage::disk('public')->put('bukti-spp/sample-bukti.svg', $svg);
    }

    private function sinkronKategoriDanTarifSpp(): void
    {
        foreach (School::all() as $school) {
            foreach ([
                ['code' => 'SPP', 'name' => 'SPP Bulanan', 'recurring' => true, 'amount' => 250000],
                ['code' => 'GEDUNG', 'name' => 'Uang Gedung', 'recurring' => false, 'amount' => 1500000],
                ['code' => 'KEGIATAN', 'name' => 'Kegiatan Sekolah', 'recurring' => false, 'amount' => 350000],
            ] as $item) {
                $category = FeeCategory::updateOrCreate(
                    ['school_id' => $school->id, 'category_code' => $item['code']],
                    [
                        'foundation_id' => $school->foundation_id,
                        'category_name' => $item['name'],
                        'is_recurring' => $item['recurring'],
                        'is_active' => true,
                    ]
                );

                FeeStructure::firstOrCreate(
                    [
                        'school_id' => $school->id,
                        'fee_category_id' => $category->id,
                        'academic_year_id' => null,
                        'grade_code' => null,
                    ],
                    [
                        'foundation_id' => $school->foundation_id,
                        'amount' => $item['amount'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function sinkronTagihanDanPembayaranSpp(): void
    {
        $admin = Admin::where('username', 'admin')->first();

        foreach (Peserta::with('grup', 'orangTuas')->get() as $peserta) {
            $schoolId = $peserta->user_school_id ?: $peserta->grup?->grup_school_id;

            if (! $schoolId) {
                continue;
            }

            $school = School::find($schoolId);
            $spp = FeeCategory::where('school_id', $schoolId)->where('category_code', 'SPP')->first();

            if (! $school || ! $spp) {
                continue;
            }

            $billNumber = 'INV-'.$school->school_code.'-'.str_pad((string) $peserta->user_id, 4, '0', STR_PAD_LEFT);
            $bill = StudentBill::where('school_id', $schoolId)->where('bill_number', $billNumber)->first();

            if (! $bill) {
                $amount = 250000;

                $bill = StudentBill::create([
                    'uuid' => (string) Str::uuid(),
                    'foundation_id' => $school->foundation_id,
                    'school_id' => $schoolId,
                    'academic_year_id' => $peserta->grup?->grup_academic_year_id,
                    'student_id' => $peserta->user_id,
                    'bill_number' => $billNumber,
                    'issue_date' => now()->subDays(10)->toDateString(),
                    'due_date' => now()->addDays(20)->toDateString(),
                    'total_amount' => $amount,
                    'paid_amount' => 0,
                    'status' => StudentBill::STATUS_DRAFT,
                    'notes' => 'Tagihan SPP sample',
                    'created_by' => $admin?->id,
                ]);

                StudentBillItem::create([
                    'bill_id' => $bill->id,
                    'fee_category_id' => $spp->id,
                    'description' => 'SPP Bulanan',
                    'quantity' => 1,
                    'unit_amount' => $amount,
                    'line_total' => $amount,
                ]);
            }

            if (SchoolPayment::where('bill_id', $bill->id)->exists()) {
                continue;
            }

            $seed = (int) $peserta->user_id % 4;

            if ($seed === 3) {
                continue;
            }

            $guardian = $peserta->orangTuas()->first();
            $status = match ($seed) {
                0 => SchoolPayment::STATUS_VERIFIED,
                1 => SchoolPayment::STATUS_PENDING,
                default => SchoolPayment::STATUS_REJECTED,
            };
            $amount = $seed === 1 ? 100000 : 250000;

            $payment = SchoolPayment::create([
                'uuid' => (string) Str::uuid(),
                'foundation_id' => $bill->foundation_id,
                'school_id' => $bill->school_id,
                'student_id' => $bill->student_id,
                'bill_id' => $bill->id,
                'guardian_id' => $guardian?->ortu_id,
                'payment_method_id' => null,
                'recorded_by' => $seed === 0 ? $admin?->id : null,
                'verified_by' => $seed === 0 ? $admin?->id : null,
                'verified_at' => $seed === 0 ? now()->subDays(5) : null,
                'payment_number' => 'PAY-'.$school->school_code.'-'.now()->format('Ym').'-'.strtoupper(Str::random(6)),
                'amount' => $amount,
                'status' => $status,
                'proof_path' => $status === SchoolPayment::STATUS_PENDING ? 'bukti-spp/sample-bukti.svg' : null,
                'paid_at' => $seed === 0 ? now()->subDays(5) : null,
                'notes' => 'Pembayaran sample',
                'rejection_reason' => $status === SchoolPayment::STATUS_REJECTED ? 'Nominal belum sesuai dengan tagihan.' : null,
            ]);

            if ($seed === 0) {
                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'bill_id' => $bill->id,
                    'allocated_amount' => $amount,
                ]);

                $bill->update([
                    'paid_amount' => $amount,
                    'status' => StudentBill::STATUS_PAID,
                ]);
            }
        }
    }

    private function sinkronPembayaranCbt(): void
    {
        $admin = Admin::where('username', 'admin')->first();

        foreach (Tes::where('tes_biaya', '>', 0)->get() as $tes) {
            foreach (TesUser::where('tesuser_tes_id', $tes->tes_id)->get() as $attempt) {
                $peserta = Peserta::find($attempt->tesuser_user_id);

                if (! $peserta) {
                    continue;
                }

                $existing = Pembayaran::where('pembayaran_user_id', $peserta->user_id)
                    ->where('pembayaran_tes_id', $tes->tes_id)
                    ->first();

                if ($existing) {
                    continue;
                }

                $seed = (int) $peserta->user_id % 3;
                $status = match ($seed) {
                    0 => Pembayaran::DIVERIFIKASI,
                    1 => Pembayaran::MENUNGGU,
                    default => Pembayaran::DITOLAK,
                };
                $guardian = $peserta->orangTuas()->first();

                if (! $guardian) {
                    $guardian = OrangTua::create([
                        'ortu_nama' => 'Orang Tua '.$peserta->user_firstname,
                        'ortu_username' => 'ortu'.$peserta->user_id,
                        'ortu_password' => Hash::make('ortu'.$peserta->user_id),
                        'ortu_email' => 'ortu'.$peserta->user_id.'@example.com',
                        'ortu_telepon' => '0812'.str_pad((string) $peserta->user_id, 8, '0', STR_PAD_LEFT),
                    ]);

                    $guardian->pesertas()->attach($peserta->user_id);
                }

                Pembayaran::create([
                    'pembayaran_user_id' => $peserta->user_id,
                    'pembayaran_foundation_id' => $peserta->user_foundation_id,
                    'pembayaran_school_id' => $peserta->user_school_id,
                    'pembayaran_tes_id' => $tes->tes_id,
                    'pembayaran_ortu_id' => $guardian?->ortu_id,
                    'pembayaran_nominal' => (float) $tes->tes_biaya,
                    'pembayaran_status' => $status,
                    'pembayaran_bukti' => 'bukti-pembayaran/demo-bukti.svg',
                    'pembayaran_catatan' => 'Pembayaran biaya ujian sample',
                    'pembayaran_catatan_admin' => $status === Pembayaran::DITOLAK ? 'Nominal belum sesuai.' : null,
                    'pembayaran_verified_by' => $status === Pembayaran::DIVERIFIKASI ? $admin?->id : null,
                    'pembayaran_verified_at' => $status === Pembayaran::DIVERIFIKASI ? now()->subDays(2) : null,
                ]);
            }
        }
    }

    private function sinkronRaportPeserta(): void
    {
        $moduls = Modul::where('modul_aktif', true)->get();
        $guru = Admin::where('username', 'gurua')->first() ?? Admin::where('username', 'admin')->first();

        foreach (Peserta::with('grup')->get() as $peserta) {
            $grup = $peserta->grup;

            if (! $grup) {
                continue;
            }

            foreach ($moduls as $modul) {
                Raport::firstOrCreate(
                    [
                        'raport_user_id' => $peserta->user_id,
                        'raport_modul_id' => $modul->modul_id,
                        'raport_jenjang' => $grup->grup_jenjang,
                        'raport_tingkat' => $grup->grup_tingkat,
                    ],
                    [
                        'raport_guru_id' => $guru?->id,
                        'raport_nilai' => 70 + (((int) $peserta->user_id + $modul->modul_id) % 28),
                        'raport_catatan' => null,
                    ]
                );
            }
        }
    }

    private function sinkronRelasiOrangTua(): void
    {
        $ortu1 = OrangTua::where('ortu_username', 'orangtua1')->first();
        $ortu2 = OrangTua::where('ortu_username', 'orangtua2')->first();

        if (! $ortu1 || ! $ortu2) {
            return;
        }

        $anak1 = Peserta::whereIn('user_name', ['raka', 'lutfi', 'dewi'])->pluck('user_id')->all();
        $anak2 = Peserta::whereIn('user_name', ['siti', 'budi', 'rina'])->pluck('user_id')->all();

        $ortu1->pesertas()->sync(array_values(array_unique(array_filter($anak1))));
        $ortu2->pesertas()->sync(array_values(array_unique(array_filter($anak2))));
    }
}
