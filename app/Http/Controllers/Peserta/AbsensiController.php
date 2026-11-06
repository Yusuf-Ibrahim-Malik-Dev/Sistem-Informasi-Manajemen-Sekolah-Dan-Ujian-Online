<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\AttendancePolicy;
use App\Models\AttendanceRecord;
use App\Models\Peserta;
use App\Models\Tes;
use App\Support\AttendanceService;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index(AttendanceService $service)
    {
        $peserta = auth('peserta')->user();
        $school = $service->schoolForPeserta($peserta);

        $session = $service->dailySessionFor($peserta, now(), false);
        $today = $session
            ? $service->recordFor($session, (int) $peserta->user_id)
            : null;

        $history = AttendanceRecord::with(['session.location'])
            ->where('student_id', $peserta->user_id)
            ->whereHas('session', fn ($q) => $q->where('session_type', 'daily'))
            ->orderByDesc('check_in_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $location = $school
            ? $service->locationForSchool($session?->location_id, $school, now())
            : null;

        $policy = $school
            ? $service->policyFor($school, AttendancePolicy::TYPE_DAILY, $service->academicYearForPeserta($peserta))
            : null;

        return view('peserta.absensi', compact('peserta', 'school', 'session', 'today', 'history', 'location', 'policy'));
    }

    public function checkIn(Request $request, AttendanceService $service)
    {
        $peserta = auth('peserta')->user();
        $data = $this->validatedPayload($request);

        $result = $service->dailyCheckIn($peserta, $data, $request->ip());

        return $this->respond($request, $result, route('peserta.absensi.index'));
    }

    public function checkOut(Request $request, AttendanceService $service)
    {
        $peserta = auth('peserta')->user();
        $result = $service->dailyCheckOut($peserta, $request->ip());

        return $this->respond($request, $result, route('peserta.absensi.index'));
    }

    public function examCheckIn(Request $request, Tes $tes, AttendanceService $service)
    {
        $peserta = auth('peserta')->user();
        $this->pastikanAkses($tes, $peserta);

        if (! $service->requiresExamAttendance($tes)) {
            return $this->respond($request, [
                'ok' => false,
                'message' => 'Ujian ini tidak mewajibkan absensi pra-ujian.',
                'record' => null,
                'check' => null,
            ], route('peserta.tes.show', $tes));
        }

        $data = $this->validatedPayload($request);
        $result = $service->examCheckIn($tes, $peserta, $data, $request->ip());

        return $this->respond($request, $result, route('peserta.tes.show', $tes));
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ]);
    }

    private function respond(Request $request, array $result, string $fallback)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => $result['ok'],
                'message' => $result['message'],
                'record' => $result['record'] ? [
                    'status' => $result['record']->status,
                    'status_label' => $result['record']->status_label,
                    'method' => $result['record']->method,
                    'check_in_at' => $result['record']->check_in_at?->toIso8601String(),
                    'distance_meters' => $result['record']->distance_meters,
                    'accuracy_meters' => $result['record']->accuracy_meters,
                ] : null,
            ], $result['ok'] ? 200 : 422);
        }

        if (! $result['ok']) {
            return back()->with('error', $result['message'])->withInput();
        }

        return redirect($fallback)->with('success', $result['message']);
    }

    private function pastikanAkses(Tes $tes, Peserta $peserta): void
    {
        $punyaAkses = $tes->groups()->where('grup_id', $peserta->user_grup_id)->exists();

        abort_unless($punyaAkses, 403, 'Anda tidak terdaftar pada tes ini.');
    }
}
