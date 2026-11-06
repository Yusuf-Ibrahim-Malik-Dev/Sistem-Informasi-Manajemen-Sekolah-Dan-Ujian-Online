<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Peserta;
use App\Models\Tes;
use App\Models\TesSoal;
use App\Models\TesUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_halaman_login_dapat_diakses(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Masuk sebagai Operator');
        $this->get('/peserta/login')->assertOk()->assertSee('Masuk ke Ujian');
    }

    public function test_login_dengan_opsi_ingat_saya(): void
    {
        $this->post('/admin/login', [
            'username' => 'admin',
            'password' => 'admin',
            'remember' => '1',
        ])->assertRedirect('/admin');

        $this->assertAuthenticated('web');
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest('web');

        $this->post('/peserta/login', [
            'username' => 'lutfi',
            'password' => 'lutfi',
            'remember' => '1',
        ])->assertRedirect('/peserta');

        $this->assertAuthenticated('peserta');
    }

    public function test_seluruh_halaman_admin_dapat_diakses(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin, 'web');

        $soal = \App\Models\Soal::firstOrFail();
        $topik = \App\Models\Topik::firstOrFail();
        $tes = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->firstOrFail();
        $peserta = Peserta::firstOrFail();
        $hasil = TesUser::where('tesuser_status', TesUser::STATUS_SELESAI)->firstOrFail();

        $routes = [
            '/admin',
            '/admin/modul',
            '/admin/topik',
            '/admin/soal',
            '/admin/soal/grup/' . $topik->topik_id,
            '/admin/soal/create',
            '/admin/soal/' . $soal->soal_id . '/edit',
            '/admin/grup',
            '/admin/peserta',
            '/admin/peserta/create',
            '/admin/peserta/' . $peserta->user_id . '/edit',
            '/admin/tes',
            '/admin/tes/create',
            '/admin/tes/' . $tes->tes_id . '/edit',
            '/admin/token',
            '/admin/hasil',
            '/admin/hasil/' . $hasil->tesuser_id,
            '/admin/hasil/' . $hasil->tesuser_id . '/cetak',
            '/admin/laporan/rekap',
            '/admin/laporan/analisis?tes=' . $tes->tes_id,
            '/admin/pengaturan',
            '/admin/user',
            '/admin/user/create',
            '/admin/user/' . $admin->id . '/edit',
            '/admin/level',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);

            $this->assertSame(
                200,
                $response->status(),
                $route . ' => ' . $response->status() . ' ' . substr(strip_tags($response->getContent()), 0, 800)
            );
        }
    }

    public function test_peserta_dapat_mengerjakan_ujian_sampai_selesai(): void
    {
        $peserta = Peserta::where('user_name', 'lutfi')->firstOrFail();
        $tes = Tes::where('tes_nama', 'Try Out Matematika')->firstOrFail();

        $this->actingAs($peserta, 'peserta');

        $this->get('/peserta')->assertOk();
        $this->get('/peserta/tes/' . $tes->tes_id)->assertOk();

        $this->post('/peserta/tes/' . $tes->tes_id . '/mulai')
            ->assertRedirect(route('peserta.ujian.kerjakan', $tes));

        $attempt = TesUser::where('tesuser_tes_id', $tes->tes_id)
            ->where('tesuser_user_id', $peserta->user_id)
            ->where('tesuser_status', TesUser::STATUS_BERJALAN)
            ->firstOrFail();

        $this->get('/peserta/tes/' . $tes->tes_id . '/kerjakan')->assertOk();

        $tesSoal = TesSoal::where('tessoal_tesuser_id', $attempt->tesuser_id)->firstOrFail();
        $jawaban = $tesSoal->soal->jawabans()->firstOrFail();

        $this->post('/peserta/tes/' . $tes->tes_id . '/jawab', [
            'tessoal_id' => $tesSoal->tessoal_id,
            'jawaban_id' => $jawaban->jawaban_id,
            'aksi' => 'simpan',
        ])->assertRedirect();

        $this->assertNotNull($tesSoal->fresh()->tessoal_change_time);

        $this->post('/peserta/tes/' . $tes->tes_id . '/selesai')
            ->assertRedirect(route('peserta.ujian.hasil', $tes));

        $this->get('/peserta/tes/' . $tes->tes_id . '/hasil')
            ->assertOk()
            ->assertSee('Rincian jawaban belum tersedia')
            ->assertDontSee('Rincian Jawaban');

        $pesertaIds = Peserta::whereIn(
            'user_grup_id',
            $tes->groups()->pluck('cbt_user_grup.grup_id')
        )->pluck('user_id')->all();

        foreach ($pesertaIds as $pesertaId) {
            if ((int) $pesertaId === (int) $peserta->user_id) {
                continue;
            }

            TesUser::create([
                'tesuser_tes_id' => $tes->tes_id,
                'tesuser_user_id' => $pesertaId,
                'tesuser_status' => TesUser::STATUS_SELESAI,
                'tesuser_creation_time' => now(),
            ]);
        }

        $this->get('/peserta/tes/' . $tes->tes_id . '/hasil')->assertOk()->assertSee('Rincian Jawaban');
        $this->get('/peserta/tes/' . $tes->tes_id . '/cetak')
            ->assertOk()
            ->assertSee('Laporan Hasil Ujian')
            ->assertSee('Guru')
            ->assertSee('Administrator')
            ->assertDontSee('Operator');
    }

    public function test_ujian_dengan_token_memerlukan_token_valid(): void
    {
        $peserta = Peserta::where('user_name', 'joko')->firstOrFail();
        $tes = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->firstOrFail();

        $this->actingAs($peserta, 'peserta');

        $this->from('/peserta/tes/' . $tes->tes_id)
            ->post('/peserta/tes/' . $tes->tes_id . '/mulai', ['token' => 'salah'])
            ->assertRedirect('/peserta/tes/' . $tes->tes_id)
            ->assertSessionHasErrors('token');

        $this->post('/peserta/tes/' . $tes->tes_id . '/mulai', ['token' => '123456'])
            ->assertRedirect(route('peserta.ujian.kerjakan', $tes));
    }
}
