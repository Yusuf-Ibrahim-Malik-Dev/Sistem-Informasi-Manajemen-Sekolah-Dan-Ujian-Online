<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbt_tes', function (Blueprint $table) {
            $table->decimal('tes_biaya', 12, 2)->default(0);
        });

        Schema::create('cbt_orang_tua', function (Blueprint $table) {
            $table->id('ortu_id');
            $table->string('ortu_nama');
            $table->string('ortu_username')->unique();
            $table->string('ortu_password');
            $table->string('ortu_email')->nullable();
            $table->string('ortu_telepon', 30)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('cbt_orang_tua_peserta', function (Blueprint $table) {
            $table->unsignedBigInteger('ortu_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['ortu_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::create('cbt_absensi', function (Blueprint $table) {
            $table->id('absensi_id');
            $table->unsignedBigInteger('absensi_user_id');
            $table->unsignedBigInteger('absensi_tes_id')->nullable();
            $table->date('absensi_tanggal');
            $table->string('absensi_status', 20)->default('hadir');
            $table->string('absensi_sumber', 20)->default('ujian');
            $table->string('absensi_keterangan')->nullable();
            $table->timestamps();
            $table->unique(['absensi_user_id', 'absensi_tes_id'], 'ak_absensi_user_tes');
            $table->index(['absensi_tanggal', 'absensi_status']);
        });

        Schema::create('cbt_pembayaran', function (Blueprint $table) {
            $table->id('pembayaran_id');
            $table->unsignedBigInteger('pembayaran_user_id');
            $table->unsignedBigInteger('pembayaran_tes_id');
            $table->unsignedBigInteger('pembayaran_ortu_id');
            $table->decimal('pembayaran_nominal', 12, 2)->default(0);
            $table->string('pembayaran_status', 20)->default('menunggu');
            $table->string('pembayaran_bukti')->nullable();
            $table->text('pembayaran_catatan')->nullable();
            $table->text('pembayaran_catatan_admin')->nullable();
            $table->unsignedBigInteger('pembayaran_verified_by')->nullable();
            $table->timestamp('pembayaran_verified_at')->nullable();
            $table->timestamps();
            $table->unique(['pembayaran_user_id', 'pembayaran_tes_id'], 'ak_pembayaran_user_tes');
            $table->index(['pembayaran_status', 'pembayaran_verified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_pembayaran');
        Schema::dropIfExists('cbt_absensi');
        Schema::dropIfExists('cbt_orang_tua_peserta');
        Schema::dropIfExists('cbt_orang_tua');

        Schema::table('cbt_tes', function (Blueprint $table) {
            $table->dropColumn('tes_biaya');
        });
    }
};
