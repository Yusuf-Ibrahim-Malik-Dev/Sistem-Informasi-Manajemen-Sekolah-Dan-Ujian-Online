<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Separate school-fee (SPP) ledger. CBT exam-fee payments remain in
     * cbt_pembayaran and are intentionally not mixed with tuition invoices.
     */
    public function up(): void
    {
        Schema::create('fin_fee_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foundation_id')->constrained('core_foundations')->restrictOnDelete();
            $table->foreignId('school_id')->constrained('core_schools')->restrictOnDelete();
            $table->string('category_code', 30);
            $table->string('category_name', 120);
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'category_code']);
            $table->index(['foundation_id', 'school_id', 'is_active']);
        });

        Schema::create('fin_fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foundation_id')->constrained('core_foundations')->restrictOnDelete();
            $table->foreignId('school_id')->constrained('core_schools')->restrictOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('core_academic_years')->nullOnDelete();
            $table->foreignId('fee_category_id')->constrained('fin_fee_categories')->restrictOnDelete();
            $table->string('grade_code', 30)->nullable();
            $table->decimal('amount', 18, 2);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['school_id', 'academic_year_id', 'fee_category_id'], 'fin_fee_structure_school_year_category_idx');
        });

        Schema::create('fin_student_bills', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('foundation_id')->constrained('core_foundations')->restrictOnDelete();
            $table->foreignId('school_id')->constrained('core_schools')->restrictOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('core_academic_years')->nullOnDelete();
            $table->foreignId('student_id')->constrained('cbt_user', 'user_id')->restrictOnDelete();
            $table->string('bill_number', 50);
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'bill_number']);
            $table->index(['foundation_id', 'school_id', 'student_id', 'status'], 'fin_student_bills_foundation_student_status_idx');
            $table->index(['school_id', 'due_date', 'status']);
        });

        Schema::create('fin_student_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('fin_student_bills')->restrictOnDelete();
            $foreign = $table->foreignId('fee_category_id')->nullable();
            $foreign->constrained('fin_fee_categories')->nullOnDelete();
            $table->string('description', 180);
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_amount', 18, 2);
            $table->decimal('line_total', 18, 2);
            $table->timestamps();
            $table->index('bill_id');
        });

        Schema::create('fin_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('core_schools')->restrictOnDelete();
            $table->string('method_code', 30);
            $table->string('method_name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'method_code']);
        });

        Schema::create('fin_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('foundation_id')->constrained('core_foundations')->restrictOnDelete();
            $table->foreignId('school_id')->constrained('core_schools')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('cbt_user', 'user_id')->restrictOnDelete();
            $table->foreignId('guardian_id')->nullable()->constrained('cbt_orang_tua', 'ortu_id')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('fin_payment_methods')->nullOnDelete();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->string('payment_number', 50);
            $table->decimal('amount', 18, 2);
            $table->string('status', 20)->default('pending');
            $table->string('provider_reference', 120)->nullable()->unique();
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->string('proof_path')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'payment_number']);
            $table->index(['foundation_id', 'school_id', 'student_id', 'status']);
        });

        Schema::create('fin_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('fin_payments')->restrictOnDelete();
            $table->foreignId('bill_id')->constrained('fin_student_bills')->restrictOnDelete();
            $table->decimal('allocated_amount', 18, 2);
            $table->timestamps();
            $table->unique(['payment_id', 'bill_id']);
            $table->index('bill_id');
        });

        Schema::create('fin_cash_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foundation_id')->constrained('core_foundations')->restrictOnDelete();
            $table->foreignId('school_id')->constrained('core_schools')->restrictOnDelete();
            $table->string('account_code', 30);
            $table->string('account_name', 120);
            $table->string('account_type', 20)->default('cash');
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'account_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fin_cash_accounts');
        Schema::dropIfExists('fin_payment_allocations');
        Schema::dropIfExists('fin_payments');
        Schema::dropIfExists('fin_payment_methods');
        Schema::dropIfExists('fin_student_bill_items');
        Schema::dropIfExists('fin_student_bills');
        Schema::dropIfExists('fin_fee_structures');
        Schema::dropIfExists('fin_fee_categories');
    }
};
