<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $menuCodes = ['absensi-lokasi', 'absensi-monitoring'];

    public function up(): void
    {
        DB::table('user_menu')->updateOrInsert(
            ['kode_menu' => 'absensi-lokasi'],
            [
                'tipe' => 1,
                'parent' => 'data-peserta',
                'nama_menu' => 'Lokasi & Kebijakan Absensi',
                'url' => 'admin.absensi.lokasi',
                'icon' => 'bi-geo-alt',
                'urutan' => 5,
            ]
        );

        DB::table('user_menu')->updateOrInsert(
            ['kode_menu' => 'absensi-monitoring'],
            [
                'tipe' => 1,
                'parent' => 'data-peserta',
                'nama_menu' => 'Monitoring Kehadiran',
                'url' => 'admin.absensi.monitoring',
                'icon' => 'bi-broadcast',
                'urutan' => 6,
            ]
        );

        $levels = DB::table('user_akses')
            ->where('kode_menu', 'absensi')
            ->pluck('level')
            ->unique();

        foreach ($levels as $level) {
            foreach ($this->menuCodes as $code) {
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
    }
};
