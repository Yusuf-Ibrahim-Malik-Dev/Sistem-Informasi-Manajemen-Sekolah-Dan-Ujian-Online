<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_user_grup', function (Blueprint $table) {
            $table->id('grup_id');
            $table->string('grup_nama')->unique();
        });

        Schema::create('cbt_user', function (Blueprint $table) {
            $table->id('user_id');
            $table->unsignedBigInteger('user_grup_id');
            $table->string('user_name')->unique();
            $table->string('user_password');
            $table->string('user_email')->nullable();
            $table->timestamp('user_regdate')->useCurrent();
            $table->string('user_ip', 39)->nullable();
            $table->string('user_firstname')->nullable();
            $table->date('user_birthdate')->nullable();
            $table->string('user_birthplace')->nullable();
            $table->smallInteger('user_level')->unsigned()->default(1);
            $table->string('user_detail', 25)->nullable();
            $table->index('user_grup_id');
        });

        Schema::create('cbt_tes', function (Blueprint $table) {
            $table->id('tes_id');
            $table->string('tes_nama')->unique();
            $table->text('tes_detail');
            $table->dateTime('tes_begin_time')->nullable();
            $table->dateTime('tes_end_time')->nullable();
            $table->smallInteger('tes_duration_time')->unsigned()->default(0);
            $table->string('tes_ip_range')->default('*.*.*.*');
            $table->boolean('tes_results_to_users')->default(false);
            $table->boolean('tes_detail_to_users')->default(false);
            $table->decimal('tes_score_right', 10, 2)->default(1);
            $table->decimal('tes_score_wrong', 10, 2)->default(0);
            $table->decimal('tes_score_unanswered', 10, 2)->default(0);
            $table->decimal('tes_max_score', 10, 2)->default(0);
            $table->boolean('tes_token')->default(false);
        });

        Schema::create('cbt_tesgrup', function (Blueprint $table) {
            $table->unsignedBigInteger('tstgrp_tes_id');
            $table->unsignedBigInteger('tstgrp_grup_id');
            $table->primary(['tstgrp_tes_id', 'tstgrp_grup_id']);
        });

        Schema::create('cbt_tes_topik_set', function (Blueprint $table) {
            $table->id('tset_id');
            $table->unsignedBigInteger('tset_tes_id');
            $table->unsignedBigInteger('tset_topik_id');
            $table->smallInteger('tset_tipe')->default(1);
            $table->smallInteger('tset_difficulty')->default(1);
            $table->smallInteger('tset_jumlah')->default(1);
            $table->smallInteger('tset_jawaban')->default(0);
            $table->integer('tset_acak_jawaban')->default(1);
            $table->integer('tset_acak_soal')->default(1);
            $table->index('tset_tes_id');
        });

        Schema::create('cbt_tes_user', function (Blueprint $table) {
            $table->id('tesuser_id');
            $table->unsignedBigInteger('tesuser_tes_id');
            $table->unsignedBigInteger('tesuser_user_id');
            $table->smallInteger('tesuser_status')->unsigned()->default(0);
            $table->dateTime('tesuser_creation_time');
            $table->text('tesuser_comment')->nullable();
            $table->string('tesuser_token', 10)->nullable();
            $table->unique(['tesuser_tes_id', 'tesuser_user_id', 'tesuser_status'], 'ak_tesuser');
            $table->index('tesuser_user_id');
        });

        Schema::create('cbt_tes_soal', function (Blueprint $table) {
            $table->id('tessoal_id');
            $table->unsignedBigInteger('tessoal_tesuser_id');
            $table->string('tessoal_user_ip', 39)->nullable();
            $table->unsignedBigInteger('tessoal_soal_id');
            $table->text('tessoal_jawaban_text')->nullable();
            $table->decimal('tessoal_nilai', 10, 2)->nullable();
            $table->dateTime('tessoal_creation_time')->nullable();
            $table->dateTime('tessoal_display_time')->nullable();
            $table->dateTime('tessoal_change_time')->nullable();
            $table->unsignedBigInteger('tessoal_reaction_time')->default(0);
            $table->integer('tessoal_ragu')->default(0);
            $table->smallInteger('tessoal_order')->default(1);
            $table->smallInteger('tessoal_num_answers')->unsigned()->default(0);
            $table->text('tessoal_comment')->nullable();
            $table->integer('tessoal_audio_play')->default(0);
            $table->unique(['tessoal_tesuser_id', 'tessoal_soal_id'], 'ak_testuser_question');
            $table->index('tessoal_tesuser_id');
        });

        Schema::create('cbt_tes_soal_jawaban', function (Blueprint $table) {
            $table->unsignedBigInteger('soaljawaban_tessoal_id');
            $table->unsignedBigInteger('soaljawaban_jawaban_id');
            $table->smallInteger('soaljawaban_selected')->default(-1);
            $table->smallInteger('soaljawaban_order')->default(1);
            $table->unsignedBigInteger('soaljawaban_position')->nullable();
            $table->primary(['soaljawaban_tessoal_id', 'soaljawaban_jawaban_id'], 'ak_logansw');
        });

        Schema::create('cbt_tes_token', function (Blueprint $table) {
            $table->id('token_id');
            $table->string('token_isi', 20);
            $table->unsignedBigInteger('token_user_id');
            $table->timestamp('token_ts')->useCurrent();
            $table->integer('token_aktif')->default(1);
            $table->unsignedBigInteger('token_tes_id')->default(0);
            $table->index('token_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_tes_token');
        Schema::dropIfExists('cbt_tes_soal_jawaban');
        Schema::dropIfExists('cbt_tes_soal');
        Schema::dropIfExists('cbt_tes_user');
        Schema::dropIfExists('cbt_tes_topik_set');
        Schema::dropIfExists('cbt_tesgrup');
        Schema::dropIfExists('cbt_tes');
        Schema::dropIfExists('cbt_user');
        Schema::dropIfExists('cbt_user_grup');
    }
};
