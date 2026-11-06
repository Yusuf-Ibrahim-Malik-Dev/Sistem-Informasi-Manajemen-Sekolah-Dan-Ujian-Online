<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_menu_user_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('user')->cascadeOnDelete();
            $table->string('kode_menu', 50);
            $table->boolean('is_allowed')->default(true);
            $table->boolean('can_add')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'kode_menu']);
            $table->index(['kode_menu', 'is_allowed']);
        });

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('user')->cascadeOnDelete();
            $table->string('theme_mode', 10)->default('light');
            $table->string('primary_color', 7)->default('#6366f1');
            $table->string('accent_color', 7)->default('#06b6d4');
            $table->boolean('sidebar_collapsed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('user_menu_user_access');
    }
};
