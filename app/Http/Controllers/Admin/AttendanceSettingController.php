<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Models\AttendancePolicy;
use App\Models\School;
use App\Support\AttendanceService;
use App\Support\GeocoderFactory;
use App\Support\SchoolContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceSettingController extends Controller
{
    public function index(Request $request, SchoolContext $context, AttendanceService $service)
    {
        $school = $this->resolveSchool($request, $context);

        $locations = AttendanceLocation::withCount('sessions')
            ->where('school_id', $school->id)
            ->orderBy('name')
            ->get();

        $policy = AttendancePolicy::firstOrNew(
            [
                'school_id' => $school->id,
                'policy_type' => AttendancePolicy::TYPE_DAILY,
                'academic_year_id' => null,
            ],
            [
                'foundation_id' => $school->foundation_id,
                'require_location' => true,
                'allow_manual' => true,
                'require_checkin' => true,
                'require_checkout' => false,
                'late_tolerance_minutes' => 0,
                'max_accuracy_meters' => AttendanceService::maxAccuracyDefault(),
                'is_required' => false,
            ]
        );

        return view('admin.absensi.lokasi', [
            'school' => $school,
            'schools' => $context->availableSchools(),
            'locations' => $locations,
            'policy' => $policy,
            'academicYears' => $school->academicYears()->orderByDesc('start_date')->get(),
            'defaultRadius' => AttendanceService::radiusDefault(),
            'defaultAccuracy' => AttendanceService::maxAccuracyDefault(),
        ]);
    }

    public function storeLocation(Request $request, SchoolContext $context)
    {
        $school = $this->resolveSchool($request, $context);
        $data = $this->validateLocation($request);

        AttendanceLocation::create($data + [
            'foundation_id' => $school->foundation_id,
            'school_id' => $school->id,
            'created_by' => (int) auth('web')->id(),
        ]);

        return back()->with('success', 'Titik lokasi presensi berhasil ditambahkan.');
    }

    public function updateLocation(Request $request, AttendanceLocation $location, SchoolContext $context)
    {
        $this->authorizeSchool($location->school_id, $context);
        $location->update($this->validateLocation($request));

        return back()->with('success', 'Titik lokasi presensi berhasil diperbarui.');
    }

    public function destroyLocation(AttendanceLocation $location, SchoolContext $context)
    {
        $this->authorizeSchool($location->school_id, $context);

        if ($location->sessions()->exists()) {
            return back()->with('error', 'Lokasi tidak dapat dihapus karena sudah dipakai pada sesi presensi.');
        }

        $location->delete();

        return back()->with('success', 'Titik lokasi presensi berhasil dihapus.');
    }

    public function updatePolicy(Request $request, SchoolContext $context)
    {
        $school = $this->resolveSchool($request, $context);

        $data = $request->validate([
            'academic_year_id' => [
                'nullable',
                'integer',
                Rule::exists('core_academic_years', 'id')->where(fn ($q) => $q->where('school_id', $school->id)),
            ],
            'require_location' => ['nullable', 'boolean'],
            'allow_manual' => ['nullable', 'boolean'],
            'require_checkin' => ['nullable', 'boolean'],
            'require_checkout' => ['nullable', 'boolean'],
            'service_start' => ['nullable', 'date_format:H:i'],
            'service_end' => ['nullable', 'date_format:H:i', 'after:service_start'],
            'late_tolerance_minutes' => ['nullable', 'integer', 'min:0', 'max:720'],
            'max_accuracy_meters' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'is_required' => ['nullable', 'boolean'],
        ]);

        AttendancePolicy::updateOrCreate(
            [
                'school_id' => $school->id,
                'policy_type' => AttendancePolicy::TYPE_DAILY,
                'academic_year_id' => $data['academic_year_id'] ?? null,
            ],
            [
                'foundation_id' => $school->foundation_id,
                'require_location' => $request->boolean('require_location'),
                'allow_manual' => $request->boolean('allow_manual'),
                'require_checkin' => $request->boolean('require_checkin'),
                'require_checkout' => $request->boolean('require_checkout'),
                'service_start' => $data['service_start'] ?? null,
                'service_end' => $data['service_end'] ?? null,
                'late_tolerance_minutes' => (int) ($data['late_tolerance_minutes'] ?? 0),
                'max_accuracy_meters' => (int) ($data['max_accuracy_meters'] ?? AttendanceService::maxAccuracyDefault()),
                'is_required' => $request->boolean('is_required'),
            ]
        );

        return back()->with('success', 'Kebijakan absensi harian berhasil disimpan.');
    }

    public function geocode(Request $request, SchoolContext $context)
    {
        $school = $this->resolveSchool($request, $context);
        $this->authorizeSchool($school->id, $context);

        $query = trim((string) $request->input('q', ''));
        if ($query === '') {
            return response()->json(['results' => []]);
        }

        $geocoder = GeocoderFactory::forAdmin($request);

        $results = $geocoder->search($query, (int) $request->input('limit', 8));

        return response()->json(['results' => $results]);
    }

    private function validateLocation(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:1', 'max:100000'],
            'max_accuracy_meters' => ['required', 'integer', 'min:1', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function resolveSchool(Request $request, SchoolContext $context): School
    {
        $schoolId = $context->schoolId() ?? (int) $request->input('school_id');
        $school = $schoolId > 0
            ? $context->availableSchools()->firstWhere('id', $schoolId)
            : $context->defaultSchool();

        abort_unless($school, 422, 'Pilih sekolah yang valid terlebih dahulu.');

        return $school;
    }

    private function authorizeSchool(int $schoolId, SchoolContext $context): void
    {
        abort_unless($context->availableSchools()->contains('id', $schoolId), 403);
    }
}
