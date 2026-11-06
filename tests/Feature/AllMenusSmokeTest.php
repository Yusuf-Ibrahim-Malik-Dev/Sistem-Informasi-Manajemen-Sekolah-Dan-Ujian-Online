<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\OrangTua;
use App\Models\Peserta;
use App\Models\School;
use App\Models\SchoolPayment;
use App\Models\Soal;
use App\Models\StudentBill;
use App\Models\Tes;
use App\Models\TesUser;
use App\Models\Topik;
use App\Support\SppPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AllMenusSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_semua_menu_admin_dapat_dibuka(): void
    {
        Storage::fake('public');

        $admin = Admin::where('username', 'admin')->firstOrFail();
        $soal = Soal::firstOrFail();
        $topik = Topik::firstOrFail();
        $tes = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->firstOrFail();
        $peserta = Peserta::firstOrFail();
        $hasil = TesUser::where('tesuser_status', TesUser::STATUS_SELESAI)->firstOrFail();
        $orangTua = OrangTua::firstOrFail();
        $lulus = Peserta::where('user_name', 'sma3a')->firstOrFail();

        $this->actingAs($admin, 'web');

        [$bill, $payment] = $this->buatSpp($admin);

        $routes = [
            '/admin',
            '/admin/yayasan',
            '/admin/sekolah',
            '/admin/modul',
            '/admin/topik',
            '/admin/soal',
            '/admin/soal/create',
            '/admin/soal/grup/'.$topik->topik_id,
            '/admin/soal/'.$soal->soal_id.'/edit',
            '/admin/grup',
            '/admin/peserta',
            '/admin/peserta/create',
            '/admin/peserta/'.$peserta->user_id.'/edit',
            '/admin/orangtua',
            '/admin/orangtua/create',
            '/admin/orangtua/'.$orangTua->ortu_id.'/edit',
            '/admin/absensi',
            '/admin/absensi/lokasi',
            '/admin/absensi/monitoring',
            '/admin/absensi/monitoring/ujian/'.$tes->tes_id,
            '/admin/guru/absensi',
            '/admin/tes',
            '/admin/tes/create',
            '/admin/tes/'.$tes->tes_id.'/edit',
            '/admin/token',
            '/admin/hasil',
            '/admin/hasil/'.$hasil->tesuser_id,
            '/admin/hasil/'.$hasil->tesuser_id.'/cetak',
            '/admin/pembayaran',
            '/admin/spp',
            '/admin/spp/pengaturan',
            '/admin/spp/tagihan/create',
            '/admin/spp/tagihan/'.$bill->id,
            '/admin/spp/pembayaran',
            '/admin/spp/pembayaran/'.$payment->id,
            '/admin/spp/pembayaran/'.$payment->id.'/kwitansi',
            '/admin/guru',
            '/admin/guru/raport',
            '/admin/guru/tes/'.$tes->tes_id.'/ranking',
            '/admin/guru/tes/'.$tes->tes_id.'/ranking/cetak',
            '/admin/kelulusan',
            '/admin/kelulusan/'.$lulus->user_id.'/cetak',
            '/admin/laporan/rekap',
            '/admin/laporan/analisis?tes='.$tes->tes_id,
            '/admin/pengaturan',
            '/admin/user',
            '/admin/user/create',
            '/admin/user/'.$admin->id.'/edit',
            '/admin/level',
            '/admin/preferensi-tampilan',
        ];

        $this->assertRoutesOk($routes);
    }

    public function test_semua_menu_peserta_dapat_dibuka(): void
    {
        $peserta = Peserta::where('user_name', 'siti')->firstOrFail();
        $lulus = Peserta::where('user_name', 'sma3a')->firstOrFail();
        $tes = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->firstOrFail();

        $this->actingAs($peserta, 'peserta');

        $this->assertRoutesOk([
            '/peserta',
            '/peserta/absensi',
            '/peserta/prestasi',
            '/peserta/raport',
            '/peserta/kelulusan',
            '/peserta/ranking',
            '/peserta/ranking/'.$tes->tes_id,
            '/peserta/tes/'.$tes->tes_id,
            '/peserta/tes/'.$tes->tes_id.'/hasil',
        ]);

        $this->actingAs($lulus, 'peserta');

        $this->assertRoutesOk([
            '/peserta',
            '/peserta/prestasi',
            '/peserta/raport',
            '/peserta/kelulusan',
            '/peserta/kelulusan/cetak',
        ]);
    }

    public function test_semua_menu_orang_tua_dapat_dibuka(): void
    {
        Storage::fake('public');

        $admin = Admin::where('username', 'admin')->firstOrFail();
        $orangTua = OrangTua::where('ortu_username', 'orangtua2')->firstOrFail();
        $student = Peserta::where('user_name', 'siti')->firstOrFail();

        $this->actingAs($admin, 'web');
        [$bill] = $this->buatSpp($admin, $student);

        $this->actingAs($orangTua, 'orangtua');

        $this->assertRoutesOk([
            '/orangtua',
            '/orangtua/absensi',
            '/orangtua/pembayaran',
            '/orangtua/spp?anak='.$student->user_id,
            '/orangtua/spp/'.$bill->id.'?anak='.$student->user_id,
        ]);

        $this->post('/orangtua/spp/'.$bill->id.'/bayar?anak='.$student->user_id, [
            'amount' => 50000,
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
            'notes' => 'Smoke test',
        ])->assertRedirect();

        $payment = SchoolPayment::where('bill_id', $bill->id)->latest('id')->firstOrFail();

        $this->actingAs($admin, 'web')
            ->post(route('admin.spp.payment.verify', $payment), ['aksi' => 'verifikasi'])
            ->assertRedirect();

        $this->actingAs($orangTua, 'orangtua');

        $this->assertRoutesOk([
            '/orangtua/spp/pembayaran/'.$payment->id.'/kwitansi',
        ]);
    }

    private function assertRoutesOk(array $routes): void
    {
        foreach ($routes as $route) {
            $response = $this->get($route);

            $this->assertSame(
                200,
                $response->status(),
                $route.' => '.$response->status().' '.substr(strip_tags($response->getContent()), 0, 1200)
            );
        }
    }

    private function buatSpp(Admin $admin, ?Peserta $student = null): array
    {
        $student ??= Peserta::whereNotNull('user_school_id')->firstOrFail();
        $school = School::whereKey($student->user_school_id)->firstOrFail();

        $category = FeeCategory::create([
            'foundation_id' => $school->foundation_id,
            'school_id' => $school->id,
            'category_code' => 'SMOKE',
            'category_name' => 'Biaya Smoke Test',
            'is_recurring' => false,
            'is_active' => true,
        ]);

        FeeStructure::create([
            'foundation_id' => $school->foundation_id,
            'school_id' => $school->id,
            'fee_category_id' => $category->id,
            'amount' => 100000,
            'is_active' => true,
        ]);

        $bill = StudentBill::create([
            'foundation_id' => $school->foundation_id,
            'school_id' => $school->id,
            'student_id' => $student->user_id,
            'bill_number' => 'SMOKE-'.now()->format('YmdHis').'-'.$student->user_id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'total_amount' => 100000,
            'paid_amount' => 0,
            'status' => StudentBill::STATUS_DRAFT,
            'notes' => 'Tagihan smoke test',
        ]);

        $bill->items()->create([
            'fee_category_id' => $category->id,
            'description' => 'Biaya Smoke Test',
            'quantity' => 1,
            'unit_amount' => 100000,
            'line_total' => 100000,
        ]);

        $payment = app(SppPaymentService::class)->recordManual($bill, [
            'amount' => 40000,
            'paid_at' => now(),
            'notes' => 'Pembayaran smoke test',
        ], $admin->id);

        return [$bill->fresh(), $payment];
    }
}
