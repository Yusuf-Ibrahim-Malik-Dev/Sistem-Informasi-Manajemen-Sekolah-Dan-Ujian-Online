<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Admin;
use App\Models\OrangTua;
use App\Models\Pembayaran;
use App\Models\Peserta;
use App\Models\Tes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrangTuaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_orang_tua_dapat_login_dan_melihat_dashboard_anak(): void
    {
        $this->get('/orangtua/login')->assertOk()->assertSee('Masuk sebagai Orang Tua');

        $this->post('/orangtua/login', [
            'username' => 'orangtua1',
            'password' => 'orangtua1',
        ])->assertRedirect('/orangtua');

        $this->assertAuthenticated('orangtua');

        $this->get('/orangtua')->assertOk()->assertSee('Ringkasan Pembayaran');
    }

    public function test_orang_tua_dapat_membuka_halaman_absensi_dan_pembayaran(): void
    {
        $ortu = OrangTua::where('ortu_username', 'orangtua1')->firstOrFail();
        $anak = $ortu->pesertas()->firstOrFail();

        $this->actingAs($ortu, 'orangtua');

        $this->get('/orangtua?anak='.$anak->user_id)->assertOk()->assertSee($anak->nama);
        $this->get('/orangtua/absensi?anak='.$anak->user_id)->assertOk()->assertSee('Riwayat Absensi');
        $this->get('/orangtua/pembayaran?anak='.$anak->user_id)->assertOk()->assertSee('Pembayaran');
    }

    public function test_orang_tua_dapat_mengunggah_bukti_pembayaran(): void
    {
        Storage::fake('public');

        $ortu = OrangTua::where('ortu_username', 'orangtua1')->firstOrFail();
        $anak = Peserta::where('user_name', 'lutfi')->firstOrFail();
        $tes = Tes::where('tes_nama', 'Try Out Matematika')->firstOrFail();

        $this->actingAs($ortu, 'orangtua');

        $this->post('/orangtua/pembayaran?anak='.$anak->user_id, [
            'tes_id' => $tes->tes_id,
            'bukti' => UploadedFile::fake()->image('bukti.png'),
            'catatan' => 'Transfer atas nama orang tua.',
        ])->assertRedirect(route('orangtua.pembayaran.index', ['anak' => $anak->user_id]));

        $pembayaran = Pembayaran::where('pembayaran_user_id', $anak->user_id)
            ->where('pembayaran_tes_id', $tes->tes_id)
            ->firstOrFail();

        $this->assertSame(Pembayaran::MENUNGGU, $pembayaran->pembayaran_status);
        $this->assertSame((float) $tes->tes_biaya, $pembayaran->pembayaran_nominal);
        $this->assertSame($ortu->ortu_id, $pembayaran->pembayaran_ortu_id);
        Storage::disk('public')->assertExists($pembayaran->pembayaran_bukti);
    }

    public function test_orang_tua_tidak_dapat_melihat_bukti_anak_orang_lain(): void
    {
        $ortuRaka = OrangTua::where('ortu_username', 'orangtua1')->firstOrFail();
        $siti = Peserta::where('user_name', 'siti')->firstOrFail();
        $pembayaran = Pembayaran::where('pembayaran_user_id', $siti->user_id)->firstOrFail();

        $this->actingAs($ortuRaka, 'orangtua');

        $this->get('/orangtua/pembayaran/'.$pembayaran->pembayaran_id.'/bukti')->assertForbidden();
    }

    public function test_admin_dapat_memverifikasi_pembayaran(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $pembayaran = Pembayaran::where('pembayaran_status', Pembayaran::MENUNGGU)->firstOrFail();

        $this->actingAs($admin, 'web');

        $this->put('/admin/pembayaran/'.$pembayaran->pembayaran_id.'/verifikasi', [
            'aksi' => 'verifikasi',
            'catatan_admin' => 'Sudah sesuai.',
        ])->assertRedirect();

        $pembayaran->refresh();

        $this->assertSame(Pembayaran::DIVERIFIKASI, $pembayaran->pembayaran_status);
        $this->assertSame($admin->id, (int) $pembayaran->pembayaran_verified_by);
        $this->assertNotNull($pembayaran->pembayaran_verified_at);
    }

    public function test_admin_dapat_membuat_akun_orang_tua_dan_menghubungkan_anak(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $raka = Peserta::where('user_name', 'raka')->firstOrFail();
        $siti = Peserta::where('user_name', 'siti')->firstOrFail();

        $this->actingAs($admin, 'web');

        $this->post('/admin/orangtua', [
            'ortu_nama' => 'Bapak Contoh',
            'ortu_username' => 'orangtua3',
            'password' => 'rahasia123',
            'ortu_email' => 'orangtua3@example.com',
            'ortu_telepon' => '081200000000',
            'peserta' => [$raka->user_id, $siti->user_id],
        ])->assertRedirect(route('admin.orangtua.index'));

        $ortu = OrangTua::where('ortu_username', 'orangtua3')->firstOrFail();

        $this->assertSame(2, $ortu->pesertas()->count());
        $this->assertDatabaseHas('cbt_orang_tua_peserta', [
            'ortu_id' => $ortu->ortu_id,
            'user_id' => $raka->user_id,
        ]);
    }

    public function test_admin_dapat_membuka_halaman_orang_tua_absensi_dan_pembayaran(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $ortu = OrangTua::where('ortu_username', 'orangtua1')->firstOrFail();

        $this->actingAs($admin, 'web');

        $this->get('/admin/orangtua')->assertOk()->assertSee('Daftar Akun Orang Tua');
        $this->get('/admin/orangtua/create')->assertOk()->assertSee('Tambah Akun Orang Tua');
        $this->get('/admin/orangtua/'.$ortu->ortu_id.'/edit')->assertOk()->assertSee($ortu->ortu_nama);
        $this->get('/admin/absensi')->assertOk()->assertSee('Catatan Absensi');
        $this->get('/admin/pembayaran')->assertOk()->assertSee('Daftar Pembayaran');
    }

    public function test_guru_dapat_mengoreksi_absensi_tetapi_tidak_dapat_memverifikasi_pembayaran(): void
    {
        $guru = Admin::where('username', 'operatorsoal')->firstOrFail();

        $this->actingAs($guru, 'web');

        $this->get('/admin/absensi')->assertOk();
        $this->get('/admin/pembayaran')->assertRedirect('/admin');
    }

    public function test_absensi_otomatis_tercatat_saat_peserta_memulai_ujian(): void
    {
        $peserta = Peserta::where('user_name', 'dewi')->firstOrFail();
        $tes = Tes::where('tes_nama', 'Try Out Matematika')->firstOrFail();

        $this->actingAs($peserta, 'peserta');

        $this->post('/peserta/tes/'.$tes->tes_id.'/mulai')
            ->assertRedirect(route('peserta.ujian.kerjakan', $tes));

        $this->assertDatabaseHas('cbt_absensi', [
            'absensi_user_id' => $peserta->user_id,
            'absensi_tes_id' => $tes->tes_id,
            'absensi_status' => Absensi::HADIR,
            'absensi_sumber' => Absensi::SUMBER_UJIAN,
        ]);
    }
}
