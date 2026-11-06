<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique();
            $table->string('password');
            $table->string('nama', 150);
            $table->string('opsi1', 75)->default('');
            $table->string('opsi2', 75)->default('');
            $table->string('keterangan', 150)->default('');
            $table->string('level', 50);
            $table->timestamp('ts')->useCurrent();
            $table->index('level');
        });

        Schema::create('user_level', function (Blueprint $table) {
            $table->id();
            $table->string('level', 50)->unique();
            $table->string('keterangan', 100)->nullable();
        });

        Schema::create('user_akses', function (Blueprint $table) {
            $table->id();
            $table->string('level', 75);
            $table->string('kode_menu', 50);
            $table->boolean('add')->default(true);
            $table->boolean('edit')->default(true);
            $table->index('kode_menu');
            $table->index('level');
        });

        Schema::create('user_menu', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('tipe')->default(1);
            $table->string('parent', 50)->nullable();
            $table->string('kode_menu', 50)->unique();
            $table->string('nama_menu', 100);
            $table->string('url', 150)->default('#');
            $table->string('icon', 75)->default('bi-circle');
            $table->integer('urutan');
        });

        Schema::create('user_log', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100);
            $table->string('log', 250);
            $table->timestamp('ts')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_log');
        Schema::dropIfExists('user_menu');
        Schema::dropIfExists('user_akses');
        Schema::dropIfExists('user_level');
        Schema::dropIfExists('user');
    }
};
