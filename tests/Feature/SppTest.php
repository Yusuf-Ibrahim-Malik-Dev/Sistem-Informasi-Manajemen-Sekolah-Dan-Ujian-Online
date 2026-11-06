<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\OrangTua;
use App\Models\Peserta;
use App\Models\School;
use App\Models\SchoolPayment;
use App\Models\StudentBill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SppTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_halaman_spp_dapat_diakses(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin, 'web');

        $this->get('/admin/spp')->assertOk()->assertSee('Tagihan SPP');
        $this->get('/admin/spp/pengaturan')->assertOk()->assertSee('Kategori Biaya');
        $this->get('/admin/spp/tagihan/create')->assertOk()->assertSee('Form Tagihan Baru');
        $this->get('/admin/spp/pembayaran')->assertOk()->assertSee('Riwayat Pembayaran');
    }

    public function test_admin_dapat_membuat_kategori_tarif_dan_tagihan(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin, 'web');

        $student = Peserta::whereNotNull('user_school_id')->firstOrFail();
        $school = School::whereKey($student->user_school_id)->firstOrFail();

        $this->post('/admin/spp/kategori', [
            'school_id' => $school->id,
            'category_code' => 'UJISP',
            'category_name' => 'SPP Bulanan',
            'is_recurring' => '1',
            'is_active' => '1',
        ])->assertRedirect();

        $category = FeeCategory::where('school_id', $school->id)->where('category_code', 'UJISP')->firstOrFail();

        $this->post('/admin/spp/tarif', [
            'school_id' => $school->id,
            'fee_category_id' => $category->id,
            'grade_code' => 'XI',
            'amount' => 250000,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('fin_fee_structures', [
            'school_id' => $school->id,
            'fee_category_id' => $category->id,
            'amount' => 250000,
        ]);

        $response = $this->post('/admin/spp/tagihan', [
            'student_id' => $student->user_id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'notes' => 'Tagihan uji',
            'items' => [
                [
                    'fee_category_id' => $category->id,
                    'description' => 'SPP Bulan November',
                    'quantity' => 1,
                    'unit_amount' => 250000,
                ],
                [
                    'description' => 'Kegiatan',
                    'quantity' => 2,
                    'unit_amount' => 50000,
                ],
            ],
        ]);

        $response->assertRedirect();

        $bill = StudentBill::where('student_id', $student->user_id)->latest('id')->firstOrFail();

        $this->assertSame(350000.0, (float) $bill->total_amount);
        $this->assertSame(0.0, (float) $bill->paid_amount);
        $this->assertSame(StudentBill::STATUS_DRAFT, $bill->status);
        $this->assertCount(2, $bill->items);

        $this->get($response->headers->get('Location'))->assertOk()->assertSee('SPP Bulan November');
    }

    public function test_pembayaran_spp_mengubah_status_tagihan(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin, 'web');

        [$student, $school, $category] = $this->buatTagihanDasar();

        $bill = StudentBill::where('student_id', $student->user_id)->latest('id')->firstOrFail();

        $this->post(route('admin.spp.bayar', $bill), [
            'amount' => 200000,
            'paid_at' => now()->toDateString(),
            'notes' => 'Cicilan pertama',
        ])->assertRedirect();

        $bill->refresh();
        $this->assertSame(200000.0, (float) $bill->paid_amount);
        $this->assertSame(StudentBill::STATUS_PARTIAL, $bill->status);
        $this->assertSame(1, SchoolPayment::where('bill_id', $bill->id)->count());

        $this->post(route('admin.spp.bayar', $bill), [
            'amount' => 150000,
            'paid_at' => now()->toDateString(),
        ])->assertRedirect();

        $bill->refresh();
        $this->assertSame(350000.0, (float) $bill->paid_amount);
        $this->assertSame(StudentBill::STATUS_PAID, $bill->status);

        $this->get('/admin/spp/pembayaran')->assertOk()->assertSee('PAY-');
    }

    public function test_operator_tanpa_akses_spp_ditolak(): void
    {
        $operator = Admin::where('username', 'operator')->firstOrFail();
        $this->actingAs($operator, 'web');

        $this->get('/admin/spp')
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('error');
    }

    public function test_orang_tua_mengunggah_bukti_lalu_admin_memverifikasi_dan_mencetak_kwitansi(): void
    {
        Storage::fake('public');

        $admin = Admin::where('username', 'admin')->firstOrFail();
        $orangTua = OrangTua::where('ortu_username', 'orangtua2')->firstOrFail();
        $student = Peserta::where('user_name', 'siti')->firstOrFail();

        $this->actingAs($admin, 'web');
        [$student] = $this->buatTagihanDasar($student);
        $bill = StudentBill::where('student_id', $student->user_id)->latest('id')->firstOrFail();

        $this->actingAs($orangTua, 'orangtua');

        $this->get(route('orangtua.spp.index', ['anak' => $student->user_id]))
            ->assertOk()
            ->assertSee('Tagihan SPP')
            ->assertSee($bill->bill_number);

        $responseShow = $this->get(route('orangtua.spp.show', ['bill' => $bill, 'anak' => $student->user_id]));
        $responseShow->assertOk()->assertSee('Bayar / Cicil Tagihan');

        $this->post(route('orangtua.spp.bayar', $bill), [
            'amount' => 150000,
            'proof' => UploadedFile::fake()->image('bukti-spp.jpg'),
            'notes' => 'Transfer pertama',
        ])->assertRedirect();

        $payment = SchoolPayment::where('bill_id', $bill->id)->latest('id')->firstOrFail();

        $this->assertSame(SchoolPayment::STATUS_PENDING, $payment->status);
        $this->assertSame(150000.0, (float) $payment->amount);
        $this->assertSame($orangTua->ortu_id, $payment->guardian_id);
        Storage::disk('public')->assertExists($payment->proof_path);

        $this->assertSame('draft', $bill->fresh()->status);

        $this->actingAs($admin, 'web');

        $this->post(route('admin.spp.payment.verify', $payment), [
            'aksi' => 'verifikasi',
        ])->assertRedirect();

        $payment->refresh();
        $bill->refresh();

        $this->assertSame(SchoolPayment::STATUS_VERIFIED, $payment->status);
        $this->assertNotNull($payment->verified_by);
        $this->assertSame(150000.0, (float) $bill->paid_amount);
        $this->assertSame(StudentBill::STATUS_PARTIAL, $bill->status);

        $this->get(route('admin.spp.payment.receipt', $payment))
            ->assertOk()
            ->assertSee($payment->payment_number);

        $this->actingAs($orangTua, 'orangtua');

        $this->get(route('orangtua.spp.kwitansi', $payment))
            ->assertOk()
            ->assertSee($payment->payment_number);
    }

    public function test_admin_menolak_bukti_dan_orang_tua_dapat_mengunggah_ulang(): void
    {
        Storage::fake('public');

        $admin = Admin::where('username', 'admin')->firstOrFail();
        $orangTua = OrangTua::where('ortu_username', 'orangtua2')->firstOrFail();
        $student = Peserta::where('user_name', 'siti')->firstOrFail();

        $this->actingAs($admin, 'web');
        $this->buatTagihanDasar($student);
        $bill = StudentBill::where('student_id', $student->user_id)->latest('id')->firstOrFail();

        $this->actingAs($orangTua, 'orangtua');

        $this->post(route('orangtua.spp.bayar', $bill), [
            'amount' => 350000,
            'proof' => UploadedFile::fake()->image('bukti-salah.jpg'),
        ])->assertRedirect();

        $payment = SchoolPayment::where('bill_id', $bill->id)->latest('id')->firstOrFail();

        $this->actingAs($admin, 'web');

        $this->post(route('admin.spp.payment.verify', $payment), [
            'aksi' => 'tolak',
            'alasan' => 'Nominal belum sesuai.',
        ])->assertRedirect();

        $payment->refresh();
        $this->assertSame(SchoolPayment::STATUS_REJECTED, $payment->status);
        $this->assertSame('Nominal belum sesuai.', $payment->rejection_reason);
        $this->assertSame(0.0, (float) $bill->fresh()->paid_amount);

        $this->actingAs($orangTua, 'orangtua');

        $this->post(route('orangtua.spp.bayar', $bill), [
            'amount' => 350000,
            'proof' => UploadedFile::fake()->image('bukti-baru.jpg', 1200, 900),
        ])->assertRedirect();

        $this->assertSame(2, SchoolPayment::where('bill_id', $bill->id)->count());
        $this->assertSame(
            SchoolPayment::STATUS_PENDING,
            SchoolPayment::where('bill_id', $bill->id)->latest('id')->firstOrFail()->status
        );
    }

    public function test_admin_dapat_membatalkan_pembayaran_pending_tanpa_mengubah_tagihan(): void
    {
        Storage::fake('public');

        $admin = Admin::where('username', 'admin')->firstOrFail();
        $orangTua = OrangTua::where('ortu_username', 'orangtua2')->firstOrFail();
        $student = Peserta::where('user_name', 'siti')->firstOrFail();

        $this->actingAs($admin, 'web');
        $this->buatTagihanDasar($student);
        $bill = StudentBill::where('student_id', $student->user_id)->latest('id')->firstOrFail();

        $this->actingAs($orangTua, 'orangtua');

        $this->post(route('orangtua.spp.bayar', $bill), [
            'amount' => 100000,
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertRedirect();

        $payment = SchoolPayment::where('bill_id', $bill->id)->latest('id')->firstOrFail();

        $this->actingAs($admin, 'web')
            ->delete(route('admin.spp.payment.cancel', $payment))
            ->assertRedirect();

        $payment->refresh();

        $this->assertSame(SchoolPayment::STATUS_CANCELLED, $payment->status);
        $this->assertSame(0.0, (float) $bill->fresh()->paid_amount);
    }

    private function buatTagihanDasar(?Peserta $student = null): array
    {
        $student ??= Peserta::whereNotNull('user_school_id')->firstOrFail();
        $school = School::whereKey($student->user_school_id)->firstOrFail();

        $category = FeeCategory::create([
            'foundation_id' => $school->foundation_id,
            'school_id' => $school->id,
            'category_code' => 'UJISP',
            'category_name' => 'SPP Bulanan',
            'is_recurring' => true,
            'is_active' => true,
        ]);

        FeeStructure::create([
            'foundation_id' => $school->foundation_id,
            'school_id' => $school->id,
            'fee_category_id' => $category->id,
            'amount' => 350000,
            'is_active' => true,
        ]);

        $this->post('/admin/spp/tagihan', [
            'student_id' => $student->user_id,
            'issue_date' => now()->toDateString(),
            'items' => [
                [
                    'fee_category_id' => $category->id,
                    'description' => 'SPP Bulan November',
                    'quantity' => 1,
                    'unit_amount' => 350000,
                ],
            ],
        ])->assertRedirect();

        return [$student, $school, $category];
    }
}
