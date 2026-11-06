<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $menuCodes = ['keuangan', 'spp', 'spp-pembayaran', 'spp-pengaturan'];

    public function up(): void
    {
        DB::table('user_menu')->updateOrInsert(
            ['kode_menu' => 'keuangan'],
            ['tipe' => 0, 'parent' => null, 'nama_menu' => 'Keuangan', 'url' => '#', 'icon' => 'bi-cash-stack', 'urutan' => 6]
        );

        DB::table('user_menu')->updateOrInsert(
            ['kode_menu' => 'spp'],
            ['tipe' => 1, 'parent' => 'keuangan', 'nama_menu' => 'Tagihan SPP', 'url' => 'admin.spp.index', 'icon' => 'bi-receipt', 'urutan' => 1]
        );

        DB::table('user_menu')->updateOrInsert(
            ['kode_menu' => 'spp-pembayaran'],
            ['tipe' => 1, 'parent' => 'keuangan', 'nama_menu' => 'Pembayaran SPP', 'url' => 'admin.spp.payments', 'icon' => 'bi-cash-coin', 'urutan' => 2]
        );

        DB::table('user_menu')->updateOrInsert(
            ['kode_menu' => 'spp-pengaturan'],
            ['tipe' => 1, 'parent' => 'keuangan', 'nama_menu' => 'Kategori & Tarif', 'url' => 'admin.spp.setup', 'icon' => 'bi-sliders2', 'urutan' => 3]
        );

        DB::table('user_menu')->where('kode_menu', 'guru')->update(['urutan' => 7]);
        DB::table('user_menu')->where('kode_menu', 'kelulusan')->update(['urutan' => 8]);
        DB::table('user_menu')->where('kode_menu', 'laporan')->update(['urutan' => 9]);
        DB::table('user_menu')->where('kode_menu', 'sistem')->update(['urutan' => 10]);

        $akses = [
            'administrator-sekolah' => ['spp', 'spp-pembayaran', 'spp-pengaturan'],
            'kepala-sekolah' => ['spp', 'spp-pembayaran'],
            'bendahara' => ['spp', 'spp-pembayaran', 'spp-pengaturan'],
            'petugas-pembayaran' => ['spp', 'spp-pembayaran'],
        ];

        foreach ($akses as $level => $codes) {
            foreach ($codes as $code) {
                DB::table('user_akses')->updateOrInsert(
                    ['level' => $level, 'kode_menu' => $code],
                    ['add' => 1, 'edit' => 1]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('user_akses')->whereIn('kode_menu', $this->menuCodes)->delete();
        DB::table('user_menu')->whereIn('kode_menu', $this->menuCodes)->delete();

        DB::table('user_menu')->where('kode_menu', 'guru')->update(['urutan' => 6]);
        DB::table('user_menu')->where('kode_menu', 'kelulusan')->update(['urutan' => 7]);
        DB::table('user_menu')->where('kode_menu', 'laporan')->update(['urutan' => 8]);
        DB::table('user_menu')->where('kode_menu', 'sistem')->update(['urutan' => 9]);
    }
};
