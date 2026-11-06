<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Peserta;
use App\Models\Tes;
use App\Models\TesUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guru_dapat_memonitor_peserta_berjalan_dan_selesai(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $peserta = Peserta::where('user_name', 'fajar')->firstOrFail();
        $tes = Tes::where('tes_nama', 'Try Out Matematika')->firstOrFail();

        TesUser::create([
            'tesuser_tes_id' => $tes->tes_id,
            'tesuser_user_id' => $peserta->user_id,
            'tesuser_status' => TesUser::STATUS_BERJALAN,
            'tesuser_creation_time' => now(),
        ]);

        $this->actingAs($admin, 'web');
        $halamanGuru = $this->get('/admin/guru')
            ->assertOk()
            ->assertSee('Peserta Ujian')
            ->assertSee('Kontrol Ranking')
            ->assertSee('Mengerjakan')
            ->assertSee($peserta->nama)
            ->assertSee('Selesai');

        $this->assertLessThan(
            strpos($halamanGuru->getContent(), 'Peserta Ujian'),
            strpos($halamanGuru->getContent(), 'Kontrol Ranking')
        );

        $bahasa = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->firstOrFail();

        $this->get('/admin/guru/tes/' . $bahasa->tes_id . '/ranking')
            ->assertOk()
            ->assertSee('Ranking Ujian')
            ->assertSee('Administrator');

        $this->get('/admin/guru/tes/' . $bahasa->tes_id . '/ranking/cetak')
            ->assertOk()
            ->assertSee('Laporan Ranking Ujian')
            ->assertSee('Guru')
            ->assertSee('Administrator');
    }

    public function test_switch_ranking_membuka_ranking_di_menu_dan_cetak_peserta(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $peserta = Peserta::where('user_name', 'siti')->firstOrFail();
        $tes = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->firstOrFail();

        $tes->update(['tes_ranking_to_users' => false]);

        $this->actingAs($admin, 'web');
        $this->from('/admin/guru')
            ->put('/admin/guru/tes/' . $tes->tes_id . '/ranking')
            ->assertRedirect('/admin/guru');

        $this->assertTrue((bool) $tes->fresh()->tes_ranking_to_users);

        $this->actingAs($peserta, 'peserta');

        $this->get('/peserta')
            ->assertOk()
            ->assertSee('Ranking Ujian');

        $this->get('/peserta/ranking')
            ->assertOk()
            ->assertSee($tes->tes_nama);

        $this->get('/peserta/ranking/' . $tes->tes_id)
            ->assertOk()
            ->assertSee('Ranking Ujian')
            ->assertSee('Kamu')
            ->assertSee('Cetak Ranking');

        $this->get('/peserta/ranking/' . $tes->tes_id . '/cetak')
            ->assertOk()
            ->assertSee('Laporan Ranking Ujian')
            ->assertSee('Guru')
            ->assertSee('Administrator');

        $this->actingAs($admin, 'web');
        $this->put('/admin/guru/tes/' . $tes->tes_id . '/ranking')->assertRedirect();

        $this->actingAs($peserta, 'peserta');
        $this->get('/peserta/ranking/' . $tes->tes_id)->assertNotFound();
    }
}
