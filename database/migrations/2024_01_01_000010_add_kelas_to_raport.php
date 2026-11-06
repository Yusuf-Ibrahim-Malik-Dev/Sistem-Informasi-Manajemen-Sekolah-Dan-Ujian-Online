<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbt_raport', function (Blueprint $table) {
            $table->string('raport_jenjang', 10)->default('SMA');
            $table->unsignedTinyInteger('raport_tingkat')->default(1);
        });

        $rows = DB::table('cbt_raport as raport')
            ->join('cbt_user as user', 'user.user_id', '=', 'raport.raport_user_id')
            ->join('cbt_user_grup as grup', 'grup.grup_id', '=', 'user.user_grup_id')
            ->select('raport.raport_id', 'grup.grup_jenjang', 'grup.grup_tingkat')
            ->get();

        foreach ($rows as $row) {
            DB::table('cbt_raport')
                ->where('raport_id', $row->raport_id)
                ->update([
                    'raport_jenjang' => $row->grup_jenjang,
                    'raport_tingkat' => $row->grup_tingkat,
                ]);
        }

        Schema::table('cbt_raport', function (Blueprint $table) {
            $table->dropUnique('ak_raport_user_modul');
            $table->unique(
                ['raport_user_id', 'raport_modul_id', 'raport_jenjang', 'raport_tingkat'],
                'ak_raport_user_modul_kelas'
            );
        });
    }

    public function down(): void
    {
        Schema::table('cbt_raport', function (Blueprint $table) {
            $table->dropUnique('ak_raport_user_modul_kelas');
            $table->unique(['raport_user_id', 'raport_modul_id'], 'ak_raport_user_modul');
            $table->dropColumn(['raport_jenjang', 'raport_tingkat']);
        });
    }
};
