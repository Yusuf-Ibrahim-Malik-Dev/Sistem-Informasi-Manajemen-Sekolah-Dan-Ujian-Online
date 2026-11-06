<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbt_user_grup', function (Blueprint $table) {
            $table->string('grup_jenjang', 10)->default('SMA');
            $table->unsignedTinyInteger('grup_tingkat')->default(1);
            $table->index(['grup_jenjang', 'grup_tingkat']);
        });

        DB::table('cbt_user_grup')->update([
            'grup_jenjang' => 'SMA',
            'grup_tingkat' => 2,
        ]);

        Schema::create('cbt_raport', function (Blueprint $table) {
            $table->id('raport_id');
            $table->unsignedBigInteger('raport_user_id');
            $table->unsignedBigInteger('raport_modul_id');
            $table->unsignedBigInteger('raport_guru_id');
            $table->decimal('raport_nilai', 5, 2);
            $table->text('raport_catatan')->nullable();
            $table->timestamps();
            $table->unique(['raport_user_id', 'raport_modul_id'], 'ak_raport_user_modul');
            $table->index('raport_modul_id');
            $table->index('raport_guru_id');
        });

        DB::table('user_akses')->updateOrInsert(
            ['level' => 'operator-soal', 'kode_menu' => 'guru'],
            ['add' => 1, 'edit' => 1]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_raport');

        Schema::table('cbt_user_grup', function (Blueprint $table) {
            $table->dropIndex(['grup_jenjang', 'grup_tingkat']);
            $table->dropColumn(['grup_jenjang', 'grup_tingkat']);
        });
    }
};
