<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbt_topik', function (Blueprint $table) {
            $table->timestamp('topik_creation_time')->useCurrent()->after('topik_aktif');
        });
    }

    public function down(): void
    {
        Schema::table('cbt_topik', function (Blueprint $table) {
            $table->dropColumn('topik_creation_time');
        });
    }
};
