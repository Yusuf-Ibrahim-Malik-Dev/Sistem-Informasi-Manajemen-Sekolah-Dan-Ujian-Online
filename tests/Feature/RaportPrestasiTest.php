<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Modul;
use App\Models\Peserta;
use App\Models\UserGroup;
use App\Support\PrestasiKelas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaportPrestasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_dapat_menyimpan_jenjang_dan_kelas_group(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')
            ->post('/admin/grup', [
                'grup_nama' => 'SD Kelas 4',
                'grup_jenjang' => UserGroup::JENJANG_SD,
                'grup_tingkat' => 4,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('cbt_user_grup', [
            'grup_nama' => 'SD Kelas 4',
            'grup_jenjang' => 'SD',
            'grup_tingkat' => 4,
        ]);
    }

    public function test_admin_melihat_grafik_prestasi_per_jenjang_dan_kelas(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')
            ->get('/admin/peserta')
            ->assertOk()
            ->assertSee('Grafik Prestasi Peserta')
            ->assertSee('SD')
            ->assertSee('SMP/MTs')
            ->assertSee('SMA/MA')
            ->assertSee('SMK')
            ->assertSee('Kelas 6');

        $chart = PrestasiKelas::perKelas();

        $this->assertCount(6, $chart['SD']['labels']);
        $this->assertCount(3, $chart['SMP']['labels']);
        $this->assertCount(3, $chart['SMA']['labels']);
        $this->assertCount(3, $chart['SMK']['labels']);

        foreach (['SD', 'SMP', 'SMA', 'SMK'] as $jenjang) {
            foreach ($chart[$jenjang]['nilai'] as $nilai) {
                $this->assertGreaterThan(0, $nilai, 'Grafik '.$jenjang.' masih kosong.');
            }
        }
    }

    public function test_data_raport_tersedia_dari_sd_sampai_smk(): void
    {
        $jumlahModul = Modul::where('modul_aktif', true)->count();
        $kelas = [
            'SD 1', 'SD 2', 'SD 3', 'SD 4', 'SD 5', 'SD 6',
            'SMP 1', 'SMP 2', 'SMP 3',
            'SMA 1', 'SMA 2', 'SMA 3',
            'SMK 1', 'SMK 2', 'SMK 3',
        ];

        foreach ($kelas as $namaKelas) {
            $grup = UserGroup::where('grup_nama', $namaKelas)->firstOrFail();

            $this->assertSame(2, $grup->pesertas()->count(), $namaKelas.' harus punya 2 peserta sampel.');

            foreach ($grup->pesertas as $peserta) {
                $this->assertSame(
                    $jumlahModul,
                    $peserta->rapors()->count(),
                    $peserta->nama.' belum memiliki nilai untuk semua mata pelajaran.'
                );
            }
        }
    }

    public function test_peserta_kelas_akhir_mendapat_surat_kelulusan(): void
    {
        $peserta = Peserta::where('user_name', 'sma3a')->firstOrFail();

        $this->actingAs($peserta, 'peserta')
            ->get('/peserta')
            ->assertOk()
            ->assertSee('Kelulusan');

        $this->get('/peserta/kelulusan')
            ->assertOk()
            ->assertSee('Status Kelulusan')
            ->assertSee('LULUS')
            ->assertSee('Wawan Setiawan');

        $this->get('/peserta/kelulusan/cetak')
            ->assertOk()
            ->assertSee('Surat Keterangan Lulus')
            ->assertSee('LULUS')
            ->assertSee('Wawan Setiawan')
            ->assertSee('Drs. Ahmad Subarjo, M.Pd.');
    }

    public function test_peserta_bukan_kelas_akhir_belum_mendapat_surat_kelulusan(): void
    {
        $peserta = Peserta::where('user_name', 'lutfi')->firstOrFail();

        $this->actingAs($peserta, 'peserta')
            ->get('/peserta')
            ->assertOk()
            ->assertDontSee('Kelulusan');

        $this->get('/peserta/kelulusan')
            ->assertOk()
            ->assertSee('Surat kelulusan tersedia untuk peserta kelas akhir');

        $this->get('/peserta/kelulusan/cetak')
            ->assertRedirect('/peserta/kelulusan')
            ->assertSessionHas('popup_error');
    }

    public function test_peserta_melihat_menu_prestasi_dan_raport(): void
    {
        $peserta = Peserta::where('user_name', 'siti')->firstOrFail();
        $matematika = Modul::where('modul_nama', 'Matematika')->firstOrFail();
        $guruA = Admin::where('username', 'gurua')->firstOrFail();

        $this->actingAs($peserta, 'peserta')
            ->get('/peserta')
            ->assertOk()
            ->assertSee('Prestasi')
            ->assertSee('Raport');

        $this->get('/peserta/prestasi')
            ->assertOk()
            ->assertSee('Prestasi Saya')
            ->assertSee('Prestasi per Jenjang dan Kelas')
            ->assertSee('Ulangan Bahasa Indonesia');

        $this->get('/peserta/raport')
            ->assertOk()
            ->assertSee('Raport Peserta')
            ->assertSee('Matematika')
            ->assertSee('Guru A (Matematika)')
            ->assertSee('92.0');

        $this->assertDatabaseHas('cbt_raport', [
            'raport_user_id' => $peserta->user_id,
            'raport_modul_id' => $matematika->modul_id,
            'raport_guru_id' => $guruA->id,
            'raport_nilai' => 92,
        ]);
    }

    public function test_akun_riwayat_menampilkan_raport_dari_sd_sampai_sma(): void
    {
        $peserta = Peserta::where('user_name', 'raka')->firstOrFail();

        $this->actingAs($peserta, 'peserta')
            ->get('/peserta/raport')
            ->assertOk()
            ->assertSee('Raka Pratama')
            ->assertSee('Riwayat Kelas')
            ->assertSee('SD Kelas 1')
            ->assertSee('SMA/MA Kelas 3');

        $this->get('/peserta/raport?kelas=SD-1')
            ->assertOk()
            ->assertSee('Nilai Mata Pelajaran')
            ->assertSee('Matematika')
            ->assertSee('SD Kelas 1');

        $this->get('/peserta/kelulusan')
            ->assertOk()
            ->assertSee('LULUS')
            ->assertSee('Raka Pratama')
            ->assertSee('SD Kelas 6')
            ->assertSee('SMP/MTs Kelas 3')
            ->assertSee('SMA/MA Kelas 3');

        $this->get('/peserta/kelulusan/cetak?kelas=SD-6')
            ->assertOk()
            ->assertSee('Surat Keterangan Lulus')
            ->assertSee('SD Kelas 6');

        $this->get('/peserta/kelulusan/cetak?kelas=SMP-3')
            ->assertOk()
            ->assertSee('Surat Keterangan Lulus')
            ->assertSee('SMP/MTs Kelas 3');

        $this->assertSame(36, $peserta->rapors()->count());
    }

    public function test_peserta_smk_melihat_raport_dan_surat_kelulusan(): void
    {
        $peserta = Peserta::where('user_name', 'smk3a')->firstOrFail();

        $this->actingAs($peserta, 'peserta')
            ->get('/peserta/raport')
            ->assertOk()
            ->assertSee('Raport Peserta')
            ->assertSee('SMK Kelas 3')
            ->assertSee('Matematika');

        $this->get('/peserta/kelulusan')
            ->assertOk()
            ->assertSee('Status Kelulusan')
            ->assertSee('LULUS')
            ->assertSee('Zaki Firmansyah');
    }

    public function test_grafik_prestasi_peserta_mengikuti_jenjang_dan_kelas_terdaftar(): void
    {
        $smk = Peserta::where('user_name', 'smk3a')->firstOrFail();

        $response = $this->actingAs($smk, 'peserta')
            ->get('/peserta/prestasi')
            ->assertOk();

        $chart = $response->viewData('chartPrestasi');
        $this->assertSame(['SMK'], array_keys($chart));
        $this->assertSame(['Kelas 3'], $chart['SMK']['labels']);

        $riwayat = Peserta::where('user_name', 'raka')->firstOrFail();

        $response = $this->actingAs($riwayat, 'peserta')
            ->get('/peserta/prestasi')
            ->assertOk();

        $chart = $response->viewData('chartPrestasi');
        $this->assertSame(['SMA'], array_keys($chart));
        $this->assertSame(['Kelas 1', 'Kelas 2', 'Kelas 3'], $chart['SMA']['labels']);
    }

    public function test_guru_dapat_menilai_raport_peserta_pada_mapel_yang_diampu(): void
    {
        $guruA = Admin::where('username', 'gurua')->firstOrFail();
        $peserta = Peserta::where('user_name', 'siti')->firstOrFail();
        $grup = UserGroup::where('grup_nama', 'XI MM')->firstOrFail();
        $matematika = Modul::where('modul_nama', 'Matematika')->firstOrFail();

        $this->actingAs($guruA, 'web')
            ->get('/admin/guru/raport?modul='.$matematika->modul_id.'&grup='.$grup->grup_id)
            ->assertOk()
            ->assertSee('Penilaian Raport')
            ->assertSee('Matematika')
            ->assertSee('Siti Aminah');

        $this->post('/admin/guru/raport', [
            'raport_modul_id' => $matematika->modul_id,
            'raport_grup_id' => $grup->grup_id,
            'nilai' => [$peserta->user_id => 95],
            'catatan' => [$peserta->user_id => 'Peningkatan sangat baik.'],
        ])->assertRedirect(route('admin.guru.raport', [
            'modul' => $matematika->modul_id,
            'grup' => $grup->grup_id,
        ]));

        $this->assertDatabaseHas('cbt_raport', [
            'raport_user_id' => $peserta->user_id,
            'raport_modul_id' => $matematika->modul_id,
            'raport_guru_id' => $guruA->id,
            'raport_nilai' => 95,
            'raport_catatan' => 'Peningkatan sangat baik.',
        ]);
    }

    public function test_admin_melihat_menu_dan_mencetak_surat_kelulusan(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $peserta = Peserta::where('user_name', 'sma3a')->firstOrFail();

        $this->actingAs($admin, 'web')
            ->get('/admin')
            ->assertOk()
            ->assertSee('Kelulusan');

        $this->get('/admin/kelulusan?q=Wawan')
            ->assertOk()
            ->assertSee('Status Kelulusan Peserta')
            ->assertSee('Wawan Setiawan')
            ->assertSee('LULUS');

        $this->get('/admin/kelulusan/'.$peserta->user_id.'/cetak?kelas=SMA-3')
            ->assertOk()
            ->assertSee('Surat Keterangan Lulus')
            ->assertSee('Wawan Setiawan')
            ->assertSee('SMA/MA Kelas 3');
    }

    public function test_admin_tidak_dapat_mencetak_surat_kelulusan_peserta_bukan_kelas_akhir(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $peserta = Peserta::where('user_name', 'lutfi')->firstOrFail();

        $this->actingAs($admin, 'web')
            ->get('/admin/kelulusan/'.$peserta->user_id.'/cetak')
            ->assertRedirect('/admin/kelulusan')
            ->assertSessionHas('popup_error');
    }

    public function test_operator_soal_tidak_dapat_mengakses_menu_kelulusan(): void
    {
        $operator = Admin::where('username', 'operatorsoal')->firstOrFail();

        $this->actingAs($operator, 'web')
            ->get('/admin/kelulusan')
            ->assertRedirect('/admin');
    }

    public function test_guru_tidak_dapat_menilai_mapel_yang_tidak_diampu(): void
    {
        $guruA = Admin::where('username', 'gurua')->firstOrFail();
        $guruB = Admin::where('username', 'gurub')->firstOrFail();
        $peserta = Peserta::where('user_name', 'siti')->firstOrFail();
        $grup = UserGroup::where('grup_nama', 'XI MM')->firstOrFail();
        $bahasa = Modul::where('modul_nama', 'Bahasa Indonesia')->firstOrFail();

        $this->actingAs($guruA, 'web')
            ->from('/admin/guru/raport')
            ->post('/admin/guru/raport', [
                'raport_modul_id' => $bahasa->modul_id,
                'raport_grup_id' => $grup->grup_id,
                'nilai' => [$peserta->user_id => 99],
            ])
            ->assertRedirect('/admin/guru/raport')
            ->assertSessionHas('popup_error');

        $this->assertDatabaseHas('cbt_raport', [
            'raport_user_id' => $peserta->user_id,
            'raport_modul_id' => $bahasa->modul_id,
            'raport_guru_id' => $guruB->id,
            'raport_nilai' => 90,
        ]);
    }
}
