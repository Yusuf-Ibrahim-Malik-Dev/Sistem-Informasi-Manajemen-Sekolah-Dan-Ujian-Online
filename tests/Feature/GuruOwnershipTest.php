<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Modul;
use App\Models\Soal;
use App\Models\Topik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_setiap_guru_contoh_punya_grup_dan_soal_sendiri(): void
    {
        $sampel = [
            'gurua' => 'Aljabar - Guru A',
            'gurub' => 'Teks Deskripsi - Guru B',
            'guruc' => 'Sistem Tata Surya - Guru C',
        ];

        foreach ($sampel as $username => $namaTopik) {
            $guru = Admin::where('username', $username)->firstOrFail();
            $topik = Topik::where('topik_guru_id', $guru->id)
                ->where('topik_nama', $namaTopik)
                ->withCount('soals')
                ->firstOrFail();

            $this->assertTrue(\Illuminate\Support\Facades\Hash::check($username, $guru->getAuthPassword()));
            $this->assertSame($guru->id, (int) $topik->topik_guru_id);
            $this->assertSame(4, $topik->soals_count);
            $this->assertDatabaseHas('cbt_soal', [
                'soal_topik_id' => $topik->topik_id,
                'soal_creator_id' => $guru->id,
            ]);
        }
    }

    public function test_guru_lain_tidak_bisa_mengubah_atau_menghapus_grup_dan_soal(): void
    {
        $guruA = Admin::where('username', 'gurua')->firstOrFail();
        $guruB = Admin::where('username', 'gurub')->firstOrFail();
        $modul = Modul::firstOrFail();

        $this->actingAs($guruA, 'web');

        $this->post('/admin/soal/grup', [
            'topik_modul_id' => $modul->modul_id,
            'topik_nama' => 'Grup Milik Guru A',
            'topik_guru_id' => $guruA->id,
            'topik_tipe_ujian' => Topik::TIPE_UTS,
            'topik_detail' => 'Grup ini hanya boleh dikelola guru A.',
            'topik_aktif' => 1,
        ])->assertRedirect();

        $topik = Topik::where('topik_nama', 'Grup Milik Guru A')->firstOrFail();

        $this->post('/admin/soal', [
            'soal_topik_id' => $topik->topik_id,
            'soal_detail' => 'Soal milik guru A',
            'soal_tipe' => Soal::TIPE_ESSAY,
            'soal_difficulty' => 2,
            'soal_aktif' => 1,
        ])->assertRedirect();

        $soal = Soal::where('soal_detail', 'Soal milik guru A')->firstOrFail();

        $this->get('/admin/soal')
            ->assertOk()
            ->assertSee('data-bs-target="#editGrup' . $topik->topik_id . '"', false);
        $this->get('/admin/soal/grup/' . $topik->topik_id)
            ->assertOk()
            ->assertSee('Tambah Soal')
            ->assertSee(route('admin.soal.edit', $soal), false);

        $this->actingAs($guruB, 'web');

        $this->get('/admin/soal')
            ->assertOk()
            ->assertSee($topik->topik_nama)
            ->assertDontSee('data-bs-target="#editGrup' . $topik->topik_id . '"', false);

        $this->get('/admin/topik')
            ->assertOk()
            ->assertSee($topik->topik_nama)
            ->assertDontSee('data-bs-target="#editTopik' . $topik->topik_id . '"', false);

        $this->get('/admin/soal/grup/' . $topik->topik_id)
            ->assertOk()
            ->assertSee($soal->soal_detail)
            ->assertDontSee('Tambah Soal')
            ->assertDontSee(route('admin.soal.edit', $soal), false)
            ->assertDontSee('Hapus soal');

        $payloadGrup = [
            'topik_modul_id' => $modul->modul_id,
            'topik_nama' => 'Diubah Guru B',
            'topik_guru_id' => $guruB->id,
            'topik_tipe_ujian' => Topik::TIPE_UAS,
            'topik_detail' => 'Tidak boleh.',
            'topik_aktif' => 1,
        ];

        $this->put('/admin/soal/grup/' . $topik->topik_id, $payloadGrup)
            ->assertRedirect(route('admin.soal.index'))
            ->assertSessionHas('popup_error');
        $this->delete('/admin/soal/grup/' . $topik->topik_id)
            ->assertRedirect(route('admin.soal.index'))
            ->assertSessionHas('popup_error');
        $this->put('/admin/topik/' . $topik->topik_id, $payloadGrup)
            ->assertRedirect(route('admin.topik.index'))
            ->assertSessionHas('popup_error');
        $this->delete('/admin/topik/' . $topik->topik_id)
            ->assertRedirect(route('admin.topik.index'))
            ->assertSessionHas('popup_error');

        $payloadSoal = [
            'soal_topik_id' => $topik->topik_id,
            'soal_detail' => 'Diubah Guru B',
            'soal_tipe' => Soal::TIPE_ESSAY,
            'soal_difficulty' => 2,
            'soal_aktif' => 1,
        ];

        $this->get('/admin/soal/' . $soal->soal_id . '/edit')
            ->assertRedirect(route('admin.soal.index'))
            ->assertSessionHas('popup_error');
        $this->put('/admin/soal/' . $soal->soal_id, $payloadSoal)
            ->assertRedirect(route('admin.soal.index'))
            ->assertSessionHas('popup_error');
        $this->delete('/admin/soal/' . $soal->soal_id)
            ->assertRedirect(route('admin.soal.index'))
            ->assertSessionHas('popup_error');
        $this->post('/admin/soal', $payloadSoal)
            ->assertRedirect(route('admin.soal.index'))
            ->assertSessionHas('popup_error');

        $this->get('/admin/soal')
            ->assertOk()
            ->assertSee('Tidak Bisa Dikelola')
            ->assertSee('Grup soal ini dikelola oleh guru lain.');

        $this->assertDatabaseHas('cbt_topik', [
            'topik_id' => $topik->topik_id,
            'topik_nama' => 'Grup Milik Guru A',
            'topik_guru_id' => $guruA->id,
        ]);
        $this->assertDatabaseHas('cbt_soal', [
            'soal_id' => $soal->soal_id,
            'soal_detail' => 'Soal milik guru A',
        ]);
    }
}
