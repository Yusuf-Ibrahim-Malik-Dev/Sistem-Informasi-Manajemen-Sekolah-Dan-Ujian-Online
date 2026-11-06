<?php

namespace Tests\Feature;

use App\Models\Konfigurasi;
use App\Models\OrangTua;
use App\Models\Pembayaran;
use App\Models\Peserta;
use App\Models\Tes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_gate_pembayaran_menahan_peserta_yang_belum_diverifikasi(): void
    {
        Konfigurasi::set('cbt_gate_pembayaran', 'ya');

        $peserta = Peserta::where('user_name', 'joko')->firstOrFail();
        $tes = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->firstOrFail();

        $this->actingAs($peserta, 'peserta');

        $this->get(route('peserta.tes.show', $tes))
            ->assertOk()
            ->assertSee('Pembayaran harus diverifikasi admin');

        $this->from(route('peserta.tes.show', $tes))
            ->post(route('peserta.tes.mulai', $tes))
            ->assertRedirect(route('peserta.tes.show', $tes))
            ->assertSessionHasErrors('pembayaran');

        $this->assertDatabaseMissing('cbt_tes_user', [
            'tesuser_tes_id' => $tes->tes_id,
            'tesuser_user_id' => $peserta->user_id,
        ]);
    }

    public function test_gate_pembayaran_dibuka_setelah_pembayaran_diverifikasi(): void
    {
        Konfigurasi::set('cbt_gate_pembayaran', 'ya');

        $peserta = Peserta::where('user_name', 'lutfi')->firstOrFail();
        $orangTua = OrangTua::where('ortu_username', 'orangtua1')->firstOrFail();
        $tes = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->firstOrFail();

        Pembayaran::create([
            'pembayaran_user_id' => $peserta->user_id,
            'pembayaran_tes_id' => $tes->tes_id,
            'pembayaran_ortu_id' => $orangTua->ortu_id,
            'pembayaran_foundation_id' => $peserta->user_foundation_id,
            'pembayaran_school_id' => $peserta->user_school_id,
            'pembayaran_nominal' => $tes->tes_biaya,
            'pembayaran_status' => Pembayaran::DIVERIFIKASI,
            'pembayaran_catatan' => 'Lunas.',
            'pembayaran_verified_at' => now(),
        ]);

        $this->actingAs($peserta, 'peserta');

        $this->get(route('peserta.tes.show', $tes))
            ->assertOk()
            ->assertDontSee('Pembayaran harus diverifikasi admin');

        $this->post(route('peserta.tes.mulai', $tes), ['token' => '123456'])
            ->assertRedirect(route('peserta.ujian.kerjakan', $tes));
    }

    public function test_gate_nonaktif_tidak_menghalangi_ujian_berbayar(): void
    {
        Konfigurasi::set('cbt_gate_pembayaran', 'tidak');

        $peserta = Peserta::where('user_name', 'joko')->firstOrFail();
        $tes = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->firstOrFail();

        $this->actingAs($peserta, 'peserta');

        $this->get(route('peserta.tes.show', $tes))
            ->assertOk()
            ->assertDontSee('Pembayaran harus diverifikasi admin');

        $this->post(route('peserta.tes.mulai', $tes), ['token' => '123456'])
            ->assertRedirect(route('peserta.ujian.kerjakan', $tes));
    }
}
