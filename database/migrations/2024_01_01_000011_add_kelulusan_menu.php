<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_menu')->updateOrInsert(
            ['kode_menu' => 'kelulusan'],
            [
                'tipe' => 0,
                'parent' => null,
                'nama_menu' => 'Kelulusan',
                'url' => 'admin.kelulusan.index',
                'icon' => 'bi-award',
                'urutan' => 6,
            ]
        );

        DB::table('user_menu')->where('kode_menu', 'laporan')->update(['urutan' => 7]);
        DB::table('user_menu')->where('kode_menu', 'sistem')->update(['urutan' => 8]);
    }

    public function down(): void
    {
        DB::table('user_menu')->where('kode_menu', 'kelulusan')->delete();

        DB::table('user_menu')->where('kode_menu', 'laporan')->update(['urutan' => 6]);
        DB::table('user_menu')->where('kode_menu', 'sistem')->update(['urutan' => 7]);
    }
};
