<?php

use App\Http\Controllers\Admin\AbsensiController as AdminAbsensiController;
use App\Http\Controllers\Admin\AttendanceMonitoringController;
use App\Http\Controllers\Admin\AttendanceSettingController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FoundationController;
use App\Http\Controllers\Admin\GrupController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\HasilController;
use App\Http\Controllers\Admin\KelulusanController as AdminKelulusanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\ModulController;
use App\Http\Controllers\Admin\OrangTuaController as AdminOrangTuaController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\PesertaController;
use App\Http\Controllers\Admin\PreferenceController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SoalController;
use App\Http\Controllers\Admin\SppController;
use App\Http\Controllers\Admin\TesController;
use App\Http\Controllers\Admin\TokenController;
use App\Http\Controllers\Admin\TopikController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\OrangTua\AbsensiController as OrangTuaAbsensiController;
use App\Http\Controllers\OrangTua\AuthController as OrangTuaAuthController;
use App\Http\Controllers\OrangTua\DashboardController as OrangTuaDashboardController;
use App\Http\Controllers\OrangTua\PembayaranController as OrangTuaPembayaranController;
use App\Http\Controllers\OrangTua\SppController as OrangTuaSppController;
use App\Http\Controllers\Peserta\AbsensiController as PesertaAbsensiController;
use App\Http\Controllers\Peserta\AuthController as PesertaAuthController;
use App\Http\Controllers\Peserta\DashboardController as PesertaDashboardController;
use App\Http\Controllers\Peserta\KelulusanController as PesertaKelulusanController;
use App\Http\Controllers\Peserta\PrestasiController as PesertaPrestasiController;
use App\Http\Controllers\Peserta\RankingController as PesertaRankingController;
use App\Http\Controllers\Peserta\RaportController as PesertaRaportController;
use App\Http\Controllers\Peserta\UjianController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::guard('peserta')->check()) {
        return redirect()->route('peserta.dashboard');
    }

    if (Auth::guard('web')->check()) {
        return redirect()->route('admin.dashboard');
    }

    if (Auth::guard('orangtua')->check()) {
        return redirect()->route('orangtua.dashboard');
    }

    return redirect()->route('peserta.login');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.post')->middleware('throttle:login');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['admin.access', 'school.context'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::put('password', [AdminDashboardController::class, 'password'])->name('password');
        Route::get('preferensi-tampilan', [PreferenceController::class, 'edit'])->name('preferences.edit');
        Route::put('preferensi-tampilan', [PreferenceController::class, 'update'])->name('preferences.update');
        Route::post('sekolah/switch', [SchoolController::class, 'switch'])->name('sekolah.switch');

        Route::middleware('admin.access:yayasan')->group(function () {
            Route::get('yayasan', [FoundationController::class, 'index'])->name('yayasan.index');
            Route::post('yayasan', [FoundationController::class, 'store'])->name('yayasan.store')->middleware('admin.access:yayasan,add');
            Route::put('yayasan/{foundation}', [FoundationController::class, 'update'])->name('yayasan.update')->middleware('admin.access:yayasan,edit');
            Route::delete('yayasan/{foundation}', [FoundationController::class, 'destroy'])->name('yayasan.destroy')->middleware('admin.access:yayasan,delete');
        });

        Route::middleware('admin.access:sekolah')->group(function () {
            Route::get('sekolah', [SchoolController::class, 'index'])->name('sekolah.index');
            Route::post('sekolah', [SchoolController::class, 'store'])->name('sekolah.store')->middleware('admin.access:sekolah,add');
            Route::put('sekolah/{school}', [SchoolController::class, 'update'])->name('sekolah.update')->middleware('admin.access:sekolah,edit');
            Route::delete('sekolah/{school}', [SchoolController::class, 'destroy'])->name('sekolah.destroy')->middleware('admin.access:sekolah,delete');
            Route::post('sekolah/tahun-ajaran', [SchoolController::class, 'storeYear'])->name('sekolah.tahun.store')->middleware('admin.access:sekolah,add');
            Route::put('sekolah/tahun-ajaran/{academicYear}', [SchoolController::class, 'updateYear'])->name('sekolah.tahun.update')->middleware('admin.access:sekolah,edit');
            Route::delete('sekolah/tahun-ajaran/{academicYear}', [SchoolController::class, 'destroyYear'])->name('sekolah.tahun.destroy')->middleware('admin.access:sekolah,delete');
        });

        Route::middleware('admin.access:modul')->group(function () {
            Route::get('modul', [ModulController::class, 'index'])->name('modul.index');
            Route::post('modul', [ModulController::class, 'store'])->name('modul.store')->middleware('admin.access:modul,add');
            Route::put('modul/{modul}', [ModulController::class, 'update'])->name('modul.update')->middleware('admin.access:modul,edit');
            Route::delete('modul/{modul}', [ModulController::class, 'destroy'])->name('modul.destroy')->middleware('admin.access:modul,delete');
        });

        Route::middleware('admin.access:topik')->group(function () {
            Route::get('topik', [TopikController::class, 'index'])->name('topik.index');
            Route::post('topik', [TopikController::class, 'store'])->name('topik.store')->middleware('admin.access:topik,add');
            Route::put('topik/{topik}', [TopikController::class, 'update'])->name('topik.update')->middleware('admin.access:topik,edit');
            Route::delete('topik/{topik}', [TopikController::class, 'destroy'])->name('topik.destroy')->middleware('admin.access:topik,delete');
        });

        Route::middleware('admin.access:soal')->group(function () {
            Route::get('soal', [SoalController::class, 'index'])->name('soal.index');
            Route::get('soal/grup/{topik}', [SoalController::class, 'show'])->name('soal.show');
            Route::post('soal/grup', [SoalController::class, 'storeTopik'])->name('soal.grup.store')->middleware('admin.access:soal,add');
            Route::put('soal/grup/{topik}', [SoalController::class, 'updateTopik'])->name('soal.grup.update')->middleware('admin.access:soal,edit');
            Route::delete('soal/grup/{topik}', [SoalController::class, 'destroyTopik'])->name('soal.grup.destroy')->middleware('admin.access:soal,delete');
            Route::get('soal/create', [SoalController::class, 'create'])->name('soal.create');
            Route::get('soal/template', [SoalController::class, 'template'])->name('soal.template');
            Route::post('soal/import', [SoalController::class, 'import'])->name('soal.import')->middleware('admin.access:soal,add');
            Route::post('soal', [SoalController::class, 'store'])->name('soal.store')->middleware('admin.access:soal,add');
            Route::post('soal/upload', [SoalController::class, 'upload'])->name('soal.upload')->middleware('admin.access:soal,add');
            Route::get('soal/{soal}/edit', [SoalController::class, 'edit'])->name('soal.edit');
            Route::put('soal/{soal}', [SoalController::class, 'update'])->name('soal.update')->middleware('admin.access:soal,edit');
            Route::delete('soal/{soal}', [SoalController::class, 'destroy'])->name('soal.destroy')->middleware('admin.access:soal,delete');
        });

        Route::middleware('admin.access:grup')->group(function () {
            Route::get('grup', [GrupController::class, 'index'])->name('grup.index');
            Route::post('grup', [GrupController::class, 'store'])->name('grup.store')->middleware('admin.access:grup,add');
            Route::put('grup/{grup}', [GrupController::class, 'update'])->name('grup.update')->middleware('admin.access:grup,edit');
            Route::delete('grup/{grup}', [GrupController::class, 'destroy'])->name('grup.destroy')->middleware('admin.access:grup,delete');
        });

        Route::middleware('admin.access:peserta')->group(function () {
            Route::get('peserta/template', [PesertaController::class, 'template'])->name('peserta.template');
            Route::post('peserta/import', [PesertaController::class, 'import'])->name('peserta.import')->middleware('admin.access:peserta,add');
            Route::get('peserta', [PesertaController::class, 'index'])->name('peserta.index');
            Route::get('peserta/create', [PesertaController::class, 'create'])->name('peserta.create');
            Route::post('peserta', [PesertaController::class, 'store'])->name('peserta.store')->middleware('admin.access:peserta,add');
            Route::get('peserta/{peserta}/edit', [PesertaController::class, 'edit'])->name('peserta.edit');
            Route::put('peserta/{peserta}', [PesertaController::class, 'update'])->name('peserta.update')->middleware('admin.access:peserta,edit');
            Route::delete('peserta/{peserta}', [PesertaController::class, 'destroy'])->name('peserta.destroy')->middleware('admin.access:peserta,delete');
        });

        Route::middleware('admin.access:orangtua')->group(function () {
            Route::get('orangtua', [AdminOrangTuaController::class, 'index'])->name('orangtua.index');
            Route::get('orangtua/create', [AdminOrangTuaController::class, 'create'])->name('orangtua.create');
            Route::post('orangtua', [AdminOrangTuaController::class, 'store'])->name('orangtua.store')->middleware('admin.access:orangtua,add');
            Route::get('orangtua/{orangTua}/edit', [AdminOrangTuaController::class, 'edit'])->name('orangtua.edit');
            Route::put('orangtua/{orangTua}', [AdminOrangTuaController::class, 'update'])->name('orangtua.update')->middleware('admin.access:orangtua,edit');
            Route::delete('orangtua/{orangTua}', [AdminOrangTuaController::class, 'destroy'])->name('orangtua.destroy')->middleware('admin.access:orangtua,delete');
        });

        Route::middleware('admin.access:absensi-lokasi')->group(function () {
            Route::get('absensi/lokasi', [AttendanceSettingController::class, 'index'])->name('absensi.lokasi');
            Route::post('absensi/lokasi', [AttendanceSettingController::class, 'storeLocation'])->name('absensi.lokasi.store')->middleware('admin.access:absensi-lokasi,add');
            Route::put('absensi/lokasi/{location}', [AttendanceSettingController::class, 'updateLocation'])->name('absensi.lokasi.update')->middleware('admin.access:absensi-lokasi,edit');
            Route::delete('absensi/lokasi/{location}', [AttendanceSettingController::class, 'destroyLocation'])->name('absensi.lokasi.destroy')->middleware('admin.access:absensi-lokasi,delete');
            Route::put('absensi/kebijakan', [AttendanceSettingController::class, 'updatePolicy'])->name('absensi.lokasi.policy')->middleware('admin.access:absensi-lokasi,edit');
            Route::post('absensi/lokasi/geocode', [AttendanceSettingController::class, 'geocode'])->name('absensi.lokasi.geocode')->middleware('admin.access:absensi-lokasi,add');
        });

        Route::middleware('admin.access:absensi-monitoring')->group(function () {
            Route::get('absensi/monitoring', [AttendanceMonitoringController::class, 'index'])->name('absensi.monitoring');
            Route::get('absensi/monitoring/sesi/{session}', [AttendanceMonitoringController::class, 'show'])->name('absensi.monitoring.show');
            Route::get('absensi/monitoring/ujian/{tes}', [AttendanceMonitoringController::class, 'showTes'])->name('absensi.monitoring.tes');
            Route::post('absensi/monitoring/override', [AttendanceMonitoringController::class, 'override'])->name('absensi.monitoring.override')->middleware('admin.access:absensi-monitoring,edit');
        });

        Route::middleware('admin.access:absensi')->group(function () {
            Route::get('absensi', [AdminAbsensiController::class, 'index'])->name('absensi.index');
            Route::post('absensi', [AdminAbsensiController::class, 'store'])->name('absensi.store')->middleware('admin.access:absensi,add');
            Route::put('absensi/{absensi}', [AdminAbsensiController::class, 'update'])->name('absensi.update')->middleware('admin.access:absensi,edit');
            Route::delete('absensi/{absensi}', [AdminAbsensiController::class, 'destroy'])->name('absensi.destroy')->middleware('admin.access:absensi,delete');
        });

        Route::middleware('admin.access:pembayaran')->group(function () {
            Route::get('pembayaran', [AdminPembayaranController::class, 'index'])->name('pembayaran.index');
            Route::put('pembayaran/{pembayaran}/verifikasi', [AdminPembayaranController::class, 'verifikasi'])->name('pembayaran.verifikasi')->middleware('admin.access:pembayaran,approve');
            Route::get('pembayaran/{pembayaran}/bukti', [AdminPembayaranController::class, 'bukti'])->name('pembayaran.bukti');
        });

        Route::prefix('spp')->name('spp.')->group(function () {
            Route::middleware('admin.access:spp')->group(function () {
                Route::get('/', [SppController::class, 'index'])->name('index');
                Route::get('tagihan/create', [SppController::class, 'create'])->name('create');
                Route::post('tagihan', [SppController::class, 'storeBill'])->name('store')->middleware('admin.access:spp,add');
                Route::get('tagihan/{studentBill}', [SppController::class, 'show'])->name('show');
                Route::delete('tagihan/{studentBill}', [SppController::class, 'destroyBill'])->name('destroy')->middleware('admin.access:spp,delete');
            });

            Route::middleware('admin.access:spp-pengaturan')->group(function () {
                Route::get('pengaturan', [SppController::class, 'setup'])->name('setup');
                Route::post('kategori', [SppController::class, 'storeCategory'])->name('kategori.store')->middleware('admin.access:spp-pengaturan,add');
                Route::put('kategori/{feeCategory}', [SppController::class, 'updateCategory'])->name('kategori.update')->middleware('admin.access:spp-pengaturan,edit');
                Route::delete('kategori/{feeCategory}', [SppController::class, 'destroyCategory'])->name('kategori.destroy')->middleware('admin.access:spp-pengaturan,delete');
                Route::post('tarif', [SppController::class, 'storeStructure'])->name('tarif.store')->middleware('admin.access:spp-pengaturan,add');
                Route::put('tarif/{feeStructure}', [SppController::class, 'updateStructure'])->name('tarif.update')->middleware('admin.access:spp-pengaturan,edit');
                Route::delete('tarif/{feeStructure}', [SppController::class, 'destroyStructure'])->name('tarif.destroy')->middleware('admin.access:spp-pengaturan,delete');
                Route::post('metode', [SppController::class, 'storeMethod'])->name('metode.store')->middleware('admin.access:spp-pengaturan,add');
                Route::put('metode/{paymentMethod}', [SppController::class, 'updateMethod'])->name('metode.update')->middleware('admin.access:spp-pengaturan,edit');
                Route::delete('metode/{paymentMethod}', [SppController::class, 'destroyMethod'])->name('metode.destroy')->middleware('admin.access:spp-pengaturan,delete');
            });

            Route::middleware('admin.access:spp-pembayaran')->group(function () {
                Route::get('pembayaran', [SppController::class, 'payments'])->name('payments');
                Route::get('pembayaran/{payment}', [SppController::class, 'paymentShow'])->name('payment.show');
                Route::get('pembayaran/{payment}/bukti', [SppController::class, 'proof'])->name('payment.proof');
                Route::get('pembayaran/{payment}/kwitansi', [SppController::class, 'receipt'])->name('payment.receipt');
                Route::post('pembayaran/{payment}/verifikasi', [SppController::class, 'verifyPayment'])->name('payment.verify')->middleware('admin.access:spp-pembayaran,approve');
                Route::delete('pembayaran/{payment}/batalkan', [SppController::class, 'cancelPayment'])->name('payment.cancel')->middleware('admin.access:spp-pembayaran,delete');
                Route::post('tagihan/{studentBill}/bayar', [SppController::class, 'storePayment'])->name('bayar')->middleware('admin.access:spp-pembayaran,add');
            });
        });

        Route::middleware('admin.access:tes')->group(function () {
            Route::get('tes', [TesController::class, 'index'])->name('tes.index');
            Route::get('tes/create', [TesController::class, 'create'])->name('tes.create');
            Route::post('tes', [TesController::class, 'store'])->name('tes.store')->middleware('admin.access:tes,add');
            Route::get('tes/{tes}/edit', [TesController::class, 'edit'])->name('tes.edit');
            Route::put('tes/{tes}', [TesController::class, 'update'])->name('tes.update')->middleware('admin.access:tes,edit');
            Route::delete('tes/{tes}', [TesController::class, 'destroy'])->name('tes.destroy')->middleware('admin.access:tes,delete');
        });

        Route::middleware('admin.access:token')->group(function () {
            Route::get('token', [TokenController::class, 'index'])->name('token.index');
            Route::post('token', [TokenController::class, 'store'])->name('token.store')->middleware('admin.access:token,add');
            Route::delete('token/{token}', [TokenController::class, 'destroy'])->name('token.destroy')->middleware('admin.access:token,delete');
        });

        Route::middleware('admin.access:hasil')->group(function () {
            Route::get('hasil', [HasilController::class, 'index'])->name('hasil.index');
            Route::get('hasil/{tesUser}/cetak', [HasilController::class, 'cetak'])->name('hasil.cetak');
            Route::get('hasil/{tesUser}', [HasilController::class, 'detail'])->name('hasil.detail');
            Route::put('hasil/{tesUser}/hentikan', [HasilController::class, 'hentikan'])->name('hasil.hentikan')->middleware('admin.access:hasil,edit');
            Route::put('hasil/soal/{tesSoal}/evaluasi', [HasilController::class, 'evaluasi'])->name('hasil.evaluasi')->middleware('admin.access:hasil,edit');
        });

        Route::middleware('admin.access:guru')->group(function () {
            Route::get('guru', [GuruController::class, 'index'])->name('guru.index');
            Route::get('guru/absensi', [AttendanceMonitoringController::class, 'index'])->name('guru.absensi');
            Route::get('guru/absensi/sesi/{session}', [AttendanceMonitoringController::class, 'show'])->name('guru.absensi.show');
            Route::get('guru/absensi/ujian/{tes}', [AttendanceMonitoringController::class, 'showTes'])->name('guru.absensi.tes');
            Route::post('guru/absensi/override', [AttendanceMonitoringController::class, 'override'])->name('guru.absensi.override');
            Route::get('guru/raport', [GuruController::class, 'raport'])->name('guru.raport');
            Route::post('guru/raport', [GuruController::class, 'simpanRaport'])->name('guru.raport.simpan')->middleware('admin.access:guru,edit');
            Route::put('guru/tes/{tes}/ranking', [GuruController::class, 'toggleRanking'])->name('guru.ranking.toggle')->middleware('admin.access:guru,edit');
            Route::get('guru/tes/{tes}/ranking/cetak', [GuruController::class, 'cetakRanking'])->name('guru.ranking.cetak');
            Route::get('guru/tes/{tes}/ranking', [GuruController::class, 'ranking'])->name('guru.ranking');
        });

        Route::middleware('admin.access:kelulusan')->group(function () {
            Route::get('kelulusan', [AdminKelulusanController::class, 'index'])->name('kelulusan.index');
            Route::get('kelulusan/{peserta}/cetak', [AdminKelulusanController::class, 'cetak'])->name('kelulusan.cetak');
        });

        Route::middleware('admin.access:laporan')->group(function () {
            Route::get('laporan/rekap', [LaporanController::class, 'rekap'])->name('laporan.rekap');
            Route::get('laporan/analisis', [LaporanController::class, 'analisis'])->name('laporan.analisis');
        });

        Route::middleware('admin.access:pengaturan')->group(function () {
            Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
            Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
            Route::get('pengaturan/lokasi', [PengaturanController::class, 'indexLokasi'])->name('pengaturan.lokasi');
            Route::put('pengaturan/lokasi', [PengaturanController::class, 'updateLokasi'])->name('pengaturan.lokasi.update');
        });

        Route::middleware('admin.access:user')->group(function () {
            Route::get('user', [UserController::class, 'index'])->name('user.index');
            Route::get('user/create', [UserController::class, 'create'])->name('user.create');
            Route::post('user', [UserController::class, 'store'])->name('user.store')->middleware('admin.access:user,add');
            Route::get('user/{admin}/edit', [UserController::class, 'edit'])->name('user.edit');
            Route::put('user/{admin}', [UserController::class, 'update'])->name('user.update')->middleware('admin.access:user,edit');
            Route::delete('user/{admin}', [UserController::class, 'destroy'])->name('user.destroy')->middleware('admin.access:user,delete');
        });

        Route::middleware('admin.access:level')->group(function () {
            Route::get('level', [LevelController::class, 'index'])->name('level.index');
            Route::post('level', [LevelController::class, 'store'])->name('level.store')->middleware('admin.access:level,add');
            Route::put('level/{level}', [LevelController::class, 'update'])->name('level.update')->middleware('admin.access:level,edit');
            Route::delete('level/{level}', [LevelController::class, 'destroy'])->name('level.destroy')->middleware('admin.access:level,delete');
        });
    });
});

Route::prefix('peserta')->name('peserta.')->group(function () {
    Route::get('login', [PesertaAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [PesertaAuthController::class, 'login'])->name('login.post')->middleware('throttle:login');
    Route::post('logout', [PesertaAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:peserta')->group(function () {
        Route::get('/', [PesertaDashboardController::class, 'index'])->name('dashboard');
        Route::get('absensi', [PesertaAbsensiController::class, 'index'])->name('absensi.index');
        Route::post('absensi/check-in', [PesertaAbsensiController::class, 'checkIn'])->name('absensi.checkin');
        Route::post('absensi/check-out', [PesertaAbsensiController::class, 'checkOut'])->name('absensi.checkout');
        Route::get('tes/{tes}', [PesertaDashboardController::class, 'show'])->name('tes.show');
        Route::post('tes/{tes}/check-in', [PesertaAbsensiController::class, 'examCheckIn'])->name('tes.checkin');
        Route::post('tes/{tes}/mulai', [PesertaDashboardController::class, 'mulai'])->name('tes.mulai');
        Route::get('tes/{tes}/kerjakan', [UjianController::class, 'kerjakan'])->name('ujian.kerjakan');
        Route::post('tes/{tes}/jawab', [UjianController::class, 'jawab'])->name('ujian.jawab');
        Route::post('tes/{tes}/ragu', [UjianController::class, 'ragu'])->name('ujian.ragu');
        Route::post('tes/{tes}/selesai', [UjianController::class, 'selesai'])->name('ujian.selesai');
        Route::get('tes/{tes}/hasil', [UjianController::class, 'hasil'])->name('ujian.hasil');
        Route::get('tes/{tes}/cetak', [UjianController::class, 'cetak'])->name('ujian.cetak');
        Route::get('prestasi', [PesertaPrestasiController::class, 'index'])->name('prestasi.index');
        Route::get('raport', [PesertaRaportController::class, 'index'])->name('raport.index');
        Route::get('kelulusan', [PesertaKelulusanController::class, 'index'])->name('kelulusan.index');
        Route::get('kelulusan/cetak', [PesertaKelulusanController::class, 'cetak'])->name('kelulusan.cetak');
        Route::get('ranking', [PesertaRankingController::class, 'index'])->name('ranking.index');
        Route::get('ranking/{tes}/cetak', [PesertaRankingController::class, 'cetak'])->name('ranking.cetak');
        Route::get('ranking/{tes}', [PesertaRankingController::class, 'show'])->name('ranking.show');
    });
});

Route::prefix('orangtua')->name('orangtua.')->group(function () {
    Route::get('login', [OrangTuaAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [OrangTuaAuthController::class, 'login'])->name('login.post')->middleware('throttle:login');
    Route::post('logout', [OrangTuaAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:orangtua')->group(function () {
        Route::get('/', [OrangTuaDashboardController::class, 'index'])->name('dashboard');
        Route::get('absensi', [OrangTuaAbsensiController::class, 'index'])->name('absensi.index');
        Route::get('pembayaran', [OrangTuaPembayaranController::class, 'index'])->name('pembayaran.index');
        Route::post('pembayaran', [OrangTuaPembayaranController::class, 'store'])->name('pembayaran.store');
        Route::get('pembayaran/{pembayaran}/bukti', [OrangTuaPembayaranController::class, 'bukti'])->name('pembayaran.bukti');
        Route::get('spp', [OrangTuaSppController::class, 'index'])->name('spp.index');
        Route::get('spp/{bill}', [OrangTuaSppController::class, 'show'])->name('spp.show');
        Route::post('spp/{bill}/bayar', [OrangTuaSppController::class, 'storeProof'])->name('spp.bayar');
        Route::get('spp/pembayaran/{payment}/kwitansi', [OrangTuaSppController::class, 'receipt'])->name('spp.kwitansi');
        Route::get('spp/pembayaran/{payment}/bukti', [OrangTuaSppController::class, 'proof'])->name('spp.bukti');
    });
});
