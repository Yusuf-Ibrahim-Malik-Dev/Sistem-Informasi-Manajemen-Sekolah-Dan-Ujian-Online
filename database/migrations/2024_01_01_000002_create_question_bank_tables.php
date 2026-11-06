<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_konfigurasi', function (Blueprint $table) {
            $table->id('konfigurasi_id');
            $table->string('konfigurasi_kode', 50)->unique();
            $table->string('konfigurasi_isi', 500);
            $table->string('konfigurasi_keterangan', 100);
        });

        Schema::create('cbt_modul', function (Blueprint $table) {
            $table->id('modul_id');
            $table->string('modul_nama')->unique();
            $table->boolean('modul_aktif')->default(false);
        });

        Schema::create('cbt_topik', function (Blueprint $table) {
            $table->id('topik_id');
            $table->unsignedBigInteger('topik_modul_id')->default(1);
            $table->string('topik_nama');
            $table->text('topik_detail')->nullable();
            $table->boolean('topik_aktif')->default(false);
            $table->index('topik_modul_id');
        });

        Schema::create('cbt_soal', function (Blueprint $table) {
            $table->id('soal_id');
            $table->unsignedBigInteger('soal_topik_id');
            $table->longText('soal_detail');
            $table->smallInteger('soal_tipe')->unsigned()->default(1);
            $table->text('soal_kunci')->nullable();
            $table->smallInteger('soal_difficulty')->default(1);
            $table->boolean('soal_aktif')->default(false);
            $table->string('soal_audio', 200)->nullable();
            $table->integer('soal_audio_play')->default(0);
            $table->smallInteger('soal_timer')->nullable();
            $table->boolean('soal_inline_answers')->default(false);
            $table->boolean('soal_auto_next')->default(false);
            $table->index('soal_topik_id');
        });

        Schema::create('cbt_jawaban', function (Blueprint $table) {
            $table->id('jawaban_id');
            $table->unsignedBigInteger('jawaban_soal_id');
            $table->text('jawaban_detail');
            $table->boolean('jawaban_benar')->default(false);
            $table->boolean('jawaban_aktif')->default(false);
            $table->index('jawaban_soal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_jawaban');
        Schema::dropIfExists('cbt_soal');
        Schema::dropIfExists('cbt_topik');
        Schema::dropIfExists('cbt_modul');
        Schema::dropIfExists('cbt_konfigurasi');
    }
};
