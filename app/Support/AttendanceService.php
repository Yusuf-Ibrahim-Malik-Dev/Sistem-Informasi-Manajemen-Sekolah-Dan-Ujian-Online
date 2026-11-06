<?php

namespace App\Support;

use App\Models\AttendanceAuditEvent;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceLocation;
use App\Models\AttendanceLocationCheck;
use App\Models\AttendancePolicy;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Konfigurasi;
use App\Models\Peserta;
use App\Models\School;
use App\Models\Tes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public const EARTH_RADIUS_METERS = 6371000;

    public static function radiusDefault(): int
    {
        return (int) Konfigurasi::get('default_radius_meters', 50);
    }

    public static function maxAccuracyDefault(): int
    {
        return (int) Konfigurasi::get('default_max_accuracy_meters', 100);
    }

    public function distanceMeters(?float $lat1, ?float $lon1, ?float $lat2, ?float $lon2): ?float
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }

        $latFrom = deg2rad($lat1);
        $latTo = deg2rad($lat2);
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2;

        return 2 * self::EARTH_RADIUS_METERS * asin(min(1, sqrt($a)));
    }

    public function schoolForPeserta(Peserta $peserta): ?School
    {
        if ($peserta->user_school_id) {
            return School::find($peserta->user_school_id);
        }

        $schoolId = $peserta->grup?->grup_school_id;

        return $schoolId ? School::find($schoolId) : null;
    }

    public function schoolForTes(Tes $tes): ?School
    {
        if ($tes->tes_school_id) {
            return School::find($tes->tes_school_id);
        }

        $schoolId = $tes->groups()->first()?->grup_school_id;

        return $schoolId ? School::find($schoolId) : null;
    }

    public function academicYearForPeserta(Peserta $peserta): ?int
    {
        $id = $peserta->grup?->grup_academic_year_id;

        if ($id) {
            return (int) $id;
        }

        return $this->schoolForPeserta($peserta)?->academicYears()
            ->where('is_active', true)
            ->value('id');
    }

    public function locationForSchool(?int $locationId, School $school, ?Carbon $on = null): ?AttendanceLocation
    {
        $on ??= now();

        $query = AttendanceLocation::where('school_id', $school->id)
            ->where('is_active', true)
            ->where(function ($q) use ($on) {
                $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', $on->toDateString());
            })
            ->where(function ($q) use ($on) {
                $q->whereNull('valid_to')->orWhereDate('valid_to', '>=', $on->toDateString());
            });

        if ($locationId) {
            return $query->whereKey($locationId)->first();
        }

        return $query->orderBy('id')->first();
    }

    public function policyFor(
        School $school,
        string $type = AttendancePolicy::TYPE_DAILY,
        ?int $academicYearId = null,
        bool $create = true
    ): ?AttendancePolicy {
        $base = AttendancePolicy::where('school_id', $school->id)
            ->where('policy_type', $type);

        $policy = null;

        if ($academicYearId) {
            $policy = (clone $base)->where('academic_year_id', $academicYearId)->first();
        }

        $policy ??= (clone $base)->whereNull('academic_year_id')->first();

        if ($policy === null && $create) {
            $policy = AttendancePolicy::create([
                'foundation_id' => $school->foundation_id,
                'school_id' => $school->id,
                'academic_year_id' => null,
                'policy_type' => $type,
                'require_location' => true,
                'allow_manual' => true,
                'require_checkin' => true,
                'require_checkout' => false,
                'late_tolerance_minutes' => 0,
                'max_accuracy_meters' => self::maxAccuracyDefault(),
                'is_required' => false,
            ]);
        }

        return $policy;
    }

    public function dailySessionFor(Peserta $peserta, ?Carbon $date = null, bool $create = true): ?AttendanceSession
    {
        $school = $this->schoolForPeserta($peserta);

        if (! $school) {
            return null;
        }

        $date ??= now();
        $academicYearId = $this->academicYearForPeserta($peserta);
        $policy = $this->policyFor($school, AttendancePolicy::TYPE_DAILY, $academicYearId, $create);

        $attributes = [
            'school_id' => $school->id,
            'exam_id' => null,
            'session_type' => AttendanceSession::TYPE_DAILY,
            'session_date' => $date->toDateString(),
        ];

        $session = AttendanceSession::where($attributes)->first();

        if ($session) {
            return $session;
        }

        if (! $create) {
            return null;
        }

        $location = $policy?->require_location
            ? $this->locationForSchool(null, $school, $date)
            : null;

        $startsAt = $date->copy()->startOfDay();
        $endsAt = $date->copy()->endOfDay();

        if ($policy?->service_start) {
            $startsAt = $date->copy()->setTimeFromTimeString((string) $policy->service_start);
        }

        if ($policy?->service_end) {
            $endsAt = $date->copy()->setTimeFromTimeString((string) $policy->service_end);
        }

        return AttendanceSession::create($attributes + [
            'foundation_id' => $school->foundation_id,
            'academic_year_id' => $academicYearId,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'location_id' => $location?->id,
            'requires_checkin' => (bool) ($policy?->require_checkin ?? true),
            'allow_manual' => (bool) ($policy?->allow_manual ?? true),
        ]);
    }

    public function examSessionFor(Tes $tes, bool $create = true): ?AttendanceSession
    {
        $school = $this->schoolForTes($tes);

        if (! $school) {
            return null;
        }

        $attributes = [
            'exam_id' => $tes->tes_id,
            'session_type' => AttendanceSession::TYPE_EXAM,
        ];

        $session = AttendanceSession::where($attributes)->first();

        if (! $create && ! $session) {
            return null;
        }

        if (! $create) {
            return $session;
        }

        $begin = $tes->tes_begin_time ? Carbon::parse($tes->tes_begin_time) : now();
        $startsAt = $tes->tes_checkin_mulai ? Carbon::parse($tes->tes_checkin_mulai) : $begin;
        $endsAt = $tes->tes_checkin_selesai
            ? Carbon::parse($tes->tes_checkin_selesai)
            : ($tes->tes_end_time ? Carbon::parse($tes->tes_end_time) : $begin->copy()->endOfDay());

        return AttendanceSession::updateOrCreate($attributes, [
            'foundation_id' => $school->foundation_id,
            'school_id' => $school->id,
            'academic_year_id' => $tes->tes_academic_year_id,
            'session_date' => $begin->toDateString(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'location_id' => $tes->tes_absensi_location_id,
            'requires_checkin' => (bool) $tes->tes_absensi_wajib,
            'allow_manual' => true,
        ]);
    }

    public function recordFor(AttendanceSession $session, int $studentId): ?AttendanceRecord
    {
        return AttendanceRecord::where('session_id', $session->id)
            ->where('student_id', $studentId)
            ->first();
    }

    public function requiresExamAttendance(Tes $tes): bool
    {
        return (bool) $tes->tes_absensi_wajib;
    }

    public function hasValidExamAttendance(Tes $tes, Peserta $peserta): bool
    {
        if (! $this->requiresExamAttendance($tes)) {
            return true;
        }

        $session = $this->examSessionFor($tes, false);

        if (! $session) {
            return false;
        }

        $record = $this->recordFor($session, (int) $peserta->user_id);

        return $record !== null && $record->isPresent();
    }

    public function examCheckIn(Tes $tes, Peserta $peserta, array $payload, ?string $ip = null): array
    {
        $session = $this->examSessionFor($tes, true);

        if (! $session) {
            return $this->reject(null, (int) $peserta->user_id, AttendanceLocationCheck::INVALID_LOCATION, 'Sesi absensi ujian tidak tersedia.', $payload, $ip);
        }

        return $this->processCheckIn(
            $session,
            $peserta,
            $payload,
            $ip,
            AttendanceSession::TYPE_EXAM,
            $session->location_id ?? $tes->tes_absensi_location_id
        );
    }

    public function dailyCheckIn(Peserta $peserta, array $payload, ?string $ip = null): array
    {
        $school = $this->schoolForPeserta($peserta);

        if (! $school) {
            return [
                'ok' => false,
                'message' => 'Sekolah peserta belum ditetapkan. Hubungi petugas sekolah.',
                'record' => null,
                'check' => null,
            ];
        }

        $session = $this->dailySessionFor($peserta, now(), true);
        $policy = $this->policyFor($school, AttendancePolicy::TYPE_DAILY, $this->academicYearForPeserta($peserta));

        if (! $session) {
            return $this->reject(null, (int) $peserta->user_id, AttendanceLocationCheck::INVALID_LOCATION, 'Sesi absensi harian tidak tersedia.', $payload, $ip);
        }

        return $this->processCheckIn(
            $session,
            $peserta,
            $payload,
            $ip,
            AttendanceSession::TYPE_DAILY,
            $session->location_id,
            $policy
        );
    }

    public function dailyCheckOut(Peserta $peserta, ?string $ip = null): array
    {
        $session = $this->dailySessionFor($peserta, now(), false);

        if (! $session) {
            return [
                'ok' => false,
                'message' => 'Belum ada absensi hari ini. Lakukan check-in terlebih dahulu.',
                'record' => null,
                'check' => null,
            ];
        }

        $record = $this->recordFor($session, (int) $peserta->user_id);

        if (! $record || ! $record->isPresent()) {
            return [
                'ok' => false,
                'message' => 'Belum ada check-in yang valid hari ini.',
                'record' => $record,
                'check' => null,
            ];
        }

        if ($record->check_out_at) {
            return [
                'ok' => true,
                'message' => 'Check-out sudah tercatat sebelumnya.',
                'record' => $record,
                'check' => null,
            ];
        }

        $record->update(['check_out_at' => now()]);

        AttendanceAuditEvent::record(
            'daily_check_out',
            $session,
            (int) $peserta->user_id,
            $record,
            'peserta',
            (int) $peserta->user_id,
            [],
            $ip
        );

        return [
            'ok' => true,
            'message' => 'Check-out absensi harian berhasil dicatat.',
            'record' => $record->fresh(),
            'check' => null,
        ];
    }

    public function override(
        AttendanceSession $session,
        int $studentId,
        string $status,
        string $reason,
        ?int $actorId = null,
        ?string $ip = null,
        ?string $method = AttendanceRecord::METHOD_MANUAL
    ): AttendanceRecord {
        return DB::transaction(function () use ($session, $studentId, $status, $reason, $actorId, $ip, $method) {
            $record = $this->recordFor($session, $studentId);
            $oldStatus = $record?->status;
            $oldMethod = $record?->method;
            $oldCheckIn = $record?->check_in_at;

            $data = [
                'foundation_id' => $session->foundation_id,
                'school_id' => $session->school_id,
                'status' => $status,
                'method' => $method ?: AttendanceRecord::METHOD_MANUAL,
                'is_override' => true,
                'notes' => $reason,
                'actor_id' => $actorId,
            ];

            if (in_array($status, [AttendanceRecord::HADIR, AttendanceRecord::TERLAMBAT], true)) {
                $data['check_in_at'] = $record?->check_in_at ?? now();
            }

            if ($record) {
                $record->update($data);
            } else {
                $record = AttendanceRecord::create($data + [
                    'session_id' => $session->id,
                    'student_id' => $studentId,
                ]);
            }

            AttendanceCorrection::create([
                'record_id' => $record->id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'old_method' => $oldMethod,
                'new_method' => $data['method'],
                'old_check_in_at' => $oldCheckIn,
                'new_check_in_at' => $record->check_in_at,
                'reason' => $reason,
                'requested_by' => $actorId,
                'approved_by' => $actorId,
                'approved_at' => now(),
            ]);

            AttendanceAuditEvent::record(
                'attendance_override',
                $session,
                $studentId,
                $record,
                'admin',
                $actorId,
                [
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'reason' => $reason,
                ],
                $ip
            );

            return $record->fresh();
        });
    }

    private function processCheckIn(
        AttendanceSession $session,
        Peserta $peserta,
        array $payload,
        ?string $ip,
        string $type,
        ?int $locationId,
        ?AttendancePolicy $policy = null
    ): array {
        $studentId = (int) $peserta->user_id;

        $latitude = isset($payload['latitude']) && is_numeric($payload['latitude']) ? (float) $payload['latitude'] : null;
        $longitude = isset($payload['longitude']) && is_numeric($payload['longitude']) ? (float) $payload['longitude'] : null;
        $accuracy = isset($payload['accuracy_meters']) && is_numeric($payload['accuracy_meters'])
            ? (float) $payload['accuracy_meters']
            : (isset($payload['accuracy']) && is_numeric($payload['accuracy']) ? (float) $payload['accuracy'] : null);

        if ($latitude === null || $longitude === null || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return $this->reject($session, $studentId, AttendanceLocationCheck::INVALID_PAYLOAD, 'Koordinat lokasi tidak valid.', $payload, $ip);
        }

        $now = now();

        if ($session->starts_at && $now->lt($session->starts_at)) {
            return $this->reject($session, $studentId, AttendanceLocationCheck::OUTSIDE_WINDOW, 'Check-in belum dibuka.', $payload, $ip, $latitude, $longitude, $accuracy);
        }

        if ($session->ends_at && $now->gt($session->ends_at)) {
            return $this->reject($session, $studentId, AttendanceLocationCheck::OUTSIDE_WINDOW, 'Check-in sudah ditutup.', $payload, $ip, $latitude, $longitude, $accuracy);
        }

        $school = School::find($session->school_id);
        $requireLocation = $policy?->require_location ?? true;

        $location = $school
            ? $this->locationForSchool($locationId, $school, $now)
            : null;

        if (! $location && $requireLocation) {
            return $this->reject($session, $studentId, AttendanceLocationCheck::INVALID_LOCATION, 'Lokasi presensi sekolah belum diatur atau tidak aktif.', $payload, $ip, $latitude, $longitude, $accuracy);
        }

        $distance = null;
        $maxAccuracy = (int) ($policy?->max_accuracy_meters ?: $location?->max_accuracy_meters ?: self::maxAccuracyDefault());

        if ($location) {
            $distance = $this->distanceMeters($latitude, $longitude, $location->latitude, $location->longitude);

            if ($accuracy !== null && $maxAccuracy > 0 && $accuracy > $maxAccuracy) {
                return $this->reject(
                    $session,
                    $studentId,
                    AttendanceLocationCheck::POOR_ACCURACY,
                    'Akurasi GPS terlalu rendah ('.$accuracy.' m). Silakan ambil lokasi ulang.',
                    $payload,
                    $ip,
                    $latitude,
                    $longitude,
                    $accuracy,
                    $location,
                    $distance
                );
            }

            if ($distance !== null && $distance > $location->radius_meters) {
                return $this->reject(
                    $session,
                    $studentId,
                    AttendanceLocationCheck::OUT_OF_RADIUS,
                    'Lokasi Anda di luar radius presensi ('.$location->radius_meters.' m). Perkiraan jarak '.round($distance).' m.',
                    $payload,
                    $ip,
                    $latitude,
                    $longitude,
                    $accuracy,
                    $location,
                    $distance
                );
            }
        }

        return DB::transaction(function () use ($session, $studentId, $now, $type, $location, $distance, $accuracy, $latitude, $longitude, $ip, $policy) {
            $record = $this->recordFor($session, $studentId);

            if ($record && $record->is_override) {
                return [
                    'ok' => false,
                    'message' => 'Absensi Anda sudah ditetapkan secara manual oleh petugas. Hubungi petugas jika perlu koreksi.',
                    'record' => $record,
                    'check' => null,
                ];
            }

            if ($record && $record->isPresent()) {
                $check = AttendanceLocationCheck::create([
                    'session_id' => $session->id,
                    'student_id' => $studentId,
                    'record_id' => $record->id,
                    'foundation_id' => $session->foundation_id,
                    'school_id' => $session->school_id,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'accuracy_meters' => $accuracy,
                    'distance_meters' => $distance,
                    'location_id' => $location?->id,
                    'validation_status' => AttendanceLocationCheck::VALID,
                    'reason' => 'Check-in berulang (idempotent).',
                    'source' => 'gps',
                    'ip_address' => $ip,
                    'checked_at' => $now,
                ]);

                AttendanceAuditEvent::record(
                    $type === AttendanceSession::TYPE_EXAM ? 'exam_check_in_duplicate' : 'daily_check_in_duplicate',
                    $session,
                    $studentId,
                    $record,
                    'peserta',
                    $studentId,
                    ['distance_meters' => $distance],
                    $ip
                );

                return [
                    'ok' => true,
                    'message' => 'Check-in sudah tercatat sebelumnya.',
                    'record' => $record,
                    'check' => $check,
                ];
            }

            if ($record && in_array($record->status, [AttendanceRecord::IZIN, AttendanceRecord::SAKIT, AttendanceRecord::DISPENSASI], true)) {
                return [
                    'ok' => false,
                    'message' => 'Status absensi Anda sudah diisi ('.$record->status_label.'). Hubungi petugas untuk perubahan.',
                    'record' => $record,
                    'check' => null,
                ];
            }

            $status = AttendanceRecord::HADIR;
            $lateTolerance = (int) ($policy?->late_tolerance_minutes ?? 0);

            if ($session->starts_at && $now->gt($session->starts_at->copy()->addMinutes($lateTolerance))) {
                $status = AttendanceRecord::TERLAMBAT;
            }

            $data = [
                'foundation_id' => $session->foundation_id,
                'school_id' => $session->school_id,
                'status' => $status,
                'method' => AttendanceRecord::METHOD_GPS,
                'check_in_at' => $now,
                'location_id' => $location?->id,
                'distance_meters' => $distance,
                'accuracy_meters' => $accuracy,
                'is_override' => false,
                'actor_id' => null,
            ];

            if ($record) {
                $record->update($data);
            } else {
                $record = AttendanceRecord::create($data + [
                    'session_id' => $session->id,
                    'student_id' => $studentId,
                ]);
            }

            $check = AttendanceLocationCheck::create([
                'session_id' => $session->id,
                'student_id' => $studentId,
                'record_id' => $record->id,
                'foundation_id' => $session->foundation_id,
                'school_id' => $session->school_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy_meters' => $accuracy,
                'distance_meters' => $distance,
                'location_id' => $location?->id,
                'validation_status' => AttendanceLocationCheck::VALID,
                'reason' => null,
                'source' => 'gps',
                'ip_address' => $ip,
                'checked_at' => $now,
            ]);

            AttendanceAuditEvent::record(
                $type === AttendanceSession::TYPE_EXAM ? 'exam_check_in' : 'daily_check_in',
                $session,
                $studentId,
                $record,
                'peserta',
                $studentId,
                [
                    'distance_meters' => $distance,
                    'accuracy_meters' => $accuracy,
                    'location_id' => $location?->id,
                    'status' => $status,
                ],
                $ip
            );

            return [
                'ok' => true,
                'message' => $type === AttendanceSession::TYPE_EXAM
                    ? 'Check-in ujian berhasil. Anda sekarang dapat memulai ujian.'
                    : 'Check-in absensi harian berhasil.',
                'record' => $record->fresh(),
                'check' => $check,
            ];
        });
    }

    private function reject(
        ?AttendanceSession $session,
        int $studentId,
        string $status,
        string $reason,
        array $payload,
        ?string $ip = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?float $accuracy = null,
        ?AttendanceLocation $location = null,
        ?float $distance = null
    ): array {
        $check = null;

        if ($session) {
            $check = AttendanceLocationCheck::create([
                'session_id' => $session->id,
                'student_id' => $studentId,
                'record_id' => null,
                'foundation_id' => $session->foundation_id,
                'school_id' => $session->school_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy_meters' => $accuracy,
                'distance_meters' => $distance,
                'location_id' => $location?->id,
                'validation_status' => $status,
                'reason' => $reason,
                'source' => 'gps',
                'ip_address' => $ip,
                'checked_at' => now(),
            ]);

            AttendanceAuditEvent::record(
                'attendance_check_in_rejected',
                $session,
                $studentId,
                null,
                'peserta',
                $studentId,
                [
                    'validation_status' => $status,
                    'reason' => $reason,
                    'distance_meters' => $distance,
                    'accuracy_meters' => $accuracy,
                ],
                $ip
            );
        }

        return [
            'ok' => false,
            'message' => $reason,
            'record' => null,
            'check' => $check,
        ];
    }
}
