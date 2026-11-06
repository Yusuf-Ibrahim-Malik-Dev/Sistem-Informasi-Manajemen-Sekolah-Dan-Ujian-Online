<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Peserta;
use App\Models\Soal;
use App\Models\Tes;
use App\Models\TesSoal;
use App\Models\TesUser;
use App\Models\Topik;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class Audit2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_import_peserta(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin, 'web');

        $csv = "user_name,user_password,user_firstname,grup_nama,user_email,user_detail\n";
        $csv .= "baru1,rahasia,Peserta Baru,XI MM,baru1@example.com,Ruang 9\n";
        $csv .= "lutfi,baru,Lutfi Baru,XI MM,lutfi@example.com,Ruang 1\n";

        $file = UploadedFile::fake()->createWithContent('peserta.csv', $csv);

        $this->post('/admin/peserta/import', ['file' => $file])->assertRedirect();

        $this->assertDatabaseHas('cbt_user', ['user_name' => 'baru1', 'user_firstname' => 'Peserta Baru']);
        $this->assertDatabaseHas('cbt_user', ['user_name' => 'lutfi', 'user_firstname' => 'Lutfi Baru']);
    }

    public function test_acak_jawaban_dihormati(): void
    {
        $topik = Topik::where('topik_nama', 'Aljabar')->firstOrFail();
        $grup = UserGroup::firstOrFail();
        $tes = Tes::create([
            'tes_nama' => 'Ujian Non Acak',
            'tes_detail' => 'x',
            'tes_begin_time' => now()->subHour(),
            'tes_end_time' => now()->addDay(),
            'tes_duration_time' => 30,
            'tes_score_right' => 10,
            'tes_max_score' => 100,
        ]);
        $tes->groups()->sync([$grup->grup_id]);
        $tes->topikSets()->create([
            'tset_topik_id' => $topik->topik_id,
            'tset_tipe' => 1,
            'tset_difficulty' => 2,
            'tset_jumlah' => 3,
            'tset_acak_jawaban' => 0,
            'tset_acak_soal' => 0,
        ]);

        $peserta = Peserta::where('user_name', 'siti')->firstOrFail();
        $this->actingAs($peserta, 'peserta');
        $this->post('/peserta/tes/' . $tes->tes_id . '/mulai')->assertRedirect();

        $attempt = TesUser::where('tesuser_tes_id', $tes->tes_id)->firstOrFail();
        $tesSoal = TesSoal::where('tessoal_tesuser_id', $attempt->tesuser_id)->with('pilihan')->firstOrFail();
        $asli = $tesSoal->soal->jawabans()->pluck('jawaban_id')->all();
        $tersimpan = $tesSoal->pilihan->pluck('soaljawaban_jawaban_id')->all();

        $this->assertSame($asli, $tersimpan);
    }

    public function test_token_lama_tidak_tertutup_token_baru_kedaluwarsa(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $tes = Tes::firstOrFail();

        \App\Models\TesToken::generate('123456', $admin->id, $tes->tes_id, 1)
            ->update(['token_ts' => now()->subMinutes(20)]);

        \App\Models\TesToken::create([
            'token_isi' => '123456',
            'token_user_id' => $admin->id,
            'token_ts' => now()->subMinutes(10),
            'token_aktif' => 5,
            'token_tes_id' => $tes->tes_id,
        ]);

        $this->assertNotNull(\App\Models\TesToken::validFor('123456', $tes->tes_id));
    }

    public function test_nilai_tidak_dijawab_diterapkan_saat_selesai(): void
    {
        $peserta = Peserta::where('user_name', 'fajar')->firstOrFail();
        $grup = UserGroup::findOrFail($peserta->user_grup_id);
        $aljabar = Topik::where('topik_nama', 'Aljabar')->firstOrFail();
        $tes = Tes::create([
            'tes_nama' => 'Ujian Penalti Kosong',
            'tes_detail' => 'x',
            'tes_begin_time' => now()->subHour(),
            'tes_end_time' => now()->addDay(),
            'tes_duration_time' => 30,
            'tes_score_right' => 10,
            'tes_score_wrong' => 0,
            'tes_score_unanswered' => -2,
            'tes_max_score' => 100,
        ]);
        $tes->groups()->sync([$grup->grup_id]);
        $tes->topikSets()->create([
            'tset_topik_id' => $aljabar->topik_id,
            'tset_tipe' => 1,
            'tset_difficulty' => 2,
            'tset_jumlah' => 2,
        ]);

        $this->actingAs($peserta, 'peserta');
        $this->post('/peserta/tes/' . $tes->tes_id . '/mulai')->assertRedirect();

        $attempt = TesUser::where('tesuser_tes_id', $tes->tes_id)->firstOrFail();
        $this->post('/peserta/tes/' . $tes->tes_id . '/selesai')->assertRedirect();

        $attempt->refresh();
        $this->assertSame(TesUser::STATUS_SELESAI, (int) $attempt->tesuser_status);
        $this->assertSame(-4.0, (float) $attempt->nilai);
        $this->assertSame(2, $attempt->soals()->where('tessoal_nilai', -2)->count());
    }

    public function test_jawaban_yang_dipakai_ujian_tidak_bisa_dihapus(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $referensi = \App\Models\TesSoalJawaban::firstOrFail();
        $soal = $referensi->tesSoal->soal;
        $jawabanDipakai = $referensi->soaljawaban_jawaban_id;

        $this->actingAs($admin, 'web');
        $response = $this->put('/admin/soal/' . $soal->soal_id, [
            'soal_topik_id' => $soal->soal_topik_id,
            'soal_detail' => $soal->soal_detail,
            'soal_tipe' => 1,
            'soal_difficulty' => $soal->soal_difficulty,
            'soal_aktif' => 1,
            'jawaban' => [
                ['detail' => 'Pilihan baru A'],
                ['detail' => 'Pilihan baru B'],
            ],
            'jawaban_benar' => 0,
        ]);

        $response->assertSessionHasErrors('jawaban');
        $this->assertDatabaseHas('cbt_jawaban', ['jawaban_id' => $jawabanDipakai]);
    }

    public function test_ringkasan_menghitung_jawaban_singkat(): void
    {
        $peserta = Peserta::where('user_name', 'fajar')->firstOrFail();
        $grup = UserGroup::findOrFail($peserta->user_grup_id);
        $aljabar = Topik::where('topik_nama', 'Aljabar')->firstOrFail();
        $tes = Tes::create([
            'tes_nama' => 'Ujian Singkat',
            'tes_detail' => 'x',
            'tes_begin_time' => now()->subHour(),
            'tes_end_time' => now()->addDay(),
            'tes_duration_time' => 30,
            'tes_score_right' => 10,
            'tes_max_score' => 100,
        ]);
        $tes->groups()->sync([$grup->grup_id]);
        $tes->topikSets()->create([
            'tset_topik_id' => $aljabar->topik_id,
            'tset_tipe' => 3,
            'tset_difficulty' => 2,
            'tset_jumlah' => 1,
        ]);
        $this->actingAs($peserta, 'peserta');
        $this->post('/peserta/tes/' . $tes->tes_id . '/mulai')->assertRedirect();

        $attempt = TesUser::where('tesuser_tes_id', $tes->tes_id)->firstOrFail();
        $soal = TesSoal::where('tessoal_tesuser_id', $attempt->tesuser_id)->firstOrFail();

        $this->post('/peserta/tes/' . $tes->tes_id . '/jawab', [
            'tessoal_id' => $soal->tessoal_id,
            'jawaban_text' => $soal->soal->soal_kunci,
            'aksi' => 'selesai',
        ])->assertRedirect();

        $ringkasan = \App\Support\HasilUjian::ringkas($attempt->fresh());
        $this->assertSame(1, $ringkasan['benar']);
    }

    public function test_pembuat_soal_ditampilkan_di_admin(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $soal = Soal::whereNotNull('soal_creator_id')->firstOrFail();

        $this->assertSame($admin->id, (int) $soal->soal_creator_id);

        $this->actingAs($admin, 'web');
        $this->get('/admin/soal')
            ->assertOk()
            ->assertSee('Nama Guru')
            ->assertSee($admin->nama);
        $this->get('/admin/soal/grup/' . $soal->soal_topik_id)
            ->assertOk()
            ->assertSee($admin->nama);
    }

    public function test_grup_soal_menyimpan_guru_dan_tipe_ujian(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $modul = \App\Models\Modul::firstOrFail();

        $this->actingAs($admin, 'web');

        $this->post('/admin/soal/grup', [
            'topik_modul_id' => $modul->modul_id,
            'topik_nama' => 'Fisika Dasar',
            'topik_guru_id' => $admin->id,
            'topik_tipe_ujian' => Topik::TIPE_UAS,
            'topik_detail' => 'Grup soal fisika.',
            'topik_aktif' => 1,
        ])->assertRedirect();

        $topik = Topik::where('topik_nama', 'Fisika Dasar')->firstOrFail();
        $this->assertSame($admin->id, (int) $topik->topik_guru_id);
        $this->assertSame(Topik::TIPE_UAS, $topik->topik_tipe_ujian);

        $this->get('/admin/soal')
            ->assertOk()
            ->assertSee('Tipe Ujian')
            ->assertSee('UAS')
            ->assertSee($admin->nama);

        $this->get('/admin/soal/grup/' . $topik->topik_id)
            ->assertOk()
            ->assertSee('UAS')
            ->assertSee($admin->nama);

        $this->put('/admin/soal/grup/' . $topik->topik_id, [
            'topik_modul_id' => $modul->modul_id,
            'topik_nama' => 'Fisika Dasar',
            'topik_guru_id' => $admin->id,
            'topik_tipe_ujian' => Topik::TIPE_UTS,
            'topik_detail' => 'Grup soal fisika.',
            'topik_aktif' => 1,
        ])->assertRedirect();

        $this->assertSame(Topik::TIPE_UTS, $topik->fresh()->topik_tipe_ujian);
    }

    public function test_hasil_peserta_disembunyikan_sampai_semua_selesai_lalu_ranking_muncul(): void
    {
        $peserta = Peserta::where('user_name', 'fajar')->firstOrFail();
        $grupId = $peserta->user_grup_id;
        $topik = Topik::where('topik_nama', 'Aljabar')->firstOrFail();

        $tes = Tes::create([
            'tes_nama' => 'Ujian Ranking',
            'tes_detail' => 'x',
            'tes_begin_time' => now()->subHour(),
            'tes_end_time' => now()->addDay(),
            'tes_duration_time' => 30,
            'tes_results_to_users' => true,
            'tes_detail_to_users' => true,
            'tes_ranking_to_users' => true,
            'tes_score_right' => 10,
            'tes_max_score' => 100,
        ]);
        $tes->groups()->sync([$grupId]);
        $tes->topikSets()->create([
            'tset_topik_id' => $topik->topik_id,
            'tset_tipe' => 1,
            'tset_difficulty' => 2,
            'tset_jumlah' => 1,
        ]);

        $this->actingAs($peserta, 'peserta');
        $this->post('/peserta/tes/' . $tes->tes_id . '/mulai')->assertRedirect();
        $this->post('/peserta/tes/' . $tes->tes_id . '/selesai')->assertRedirect();

        $this->get('/peserta/tes/' . $tes->tes_id . '/hasil')
            ->assertOk()
            ->assertSee('Rincian jawaban belum tersedia')
            ->assertDontSee('Jawaban Benar')
            ->assertSee('Ranking Ujian');

        $pesertaLain = Peserta::where('user_grup_id', $grupId)
            ->where('user_id', '!=', $peserta->user_id)
            ->get();

        foreach ($pesertaLain as $lain) {
            TesUser::create([
                'tesuser_tes_id' => $tes->tes_id,
                'tesuser_user_id' => $lain->user_id,
                'tesuser_status' => TesUser::STATUS_SELESAI,
                'tesuser_creation_time' => now(),
            ]);
        }

        $this->get('/peserta/tes/' . $tes->tes_id . '/hasil')
            ->assertOk()
            ->assertSee('Jawaban Benar')
            ->assertSee('Ranking Ujian')
            ->assertSee('Kamu');
    }
}
