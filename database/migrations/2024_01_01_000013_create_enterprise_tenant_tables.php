<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_foundations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('foundation_code', 30)->unique();
            $table->string('foundation_name', 200);
            $table->string('legal_name')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('core_school_levels', function (Blueprint $table) {
            $table->id();
            $table->string('level_code', 20)->unique();
            $table->string('level_name', 100);
            $table->string('education_category', 50);
            $table->timestamps();
        });

        Schema::create('core_schools', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('foundation_id')->constrained('core_foundations')->restrictOnDelete();
            $table->foreignId('school_level_id')->constrained('core_school_levels')->restrictOnDelete();
            $table->string('school_code', 30);
            $table->string('npsn', 20)->nullable();
            $table->string('school_name', 200);
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['foundation_id', 'school_code']);
        });

        Schema::create('core_academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foundation_id')->constrained('core_foundations')->restrictOnDelete();
            $table->foreignId('school_id')->constrained('core_schools')->cascadeOnDelete();
            $table->string('year_name', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'year_name']);
        });

        Schema::create('core_semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('core_schools')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('core_academic_years')->cascadeOnDelete();
            $table->unsignedTinyInteger('semester_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['academic_year_id', 'semester_number']);
        });

        Schema::table('user', function (Blueprint $table) {
            $table->foreignId('foundation_id')->nullable()->after('level')->constrained('core_foundations')->nullOnDelete();
            $table->foreignId('school_id')->nullable()->after('foundation_id')->constrained('core_schools')->nullOnDelete();
            $table->string('scope_type', 20)->default('system')->after('school_id');
        });

        Schema::table('cbt_user_grup', function (Blueprint $table) {
            $table->unsignedBigInteger('grup_foundation_id')->nullable()->after('grup_nama');
            $table->unsignedBigInteger('grup_school_id')->nullable()->after('grup_foundation_id');
            $table->unsignedBigInteger('grup_academic_year_id')->nullable()->after('grup_school_id');
            $table->index('grup_foundation_id');
            $table->index('grup_school_id');
            $table->index('grup_academic_year_id');
        });

        Schema::table('cbt_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_foundation_id')->nullable()->after('user_grup_id');
            $table->unsignedBigInteger('user_school_id')->nullable()->after('user_foundation_id');
            $table->string('user_nis', 30)->nullable()->after('user_name');
            $table->string('user_nisn', 20)->nullable()->after('user_nis');
            $table->string('user_gender', 1)->nullable()->after('user_firstname');
            $table->index('user_foundation_id');
            $table->index('user_school_id');
        });

        Schema::table('cbt_tes', function (Blueprint $table) {
            $table->unsignedBigInteger('tes_foundation_id')->nullable()->after('tes_nama');
            $table->unsignedBigInteger('tes_school_id')->nullable()->after('tes_foundation_id');
            $table->unsignedBigInteger('tes_academic_year_id')->nullable()->after('tes_school_id');
            $table->index('tes_foundation_id');
            $table->index('tes_school_id');
            $table->index('tes_academic_year_id');
        });

        Schema::table('cbt_absensi', function (Blueprint $table) {
            $table->unsignedBigInteger('absensi_foundation_id')->nullable()->after('absensi_user_id');
            $table->unsignedBigInteger('absensi_school_id')->nullable()->after('absensi_foundation_id');
            $table->index('absensi_foundation_id');
            $table->index('absensi_school_id');
        });

        Schema::table('cbt_pembayaran', function (Blueprint $table) {
            $table->unsignedBigInteger('pembayaran_foundation_id')->nullable()->after('pembayaran_user_id');
            $table->unsignedBigInteger('pembayaran_school_id')->nullable()->after('pembayaran_foundation_id');
            $table->index('pembayaran_foundation_id');
            $table->index('pembayaran_school_id');
        });
    }

    public function down(): void
    {
        Schema::table('cbt_pembayaran', function (Blueprint $table) {
            $table->dropIndex(['pembayaran_foundation_id']);
            $table->dropIndex(['pembayaran_school_id']);
            $table->dropColumn(['pembayaran_foundation_id', 'pembayaran_school_id']);
        });

        Schema::table('cbt_absensi', function (Blueprint $table) {
            $table->dropIndex(['absensi_foundation_id']);
            $table->dropIndex(['absensi_school_id']);
            $table->dropColumn(['absensi_foundation_id', 'absensi_school_id']);
        });

        Schema::table('cbt_tes', function (Blueprint $table) {
            $table->dropIndex(['tes_foundation_id']);
            $table->dropIndex(['tes_school_id']);
            $table->dropIndex(['tes_academic_year_id']);
            $table->dropColumn(['tes_foundation_id', 'tes_school_id', 'tes_academic_year_id']);
        });

        Schema::table('cbt_user', function (Blueprint $table) {
            $table->dropIndex(['user_foundation_id']);
            $table->dropIndex(['user_school_id']);
            $table->dropColumn(['user_foundation_id', 'user_school_id', 'user_nis', 'user_nisn', 'user_gender']);
        });

        Schema::table('cbt_user_grup', function (Blueprint $table) {
            $table->dropIndex(['grup_foundation_id']);
            $table->dropIndex(['grup_school_id']);
            $table->dropIndex(['grup_academic_year_id']);
            $table->dropColumn(['grup_foundation_id', 'grup_school_id', 'grup_academic_year_id']);
        });

        Schema::table('user', function (Blueprint $table) {
            $table->dropConstrainedForeignId('foundation_id');
            $table->dropConstrainedForeignId('school_id');
            $table->dropColumn('scope_type');
        });

        Schema::dropIfExists('core_semesters');
        Schema::dropIfExists('core_academic_years');
        Schema::dropIfExists('core_schools');
        Schema::dropIfExists('core_school_levels');
        Schema::dropIfExists('core_foundations');
    }
};
