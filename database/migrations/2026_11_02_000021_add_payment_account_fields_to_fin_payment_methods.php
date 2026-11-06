<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fin_payment_methods', function (Blueprint $table) {
            $table->string('bank_name', 100)->nullable()->after('method_name');
            $table->string('account_number', 60)->nullable()->after('bank_name');
            $table->string('account_holder', 150)->nullable()->after('account_number');
            $table->boolean('show_to_guardian')->default(true)->after('is_active');
            $table->unsignedInteger('sort_order')->default(0)->after('show_to_guardian');
        });
    }

    public function down(): void
    {
        Schema::table('fin_payment_methods', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'account_number',
                'account_holder',
                'show_to_guardian',
                'sort_order',
            ]);
        });
    }
};
