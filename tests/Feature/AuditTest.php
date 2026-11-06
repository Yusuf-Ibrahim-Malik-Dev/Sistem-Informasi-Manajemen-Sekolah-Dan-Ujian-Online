<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Jawaban;
use App\Models\Modul;
use App\Models\Peserta;
use App\Models\Soal;
use App\Models\Tes;
use App\Models\TesSoal;
use App\Models\TesToken;
use App\Models\TesUser;
use App\Models\Topik;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_admin_dapat_membuat_soal_dan_tes_baru(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin, 'web');

        $topik = Topik::where('topik_nama', 'Aljabar')->firstOrFail();
        $grup = UserGroup::firstOrFail();

        $this->post('/admin/modul', ['modul_nama' => 'Fisika', 'modul_aktif' => 1])->assertRedirect();
        $modul = Modul::where('modul_nama', 'Fisika')->firstOrFail();

        $this->post('/admin/topik', [
            'topik_modul_id' => $modul->modul_id,
            'topik_nama' => 'Gerak',
            'topik_guru_id' => $admin->id,
            'topik_tipe_ujian' => Topik::TIPE_UTS,
            'topik_aktif' => 1,
        ])->assertRedirect();
        $topikBaru = Topik::where('topik_nama', 'Gerak')->firstOrFail();

        $this->post('/admin/grup', ['grup_nama' => 'XII IPA'])->assertRedirect();
        $this->post('/admin/level', ['level' => 'pengawas', 'keterangan' => 'Pengawas'])->assertRedirect();
        $this->post('/admin/user', [
            'username' => 'pengawas1',
            'nama' => 'Pengawas Satu',
            'level' => 'pengawas',
            'password' => 'rahasia',
            'password_confirmation' => 'rahasia',
        ])->assertRedirect();

        $this->post('/admin/soal', [
            'soal_topik_id' => $topikBaru->topik_id,
            'soal_detail' => 'Berapa percepatan gravitasi bumi?',
            'soal_tipe' => 1,
            'soal_difficulty' => 1,
            'soal_aktif' => 1,
            'jawaban' => [
                ['detail' => '9,8 m/s2'],
                ['detail' => '10 m/s2'],
            ],
            'jawaban_benar' => 0,
        ])->assertRedirect();

        $soal = Soal::where('soal_topik_id', $topikBaru->topik_id)->firstOrFail();
        $this->assertSame(2, $soal->jawabans()->count());
        $this->assertSame(1, $soal->jawabans()->where('jawaban_benar', 1)->count());

        $this->post('/admin/tes', [
            'tes_nama' => 'Ujian Fisika',
            'tes_detail' => 'Ujian fisika bab gerak.',
            'tes_begin_time' => now()->subHour()->format('Y-m-d H:i:s'),
            'tes_end_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'tes_duration_time' => 30,
            'tes_ip_range' => '*.*.*.*',
            'tes_score_right' => 10,
            'tes_score_wrong' => 0,
            'tes_score_unanswered' => 0,
            'tes_max_score' => 100,
            'groups' => [$grup->grup_id],
            'sets' => [[
                'topik_id' => $topikBaru->topik_id,
                'tipe' => 1,
                'difficulty' => 1,
                'jumlah' => 1,
                'acak_soal' => 1,
            ]],
        ])->assertRedirect('/admin/tes');

        $tes = Tes::where('tes_nama', 'Ujian Fisika')->firstOrFail();
        $this->assertSame(1, $tes->topikSets()->count());
        $this->assertSame(1, $tes->groups()->count());

        $this->put('/admin/tes/' . $tes->tes_id, [
            'tes_nama' => 'Ujian Fisika Revisi',
            'tes_detail' => 'Ujian fisika bab gerak.',
            'tes_begin_time' => now()->subHour()->format('Y-m-d H:i:s'),
            'tes_end_time' => now()->addDay()->format('Y-m-d H:i:s'),
            'tes_duration_time' => 30,
            'tes_ip_range' => '*.*.*.*',
            'tes_score_right' => 10,
            'tes_score_wrong' => 0,
            'tes_score_unanswered' => 0,
            'tes_max_score' => 100,
            'groups' => [$grup->grup_id],
            'sets' => [[
                'topik_id' => $topik->topik_id,
                'tipe' => 1,
                'difficulty' => 2,
                'jumlah' => 1,
                'acak_soal' => 1,
            ]],
        ])->assertRedirect('/admin/tes');

        $this->assertSame('Ujian Fisika Revisi', $tes->fresh()->tes_nama);
    }

    public function test_soal_essay_dan_singkat_dapat_dikerjakan_dan_dinilai(): void
    {
        $grup = UserGroup::firstOrFail();
        $aljabar = Topik::where('topik_nama', 'Aljabar')->firstOrFail();
        $tes = Tes::create([
            'tes_nama' => 'Ujian Essay',
            'tes_detail' => 'Ujian essay.',
            'tes_begin_time' => now()->subHour(),
            'tes_end_time' => now()->addDay(),
            'tes_duration_time' => 30,
            'tes_results_to_users' => true,
            'tes_detail_to_users' => true,
            'tes_score_right' => 10,
            'tes_score_wrong' => 0,
            'tes_score_unanswered' => 0,
            'tes_max_score' => 100,
        ]);
        $tes->groups()->sync([$grup->grup_id]);
        $tes->topikSets()->create([
            'tset_topik_id' => $aljabar->topik_id,
            'tset_tipe' => 2,
            'tset_difficulty' => 2,
            'tset_jumlah' => 1,
        ]);
        $tes->topikSets()->create([
            'tset_topik_id' => $aljabar->topik_id,
            'tset_tipe' => 3,
            'tset_difficulty' => 2,
            'tset_jumlah' => 1,
        ]);

        $peserta = Peserta::where('user_name', 'dewi')->firstOrFail();
        $this->actingAs($peserta, 'peserta');

        $this->post('/peserta/tes/' . $tes->tes_id . '/mulai')->assertRedirect();

        $attempt = TesUser::where('tesuser_tes_id', $tes->tes_id)->firstOrFail();
        $essay = TesSoal::where('tessoal_tesuser_id', $attempt->tesuser_id)
            ->whereHas('soal', fn ($q) => $q->where('soal_tipe', 2))->firstOrFail();
        $singkat = TesSoal::where('tessoal_tesuser_id', $attempt->tesuser_id)
            ->whereHas('soal', fn ($q) => $q->where('soal_tipe', 3))->firstOrFail();

        $this->post('/peserta/tes/' . $tes->tes_id . '/jawab', [
            'tessoal_id' => $essay->tessoal_id,
            'jawaban_text' => 'Jawaban essay lengkap.',
            'aksi' => 'simpan',
        ])->assertRedirect();

        $this->post('/peserta/tes/' . $tes->tes_id . '/jawab', [
            'tessoal_id' => $singkat->tessoal_id,
            'jawaban_text' => $singkat->soal->soal_kunci,
            'aksi' => 'selesai',
        ])->assertRedirect(route('peserta.ujian.hasil', $tes));

        $this->assertEquals(10, $singkat->fresh()->tessoal_nilai);
        $this->assertSame(TesUser::STATUS_SELESAI, $attempt->fresh()->tesuser_status);
        $this->get('/peserta/tes/' . $tes->tes_id . '/hasil')->assertOk();
    }

    public function test_ujian_kedaluwarsa_otomatis_dikumpulkan(): void
    {
        $peserta = Peserta::where('user_name', 'rina')->firstOrFail();
        $tes = Tes::where('tes_nama', 'Try Out Matematika')->firstOrFail();

        $this->actingAs($peserta, 'peserta');

        $this->post('/peserta/tes/' . $tes->tes_id . '/mulai')->assertRedirect();

        $attempt = TesUser::where('tesuser_tes_id', $tes->tes_id)->firstOrFail();

        Carbon::setTestNow(now()->addMinutes($tes->tes_duration_time + 1));

        $this->get('/peserta/tes/' . $tes->tes_id . '/kerjakan')
            ->assertRedirect(route('peserta.ujian.hasil', $tes));

        $this->assertSame(TesUser::STATUS_SELESAI, $attempt->fresh()->tesuser_status);
    }

    public function test_token_kedaluwarsa_tidak_dapat_dipakai(): void
    {
        $tes = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->firstOrFail();

        $token = TesToken::where('token_isi', '123456')->firstOrFail();
        $token->update(['token_ts' => now()->subMinutes(10), 'token_aktif' => 5]);

        $this->assertNull(TesToken::validFor('123456', $tes->tes_id));
    }

    public function test_admin_operator_hanya_dapat_mengakses_menu_yang_diizinkan(): void
    {
        $operator = Admin::where('username', 'operatorsoal')->firstOrFail();

        $this->actingAs($operator, 'web');
        $this->get('/admin/modul')->assertOk();
        $this->get('/admin/tes')->assertRedirect('/admin');
        $this->get('/admin/user')->assertRedirect('/admin');
    }
}
