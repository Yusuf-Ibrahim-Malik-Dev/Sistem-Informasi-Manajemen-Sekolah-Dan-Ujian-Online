<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\OrangTua;
use App\Models\SchoolPaymentMethod;
use App\Models\StudentBill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_admin_dapat_mengatur_rekening_pembayaran_yang_tampil_ke_orang_tua(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $orangTua = OrangTua::where('ortu_username', 'orangtua1')->firstOrFail();
        $anak = $orangTua->pesertas()->whereNotNull('user_school_id')->firstOrFail();
        $schoolId = $anak->user_school_id ?: $anak->grup?->grup_school_id;

        $this->actingAs($admin, 'web');

        $this->post(route('admin.spp.metode.store'), [
            'school_id' => $schoolId,
            'method_code' => 'BSI',
            'method_name' => 'Transfer Bank BSI',
            'bank_name' => 'Bank Syariah Indonesia',
            'account_number' => '0123456789',
            'account_holder' => 'Yayasan Sekolah',
            'show_to_guardian' => '1',
            'sort_order' => 1,
        ])->assertRedirect();

        $method = SchoolPaymentMethod::where('school_id', $schoolId)
            ->where('method_code', 'BSI')
            ->firstOrFail();

        $this->assertSame('Bank Syariah Indonesia', $method->bank_name);
        $this->assertSame('0123456789', $method->account_number);
        $this->assertTrue($method->show_to_guardian);

        $this->put(route('admin.spp.metode.update', $method), [
            'method_code' => 'BSI',
            'method_name' => 'Transfer Bank BSI',
            'bank_name' => 'BSI',
            'account_number' => '9876543210',
            'account_holder' => 'Sekolah',
            'is_active' => '1',
            'show_to_guardian' => '1',
            'sort_order' => 2,
        ])->assertRedirect();

        $method->refresh();
        $this->assertSame('BSI', $method->bank_name);
        $this->assertSame('9876543210', $method->account_number);

        $this->actingAs($orangTua, 'orangtua');

        $this->get(route('orangtua.pembayaran.index', ['anak' => $anak->user_id]))
            ->assertOk()
            ->assertSee('Rekening Tujuan Transfer')
            ->assertSee('BSI')
            ->assertSee('9876543210');

        $this->get(route('orangtua.spp.index', ['anak' => $anak->user_id]))
            ->assertOk()
            ->assertSee('9876543210');

        $bill = StudentBill::create([
            'foundation_id' => $anak->user_foundation_id,
            'school_id' => $schoolId,
            'student_id' => $anak->user_id,
            'bill_number' => 'SPP-TEST-0001',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
            'status' => StudentBill::STATUS_DRAFT,
            'total_amount' => 250000,
            'paid_amount' => 0,
        ]);

        $this->get(route('orangtua.spp.show', ['bill' => $bill, 'anak' => $anak->user_id]))
            ->assertOk()
            ->assertSee('Rekening Tujuan Transfer')
            ->assertSee('9876543210');
    }

    public function test_rekening_tidak_ditampilkan_ke_orang_tua_jika_disembunyikan(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $orangTua = OrangTua::where('ortu_username', 'orangtua1')->firstOrFail();
        $anak = $orangTua->pesertas()->whereNotNull('user_school_id')->firstOrFail();

        SchoolPaymentMethod::create([
            'school_id' => $anak->user_school_id,
            'method_code' => 'BRI',
            'method_name' => 'Transfer Bank Mandiri',
            'bank_name' => 'Mandiri',
            'account_number' => '555000111',
            'account_holder' => 'Sekolah',
            'is_active' => true,
            'show_to_guardian' => false,
        ]);

        $this->actingAs($orangTua, 'orangtua');

        $this->get(route('orangtua.pembayaran.index', ['anak' => $anak->user_id]))
            ->assertOk()
            ->assertDontSee('555000111');
    }
}
