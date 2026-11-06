<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbt_topik', function (Blueprint $table) {
            $table->string('topik_tipe_ujian', 10)->default('um')->after('topik_nama');
            $table->unsignedBigInteger('topik_guru_id')->nullable()->after('topik_tipe_ujian');
            $table->index('topik_guru_id');
        });

        $adminId = DB::table('user')->where('level', 'admin')->orderBy('id')->value('id')
            ?? DB::table('user')->orderBy('id')->value('id');

        if ($adminId) {
            DB::table('cbt_topik')->whereNull('topik_guru_id')->update(['topik_guru_id' => $adminId]);
        }
    }

    public function down(): void
    {
        Schema::table('cbt_topik', function (Blueprint $table) {
            $table->dropIndex(['topik_guru_id']);
            $table->dropColumn(['topik_tipe_ujian', 'topik_guru_id']);
        });
    }
};
