<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_seluruh_tabel_legacy_dan_finance_dibuat_oleh_migration(): void
    {
        $tables = [
            'att_attendance_audit_events',
            'att_attendance_corrections',
            'att_attendance_location_checks',
            'att_attendance_locations',
            'att_attendance_policies',
            'att_attendance_records',
            'att_attendance_sessions',
            'cbt_absensi',
            'cbt_jawaban',
            'cbt_konfigurasi',
            'cbt_modul',
            'cbt_orang_tua',
            'cbt_orang_tua_peserta',
            'cbt_pembayaran',
            'cbt_raport',
            'cbt_soal',
            'cbt_tes',
            'cbt_tesgrup',
            'cbt_tes_soal',
            'cbt_tes_soal_jawaban',
            'cbt_tes_token',
            'cbt_tes_topik_set',
            'cbt_tes_user',
            'cbt_topik',
            'cbt_user',
            'cbt_user_grup',
            'core_academic_years',
            'core_foundations',
            'core_schools',
            'core_school_levels',
            'core_semesters',
            'failed_jobs',
            'migrations',
            'personal_access_tokens',
            'user',
            'user_akses',
            'user_level',
            'user_log',
            'user_menu',
            'user_menu_user_access',
            'user_preferences',
            'fin_cash_accounts',
            'fin_fee_categories',
            'fin_fee_structures',
            'fin_payment_allocations',
            'fin_payment_methods',
            'fin_payments',
            'fin_student_bill_items',
            'fin_student_bills',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), 'Tabel migration tidak ditemukan: '.$table);
        }
    }

    public function test_kolom_fitur_baru_tersedia(): void
    {
        $this->assertTrue(Schema::hasColumn('user_akses', 'can_delete'));
        $this->assertTrue(Schema::hasColumn('user_akses', 'can_approve'));
        $this->assertTrue(Schema::hasColumn('user_menu_user_access', 'can_approve'));

        $this->assertTrue(Schema::hasColumn('fin_payments', 'bill_id'));
        $this->assertTrue(Schema::hasColumn('fin_payments', 'verified_by'));
        $this->assertTrue(Schema::hasColumn('fin_payments', 'verified_at'));
        $this->assertTrue(Schema::hasColumn('fin_payments', 'rejection_reason'));

        $this->assertTrue(Schema::hasColumn('fin_payment_methods', 'bank_name'));
        $this->assertTrue(Schema::hasColumn('fin_payment_methods', 'account_number'));
        $this->assertTrue(Schema::hasColumn('fin_payment_methods', 'account_holder'));
        $this->assertTrue(Schema::hasColumn('fin_payment_methods', 'show_to_guardian'));
        $this->assertTrue(Schema::hasColumn('fin_payment_methods', 'sort_order'));

        $this->assertTrue(Schema::hasColumn('cbt_tes', 'tes_absensi_wajib'));
        $this->assertTrue(Schema::hasColumn('cbt_tes', 'tes_absensi_location_id'));
        $this->assertTrue(Schema::hasColumn('cbt_tes', 'tes_checkin_mulai'));
        $this->assertTrue(Schema::hasColumn('cbt_tes', 'tes_checkin_selesai'));
    }
}
