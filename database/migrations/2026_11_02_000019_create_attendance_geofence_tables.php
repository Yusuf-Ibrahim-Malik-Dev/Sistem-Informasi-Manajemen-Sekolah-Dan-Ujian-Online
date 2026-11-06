<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbt_tes', function (Blueprint $table) {
            $table->boolean('tes_absensi_wajib')->default(false)->after('tes_biaya');
            $table->unsignedBigInteger('tes_absensi_location_id')->nullable()->after('tes_absensi_wajib');
            $table->dateTime('tes_checkin_mulai')->nullable()->after('tes_absensi_location_id');
            $table->dateTime('tes_checkin_selesai')->nullable()->after('tes_checkin_mulai');
        });

        Schema::create('att_attendance_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('foundation_id')->nullable();
            $table->unsignedBigInteger('school_id');
            $table->string('name', 150);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('radius_meters')->default(50);
            $table->unsignedInteger('max_accuracy_meters')->default(100);
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'is_active']);
            $table->index('foundation_id');
        });

        Schema::create('att_attendance_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('foundation_id')->nullable();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('policy_type', 20)->default('daily');
            $table->boolean('require_location')->default(true);
            $table->boolean('allow_manual')->default(true);
            $table->boolean('require_checkin')->default(true);
            $table->boolean('require_checkout')->default(false);
            $table->time('service_start')->nullable();
            $table->time('service_end')->nullable();
            $table->unsignedInteger('late_tolerance_minutes')->default(0);
            $table->unsignedInteger('max_accuracy_meters')->default(100);
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'policy_type', 'academic_year_id'], 'ak_att_policy_scope');
            $table->index('foundation_id');
        });

        Schema::create('att_attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('foundation_id')->nullable();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('exam_id')->nullable();
            $table->string('session_type', 20)->default('daily');
            $table->date('session_date');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->boolean('requires_checkin')->default(true);
            $table->boolean('allow_manual')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'session_type'], 'ak_att_session_exam');
            $table->index(['school_id', 'session_date', 'session_type'], 'idx_att_session_school_date');
            $table->index('foundation_id');
            $table->index('location_id');
        });

        Schema::create('att_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('foundation_id')->nullable();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->string('status', 20)->default('alpha');
            $table->string('method', 20)->default('manual');
            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->decimal('distance_meters', 10, 2)->nullable();
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->boolean('is_override')->default(false);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'student_id'], 'ak_att_record_session_student');
            $table->index(['student_id', 'status']);
            $table->index(['school_id', 'status']);
        });

        Schema::create('att_attendance_location_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('record_id')->nullable();
            $table->unsignedBigInteger('foundation_id')->nullable();
            $table->unsignedBigInteger('school_id');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->decimal('distance_meters', 10, 2)->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('validation_status', 30);
            $table->text('reason')->nullable();
            $table->string('source', 20)->default('gps');
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('checked_at');
            $table->timestamps();

            $table->index(['session_id', 'student_id'], 'idx_att_check_session_student');
            $table->index(['validation_status', 'checked_at'], 'idx_att_check_status_time');
        });

        Schema::create('att_attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id');
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20);
            $table->string('old_method', 20)->nullable();
            $table->string('new_method', 20)->nullable();
            $table->dateTime('old_check_in_at')->nullable();
            $table->dateTime('new_check_in_at')->nullable();
            $table->text('reason');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->index('record_id');
        });

        Schema::create('att_attendance_audit_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 50);
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('actor_type', 20)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('created_at')->nullable();

            $table->index(['event_type', 'created_at'], 'idx_att_audit_event_time');
            $table->index(['session_id', 'student_id'], 'idx_att_audit_session_student');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('att_attendance_audit_events');
        Schema::dropIfExists('att_attendance_corrections');
        Schema::dropIfExists('att_attendance_location_checks');
        Schema::dropIfExists('att_attendance_records');
        Schema::dropIfExists('att_attendance_sessions');
        Schema::dropIfExists('att_attendance_policies');
        Schema::dropIfExists('att_attendance_locations');

        Schema::table('cbt_tes', function (Blueprint $table) {
            $table->dropColumn([
                'tes_absensi_wajib',
                'tes_absensi_location_id',
                'tes_checkin_mulai',
                'tes_checkin_selesai',
            ]);
        });
    }
};
