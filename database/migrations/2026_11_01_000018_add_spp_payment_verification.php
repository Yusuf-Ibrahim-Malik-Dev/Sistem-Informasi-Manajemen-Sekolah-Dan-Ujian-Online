<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fin_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('bill_id')->nullable()->after('student_id');
            $table->unsignedBigInteger('verified_by')->nullable()->after('recorded_by');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('rejection_reason')->nullable()->after('notes');
            $table->index('bill_id');
            $table->index(['status', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::table('fin_payments', function (Blueprint $table) {
            $table->dropIndex(['status', 'paid_at']);
            $table->dropIndex(['bill_id']);
            $table->dropColumn(['bill_id', 'verified_by', 'verified_at', 'rejection_reason']);
        });
    }
};
