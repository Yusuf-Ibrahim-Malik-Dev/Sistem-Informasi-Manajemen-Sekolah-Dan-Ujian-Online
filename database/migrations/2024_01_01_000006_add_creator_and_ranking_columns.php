<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbt_soal', function (Blueprint $table) {
            $table->unsignedBigInteger('soal_creator_id')->nullable();
            $table->index('soal_creator_id');
        });

        Schema::table('cbt_tes', function (Blueprint $table) {
            $table->boolean('tes_ranking_to_users')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('cbt_tes', function (Blueprint $table) {
            $table->dropColumn('tes_ranking_to_users');
        });

        Schema::table('cbt_soal', function (Blueprint $table) {
            $table->dropIndex(['soal_creator_id']);
            $table->dropColumn('soal_creator_id');
        });
    }
};
