<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_akses', function (Blueprint $table) {
            $table->boolean('can_delete')->nullable()->after('edit');
            $table->boolean('can_approve')->nullable()->after('can_delete');
        });

        Schema::table('user_menu_user_access', function (Blueprint $table) {
            $table->boolean('can_approve')->default(false)->after('can_delete');
        });
    }

    public function down(): void
    {
        Schema::table('user_menu_user_access', function (Blueprint $table) {
            $table->dropColumn('can_approve');
        });

        Schema::table('user_akses', function (Blueprint $table) {
            $table->dropColumn(['can_delete', 'can_approve']);
        });
    }
};
