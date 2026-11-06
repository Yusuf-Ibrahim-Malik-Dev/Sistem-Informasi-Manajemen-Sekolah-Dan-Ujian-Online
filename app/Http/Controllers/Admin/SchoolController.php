<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Peserta;
use App\Models\School;
use App\Models\Semester;
use App\Models\Tes;
use App\Models\UserGroup;
use App\Support\SchoolContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index(SchoolContext $context)
    {
        $schools = $context->availableSchools()
            ->loadCount(['groups', 'students', 'tests'])
            ->load(['foundation', 'level', 'academicYears' => fn ($query) => $query->orderByDesc('start_date')]);

        return view('admin.sekolah.index', [
            'schools' => $schools,
            'foundations' => $context->availableFoundations(),
            'levels' => \App\Models\SchoolLevel::orderBy('id')->get(),
            'academicYears' => AcademicYear::with('school')
                ->whereIn('school_id', $schools->pluck('id'))
                ->orderByDesc('start_date')
                ->get(),
        ]);
    }

    public function store(Request $request, SchoolContext $context)
    {
        $data = $request->validate([
            'foundation_id' => ['required', 'exists:core_foundations,id'],
            'school_level_id' => ['required', 'exists:core_school_levels,id'],
            'school_code' => ['required', 'string', 'max:30', 'alpha_dash'],
            'npsn' => ['nullable', 'string', 'max:20'],
            'school_name' => ['required', 'string', 'max:200'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        abort_unless($context->availableFoundations()->contains('id', (int) $data['foundation_id']), 403);

        $data['school_code'] = strtoupper($data['school_code']);
        $data['is_active'] = $request->boolean('is_active');

        $sudahAda = School::where('foundation_id', $data['foundation_id'])
            ->where('school_code', $data['school_code'])
            ->exists();

        if ($sudahAda) {
            return back()->withErrors(['school_code' => 'Kode sekolah sudah digunakan pada yayasan ini.'])->withInput();
        }

        School::create($data);

        return back()->with('success', 'Sekolah berhasil ditambahkan.');
    }

    public function update(Request $request, School $school, SchoolContext $context)
    {
        abort_unless($context->availableSchools()->contains('id', $school->id), 403);

        $data = $request->validate([
            'foundation_id' => ['required', 'exists:core_foundations,id'],
            'school_level_id' => ['required', 'exists:core_school_levels,id'],
            'school_code' => ['required', 'string', 'max:30', 'alpha_dash'],
            'npsn' => ['nullable', 'string', 'max:20'],
            'school_name' => ['required', 'string', 'max:200'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        abort_unless($context->availableFoundations()->contains('id', (int) $data['foundation_id']), 403);

        $data['school_code'] = strtoupper($data['school_code']);
        $data['is_active'] = $request->boolean('is_active');

        $sudahAda = School::where('foundation_id', $data['foundation_id'])
            ->where('school_code', $data['school_code'])
            ->where('id', '!=', $school->id)
            ->exists();

        if ($sudahAda) {
            return back()->withErrors(['school_code' => 'Kode sekolah sudah digunakan pada yayasan ini.'])->withInput();
        }

        $school->update($data);

        return back()->with('success', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(School $school, SchoolContext $context)
    {
        abort_unless($context->availableSchools()->contains('id', $school->id), 403);

        if (UserGroup::where('grup_school_id', $school->id)->exists()
            || Peserta::where('user_school_id', $school->id)->exists()
            || Tes::where('tes_school_id', $school->id)->exists()) {
            return back()->with('error', 'Sekolah tidak dapat dihapus karena masih memiliki data akademik.');
        }

        $school->delete();

        return back()->with('success', 'Sekolah berhasil dihapus.');
    }

    public function storeYear(Request $request, SchoolContext $context)
    {
        $data = $request->validate([
            'school_id' => ['required', 'exists:core_schools,id'],
            'year_name' => ['required', 'string', 'max:20'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        abort_unless($context->availableSchools()->contains('id', (int) $data['school_id']), 403);

        $sudahAda = AcademicYear::where('school_id', $data['school_id'])
            ->where('year_name', $data['year_name'])
            ->exists();

        if ($sudahAda) {
            return back()->withErrors(['year_name' => 'Tahun ajaran sudah ada pada sekolah ini.'])->withInput();
        }

        DB::transaction(function () use ($data, $request) {
            $school = School::findOrFail($data['school_id']);
            $aktif = $request->boolean('is_active');

            if ($aktif) {
                AcademicYear::where('school_id', $school->id)->update(['is_active' => false]);
            }

            $year = AcademicYear::create([
                'foundation_id' => $school->foundation_id,
                'school_id' => $school->id,
                'year_name' => $data['year_name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => $aktif,
            ]);

            $mulai = Carbon::parse($data['start_date']);
            $selesai = Carbon::parse($data['end_date']);
            $tengah = $mulai->copy()->addMonthsNoOverflow(6)->subDay();

            if ($tengah->greaterThanOrEqualTo($selesai)) {
                $tengah = $mulai->copy()->addDays(max(1, (int) ($mulai->diffInDays($selesai) / 2)));
            }

            Semester::create([
                'school_id' => $school->id,
                'academic_year_id' => $year->id,
                'semester_number' => 1,
                'start_date' => $mulai->toDateString(),
                'end_date' => $tengah->toDateString(),
                'is_active' => true,
            ]);

            Semester::create([
                'school_id' => $school->id,
                'academic_year_id' => $year->id,
                'semester_number' => 2,
                'start_date' => $tengah->copy()->addDay()->toDateString(),
                'end_date' => $selesai->toDateString(),
                'is_active' => false,
            ]);
        });

        return back()->with('success', 'Tahun ajaran dan semester berhasil ditambahkan.');
    }

    public function updateYear(Request $request, AcademicYear $academicYear, SchoolContext $context)
    {
        abort_unless($context->availableSchools()->contains('id', $academicYear->school_id), 403);

        $data = $request->validate([
            'year_name' => [
                'required', 'string', 'max:20',
                Rule::unique('core_academic_years', 'year_name')
                    ->where('school_id', $academicYear->school_id)
                    ->ignore($academicYear->id),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $request, $academicYear) {
            if ($request->boolean('is_active')) {
                AcademicYear::where('school_id', $academicYear->school_id)->update(['is_active' => false]);
            }

            $academicYear->update([
                'year_name' => $data['year_name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return back()->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroyYear(AcademicYear $academicYear, SchoolContext $context)
    {
        abort_unless($context->availableSchools()->contains('id', $academicYear->school_id), 403);

        $dipakai = UserGroup::where('grup_academic_year_id', $academicYear->id)->exists()
            || Tes::where('tes_academic_year_id', $academicYear->id)->exists();

        if ($dipakai) {
            return back()->with('error', 'Tahun ajaran tidak dapat dihapus karena masih digunakan kelas atau tes.');
        }

        $academicYear->delete();

        return back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function switch(Request $request, SchoolContext $context)
    {
        $data = $request->validate([
            'school_id' => ['nullable', 'integer'],
        ]);

        $schoolId = (int) ($data['school_id'] ?? 0);

        if (! $context->switchTo($schoolId)) {
            return back()->with('error', 'Anda tidak memiliki akses ke sekolah tersebut.');
        }

        return back()->with('success', $context->school()
            ? 'Konteks sekolah diubah ke ' . $context->school()->school_name . '.'
            : 'Menampilkan seluruh sekolah yang dapat diakses.');
    }
}
