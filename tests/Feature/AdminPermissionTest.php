<?php

namespace Tests\Feature;

use App\Models\Access;
use App\Models\Admin;
use App\Models\Soal;
use App\Models\UserMenuAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_hak_aksi_level_dapat_dibatasi_per_aksi(): void
    {
        $operator = Admin::where('username', 'operatorsoal')->firstOrFail();

        Access::where('level', 'operator-soal')
            ->where('kode_menu', 'soal')
            ->update(['can_delete' => false, 'can_approve' => false]);

        $operator->refresh()->load(['personalMenuAccesses', 'accesses']);

        $this->assertTrue($operator->canAdd('soal'));
        $this->assertTrue($operator->canEdit('soal'));
        $this->assertFalse($operator->canDelete('soal'));
        $this->assertFalse($operator->canApprove('soal'));
    }

    public function test_override_personal_mengalahkan_hak_level(): void
    {
        $operator = Admin::where('username', 'operatorsoal')->firstOrFail();

        UserMenuAccess::create([
            'user_id' => $operator->id,
            'kode_menu' => 'soal',
            'is_allowed' => true,
            'can_add' => false,
            'can_edit' => true,
            'can_delete' => false,
            'can_approve' => false,
        ]);

        $operator->refresh()->load(['personalMenuAccesses', 'accesses']);

        $this->assertTrue($operator->hasAccess('soal'));
        $this->assertFalse($operator->canAdd('soal'));
        $this->assertTrue($operator->canEdit('soal'));
        $this->assertFalse($operator->canDelete('soal'));
        $this->assertFalse($operator->canApprove('soal'));
    }

    public function test_middleware_menolak_aksi_tanpa_izin_delete(): void
    {
        $operator = Admin::where('username', 'operatorsoal')->firstOrFail();
        $soal = Soal::firstOrFail();

        Access::where('level', 'operator-soal')
            ->where('kode_menu', 'soal')
            ->update(['can_delete' => false]);

        $this->actingAs($operator, 'web')
            ->delete(route('admin.soal.destroy', $soal))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('cbt_soal', ['soal_id' => $soal->soal_id]);
    }

    public function test_admin_utama_tetap_memiliki_semua_aksi(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();

        $this->assertTrue($admin->canAdd('soal'));
        $this->assertTrue($admin->canEdit('soal'));
        $this->assertTrue($admin->canDelete('soal'));
        $this->assertTrue($admin->canApprove('spp-pembayaran'));
    }
}
