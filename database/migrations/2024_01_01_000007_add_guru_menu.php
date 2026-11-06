<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_menu')->updateOrInsert(
            ['kode_menu' => 'guru'],
            [
                'tipe' => 0,
                'parent' => null,
                'nama_menu' => 'Guru',
                'url' => 'admin.guru.index',
                'icon' => 'bi-person-video3',
                'urutan' => 5,
            ]
        );

        DB::table('user_menu')->where('kode_menu', 'laporan')->update(['urutan' => 6]);
        DB::table('user_menu')->where('kode_menu', 'sistem')->update(['urutan' => 7]);

        DB::table('user_akses')->updateOrInsert(
            ['level' => 'operator-tes', 'kode_menu' => 'guru'],
            ['add' => 1, 'edit' => 1]
        );
    }

    public function down(): void
    {
        DB::table('user_akses')->where('level', 'operator-tes')->where('kode_menu', 'guru')->delete();
        DB::table('user_menu')->where('kode_menu', 'guru')->delete();

        DB::table('user_menu')->where('kode_menu', 'laporan')->update(['urutan' => 5]);
        DB::table('user_menu')->where('kode_menu', 'sistem')->update(['urutan' => 6]);
    }
};
