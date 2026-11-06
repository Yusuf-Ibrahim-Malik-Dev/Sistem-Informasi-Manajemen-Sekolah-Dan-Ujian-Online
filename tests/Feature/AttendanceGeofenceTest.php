<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AttendanceLocation;
use App\Models\AttendancePolicy;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Peserta;
use App\Models\Tes;
use App\Support\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceGeofenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_checkin_harian_diterima_di_dalam_radius(): void
    {
        $this->travelTo(now()->copy()->setTime(8, 0));

        $peserta = Peserta::where('user_name', 'joko')->firstOrFail();
        $location = $this->locationFor($peserta);

        $response = $this->actingAs($peserta, 'peserta')->postJson(route('peserta.absensi.checkin'), [
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'accuracy_meters' => 12,
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $record = AttendanceRecord::where('student_id', $peserta->user_id)->firstOrFail();

        $this->assertTrue($record->isPresent());
        $this->assertSame(AttendanceRecord::METHOD_GPS, $record->method);
        $this->assertLessThan($location->radius_meters, $record->distance_meters);

        $this->assertDatabaseHas('att_attendance_location_checks', [
            'student_id' => $peserta->user_id,
            'validation_status' => 'valid',
        ]);
    }

    public function test_checkin_harian_ditolak_di_luar_radius(): void
    {
        $this->travelTo(now()->copy()->setTime(8, 0));

        $peserta = Peserta::where('user_name', 'joko')->firstOrFail();
        $location = $this->locationFor($peserta);

        $response = $this->actingAs($peserta, 'peserta')->postJson(route('peserta.absensi.checkin'), [
            'latitude' => $location->latitude + 0.01,
            'longitude' => $location->longitude,
            'accuracy_meters' => 10,
        ]);

        $response->assertStatus(422)->assertJson(['ok' => false]);
        $this->assertStringContainsString('luar radius', $response->json('message'));

        $schoolId = $peserta->user_school_id ?: $peserta->grup?->grup_school_id;
        $session = AttendanceSession::where('school_id', $schoolId)
            ->where('session_type', 'daily')
            ->whereDate('session_date', now()->toDateString())
            ->first();

        $this->assertDatabaseMissing('att_attendance_records', [
            'student_id' => $peserta->user_id,
            'session_id' => $session?->id,
        ]);

        $this->assertDatabaseHas('att_attendance_location_checks', [
            'student_id' => $peserta->user_id,
            'validation_status' => 'out_of_radius',
        ]);
    }

    public function test_checkin_ujian_membuka_prasyarat_mulai_ujian(): void
    {
        $peserta = Peserta::where('user_name', 'joko')->firstOrFail();
        $tes = Tes::where('tes_nama', 'Ulangan Bahasa Indonesia')->firstOrFail();
        $location = $this->locationFor($peserta);

        $tes->update([
            'tes_absensi_wajib' => true,
            'tes_absensi_location_id' => $location->id,
            'tes_checkin_mulai' => now()->subMinutes(10),
            'tes_checkin_selesai' => now()->addMinutes(10),
            'tes_begin_time' => now()->subHour(),
            'tes_end_time' => now()->addHour(),
        ]);

        $this->actingAs($peserta, 'peserta');

        $this->from(route('peserta.tes.show', $tes))
            ->post(route('peserta.tes.mulai', $tes))
            ->assertSessionHasErrors('absensi');

        $this->postJson(route('peserta.tes.checkin', $tes), [
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'accuracy_meters' => 8,
        ])->assertOk()->assertJson(['ok' => true]);

        $this->post(route('peserta.tes.mulai', $tes), ['token' => '123456'])
            ->assertRedirect(route('peserta.ujian.kerjakan', $tes));

        $this->assertDatabaseHas('cbt_tes_user', [
            'tesuser_tes_id' => $tes->tes_id,
            'tesuser_user_id' => $peserta->user_id,
        ]);
    }

    public function test_admin_dapat_menyimpan_kebijakan_absensi_harian(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $peserta = Peserta::where('user_name', 'joko')->firstOrFail();
        $school = app(AttendanceService::class)->schoolForPeserta($peserta);

        $this->actingAs($admin, 'web')
            ->put(route('admin.absensi.lokasi.policy'), [
                'school_id' => $school->id,
                'require_location' => 1,
                'allow_manual' => 1,
                'require_checkin' => 1,
                'require_checkout' => 1,
                'service_start' => '06:30',
                'service_end' => '16:00',
                'late_tolerance_minutes' => 10,
                'max_accuracy_meters' => 75,
                'is_required' => 1,
            ])
            ->assertRedirect();

        $policy = AttendancePolicy::where('school_id', $school->id)
            ->where('policy_type', AttendancePolicy::TYPE_DAILY)
            ->whereNull('academic_year_id')
            ->firstOrFail();

        $this->assertTrue($policy->require_checkout);
        $this->assertTrue($policy->is_required);
        $this->assertSame(10, $policy->late_tolerance_minutes);
        $this->assertSame(75, $policy->max_accuracy_meters);
    }

    public function test_admin_dapat_override_absensi_dengan_alasan_dan_audit(): void
    {
        $peserta = Peserta::where('user_name', 'joko')->firstOrFail();
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $session = app(AttendanceService::class)->dailySessionFor($peserta, now(), true);

        $this->actingAs($admin, 'web')
            ->post(route('admin.absensi.monitoring.override'), [
                'session_id' => $session->id,
                'student_id' => $peserta->user_id,
                'status' => AttendanceRecord::IZIN,
                'reason' => 'Verifikasi manual: perangkat peserta tidak mendukung GPS.',
            ])
            ->assertRedirect();

        $record = AttendanceRecord::where('session_id', $session->id)
            ->where('student_id', $peserta->user_id)
            ->firstOrFail();

        $this->assertSame(AttendanceRecord::IZIN, $record->status);
        $this->assertTrue($record->is_override);

        $this->assertDatabaseHas('att_attendance_corrections', [
            'record_id' => $record->id,
            'new_status' => AttendanceRecord::IZIN,
        ]);

        $this->assertDatabaseHas('att_attendance_audit_events', [
            'session_id' => $session->id,
            'student_id' => $peserta->user_id,
            'event_type' => 'attendance_override',
        ]);
    }

    private function locationFor(Peserta $peserta): AttendanceLocation
    {
        $school = app(AttendanceService::class)->schoolForPeserta($peserta);

        $this->assertNotNull($school);

        $location = AttendanceLocation::where('school_id', $school->id)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($location);

        $this->assertDatabaseHas('att_attendance_policies', [
            'school_id' => $school->id,
            'policy_type' => AttendancePolicy::TYPE_DAILY,
        ]);

        return $location;
    }
}
