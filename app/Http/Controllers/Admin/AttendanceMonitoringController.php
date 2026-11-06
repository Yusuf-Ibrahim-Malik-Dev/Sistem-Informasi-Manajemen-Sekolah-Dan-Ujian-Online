<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceAuditEvent;
use App\Models\AttendanceLocation;
use App\Models\AttendanceLocationCheck;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Peserta;
use App\Models\Tes;
use App\Models\TesUser;
use App\Support\AttendanceService;
use App\Support\SchoolContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceMonitoringController extends Controller
{
    public function index(Request $request, SchoolContext $context, AttendanceService $service)
    {
        $schoolIds = $this->schoolIds($context);
        $date = $this->date($request);
        $type = $request->input('type');

        $sessions = AttendanceSession::with(['exam', 'location', 'school'])
            ->withCount([
                'records',
                'records as hadir_count' => fn ($q) => $q->whereIn('status', [AttendanceRecord::HADIR, AttendanceRecord::TERLAMBAT]),
                'records as override_count' => fn ($q) => $q->where('is_override', true),
                'checks as rejected_count' => fn ($q) => $q->whereIn('validation_status', [
                    AttendanceLocationCheck::OUT_OF_RADIUS,
                    AttendanceLocationCheck::POOR_ACCURACY,
                    AttendanceLocationCheck::INVALID_LOCATION,
                    AttendanceLocationCheck::OUTSIDE_WINDOW,
                    AttendanceLocationCheck::INVALID_PAYLOAD,
                ]),
            ])
            ->whereIn('school_id', $schoolIds)
            ->whereDate('session_date', $date)
            ->when(in_array($type, [AttendanceSession::TYPE_DAILY, AttendanceSession::TYPE_EXAM], true), fn ($q) => $q->where('session_type', $type))
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->get();

        $exams = Tes::with('groups')
            ->whereDate('tes_begin_time', $date)
            ->where(function ($q) use ($schoolIds) {
                $q->whereIn('tes_school_id', $schoolIds)
                    ->orWhereHas('groups', fn ($g) => $g->whereIn('grup_school_id', $schoolIds));
            })
            ->orderBy('tes_begin_time')
            ->get();

        foreach ($sessions as $session) {
            $session->total_students = $this->totalStudents($session);
            $session->attendance_location = $session->location
                ?: ($session->exam?->tes_absensi_location_id
                    ? AttendanceLocation::find($session->exam->tes_absensi_location_id)
                    : null);
        }

        return view('admin.absensi.monitoring', compact('sessions', 'exams', 'date', 'type'));
    }

    public function show(AttendanceSession $session, SchoolContext $context)
    {
        $this->authorizeSchool((int) $session->school_id, $context);

        return view('admin.absensi.monitoring-show', $this->payload($session));
    }

    public function showTes(Tes $tes, SchoolContext $context, AttendanceService $service)
    {
        $school = $service->schoolForTes($tes);
        abort_unless($school && $context->availableSchools()->contains('id', $school->id), 403);

        $session = $service->examSessionFor($tes, false);

        return view('admin.absensi.monitoring-show', $this->payload($session, $tes, $school));
    }

    public function override(Request $request, SchoolContext $context, AttendanceService $service)
    {
        $data = $request->validate([
            'session_id' => ['nullable', 'integer', 'exists:att_attendance_sessions,id'],
            'tes_id' => ['nullable', 'integer', 'exists:cbt_tes,tes_id'],
            'student_id' => ['required', 'integer', 'exists:cbt_user,user_id'],
            'status' => ['required', 'in:'.implode(',', array_keys(AttendanceRecord::statusOptions()))],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        abort_if(empty($data['session_id']) && empty($data['tes_id']), 422, 'Sesi absensi tidak ditentukan.');

        if (! empty($data['session_id'])) {
            $session = AttendanceSession::findOrFail($data['session_id']);
        } else {
            $tes = Tes::findOrFail($data['tes_id']);
            $session = $service->examSessionFor($tes, true);
        }

        abort_unless($session, 422, 'Sesi absensi tidak tersedia.');
        $this->authorizeSchool((int) $session->school_id, $context);

        $student = Peserta::findOrFail($data['student_id']);
        abort_unless($this->studentInSession($session, $student), 422, 'Peserta tidak terdaftar pada sesi ini.');

        $record = $service->override(
            $session,
            (int) $student->user_id,
            $data['status'],
            $data['reason'],
            (int) auth('web')->id(),
            $request->ip(),
            AttendanceRecord::METHOD_MANUAL
        );

        return back()->with('success', 'Absensi '.$student->nama.' diperbarui menjadi '.$record->status_label.' dengan alasan tercatat.');
    }

    private function payload(?AttendanceSession $session, ?Tes $tes = null, $school = null): array
    {
        $tes ??= $session?->exam;

        if (! $session) {
            $students = $this->examStudents($tes);
            $records = collect();
            $checks = collect();
            $attempts = collect();
            $audits = collect();
        } else {
            $session->load(['exam', 'location', 'school', 'records', 'checks']);

            $students = $session->exam_id
                ? $this->examStudents($tes)
                : $this->dailyStudents($session);

            $records = $session->records->keyBy('student_id');
            $checks = $session->checks
                ->sortByDesc('checked_at')
                ->unique('student_id')
                ->keyBy('student_id');

            $attempts = $session->exam_id
                ? TesUser::where('tesuser_tes_id', $session->exam_id)
                    ->whereIn('tesuser_user_id', $students->pluck('user_id'))
                    ->get()
                    ->keyBy('tesuser_user_id')
                : collect();

            $audits = AttendanceAuditEvent::with('session')
                ->where('session_id', $session->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(30)
                ->get();
        }

        $rows = $students->map(function (Peserta $student) use ($records, $checks, $attempts) {
            $record = $records->get($student->user_id);
            $check = $checks->get($student->user_id);
            $attempt = $attempts->get($student->user_id);

            return [
                'student' => $student,
                'record' => $record,
                'check' => $check,
                'attempt' => $attempt,
                'attendance_state' => $this->attendanceState($record, $check),
                'exam_state' => $this->examState($attempt),
            ];
        })->values();

        $stats = [
            'total' => $rows->count(),
            'hadir' => $rows->where('attendance_state', 'hadir')->count(),
            'belum' => $rows->where('attendance_state', 'belum')->count(),
            'ditolak' => $rows->where('attendance_state', 'ditolak')->count(),
            'override' => $rows->where('attendance_state', 'override')->count(),
            'mengerjakan' => $rows->whereIn('exam_state', ['mengerjakan', 'terputus'])->count(),
            'selesai' => $rows->where('exam_state', 'selesai')->count(),
        ];

        return [
            'session' => $session,
            'tes' => $tes,
            'school' => $school ?? $session?->school,
            'rows' => $rows,
            'stats' => $stats,
            'audits' => $audits,
        ];
    }

    private function examStudents(?Tes $tes)
    {
        if (! $tes) {
            return collect();
        }

        $groupIds = $tes->groups()->pluck('grup_id');

        return Peserta::with('grup')
            ->whereIn('user_grup_id', $groupIds)
            ->orderBy('user_firstname')
            ->orderBy('user_name')
            ->get();
    }

    private function dailyStudents(AttendanceSession $session)
    {
        $recordIds = $session->records->pluck('student_id');

        return Peserta::with('grup')
            ->where(function ($q) use ($session, $recordIds) {
                $q->where('user_school_id', $session->school_id);

                if ($recordIds->isNotEmpty()) {
                    $q->orWhereIn('user_id', $recordIds);
                }
            })
            ->orderBy('user_firstname')
            ->orderBy('user_name')
            ->get();
    }

    private function attendanceState(?AttendanceRecord $record, ?AttendanceLocationCheck $check): string
    {
        if ($record) {
            if ($record->is_override) {
                return 'override';
            }

            if ($record->isPresent()) {
                return 'hadir';
            }

            return $record->status;
        }

        if ($check && $check->validation_status !== AttendanceLocationCheck::VALID) {
            return 'ditolak';
        }

        return 'belum';
    }

    private function examState(?TesUser $attempt): string
    {
        if (! $attempt) {
            return 'belum_mulai';
        }

        if ($attempt->is_berjalan && $attempt->is_kedaluwarsa) {
            return 'terputus';
        }

        if ($attempt->is_berjalan) {
            return 'mengerjakan';
        }

        if ((int) $attempt->tesuser_status === TesUser::STATUS_SELESAI) {
            return 'selesai';
        }

        return 'lainnya';
    }

    private function totalStudents(AttendanceSession $session): int
    {
        if ($session->exam_id) {
            return $this->examStudents($session->exam)->count();
        }

        return Peserta::where('user_school_id', $session->school_id)->count();
    }

    private function studentInSession(AttendanceSession $session, Peserta $student): bool
    {
        if ($session->exam_id) {
            return $session->exam
                ? $session->exam->groups()->where('grup_id', $student->user_grup_id)->exists()
                : false;
        }

        return (int) $student->user_school_id === (int) $session->school_id
            || $session->records()->where('student_id', $student->user_id)->exists();
    }

    private function schoolIds(SchoolContext $context): array
    {
        return $context->schoolId()
            ? [(int) $context->schoolId()]
            : $context->availableSchools()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function date(Request $request): string
    {
        return $request->filled('date')
            ? Carbon::parse($request->input('date'))->toDateString()
            : now()->toDateString();
    }

    private function authorizeSchool(int $schoolId, SchoolContext $context): void
    {
        abort_unless($context->availableSchools()->contains('id', $schoolId), 403);
    }
}
