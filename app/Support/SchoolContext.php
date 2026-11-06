<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Foundation;
use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SchoolContext
{
    private ?Admin $admin = null;

    private ?School $school = null;

    private ?int $foundationId = null;

    private ?Collection $schools = null;

    private ?Collection $foundations = null;

    public function resolve(?Admin $admin): void
    {
        $this->admin = $admin;
        $this->school = null;
        $this->foundationId = null;
        $this->schools = null;
        $this->foundations = null;

        if (! $admin) {
            return;
        }

        $schools = $this->availableSchools();
        $sessionSchoolId = (int) session('active_school_id', 0);

        if ($admin->isSchoolScope()) {
            $this->school = $schools->firstWhere('id', (int) $admin->school_id);
        } elseif ($sessionSchoolId > 0) {
            $this->school = $schools->firstWhere('id', $sessionSchoolId);
        }

        if ($admin->isFoundationScope()) {
            $this->foundationId = (int) $admin->foundation_id;
        } elseif ($this->school) {
            $this->foundationId = (int) $this->school->foundation_id;
        }
    }

    public function admin(): ?Admin
    {
        return $this->admin;
    }

    public function school(): ?School
    {
        return $this->school;
    }

    public function schoolId(): ?int
    {
        return $this->school?->id;
    }

    public function foundationId(): ?int
    {
        return $this->foundationId;
    }

    public function foundation(): ?Foundation
    {
        if (! $this->foundationId) {
            return null;
        }

        return $this->availableFoundations()->firstWhere('id', $this->foundationId);
    }

    public function isAllSchools(): bool
    {
        return $this->school === null;
    }

    public function availableSchools(): Collection
    {
        if ($this->schools === null) {
            $this->schools = $this->admin
                ? $this->admin->accessibleSchools()->with('level')->orderBy('school_name')->get()
                : collect();
        }

        return $this->schools;
    }

    public function availableFoundations(): Collection
    {
        if ($this->foundations === null) {
            $this->foundations = $this->admin
                ? $this->admin->accessibleFoundations()->orderBy('foundation_name')->get()
                : collect();
        }

        return $this->foundations;
    }

    public function canAccessSchool(int $schoolId): bool
    {
        return $this->availableSchools()->contains('id', $schoolId);
    }

    public function switchTo(?int $schoolId): bool
    {
        if ($schoolId === null || $schoolId === 0) {
            if ($this->admin?->isSchoolScope()) {
                return false;
            }

            session()->forget('active_school_id');
            $this->resolve($this->admin);

            return true;
        }

        if (! $this->canAccessSchool($schoolId)) {
            return false;
        }

        session(['active_school_id' => $schoolId]);
        $this->resolve($this->admin);

        return true;
    }

    public function apply(Builder $query, string $schoolColumn, string $foundationColumn): Builder
    {
        if ($this->schoolId()) {
            return $query->where($schoolColumn, $this->schoolId());
        }

        if ($this->foundationId()) {
            return $query->where($foundationColumn, $this->foundationId());
        }

        return $query;
    }

    public function applyForSchool(Builder $query, string $schoolColumn): Builder
    {
        if ($this->schoolId()) {
            return $query->where($schoolColumn, $this->schoolId());
        }

        return $query;
    }

    public function defaultSchool(): ?School
    {
        return $this->school ?? $this->availableSchools()->first();
    }

    public function defaultAcademicYear(): ?int
    {
        $school = $this->defaultSchool();

        if (! $school) {
            return null;
        }

        return $school->academicYears()->where('is_active', true)->value('id')
            ?? $school->academicYears()->orderByDesc('start_date')->value('id');
    }
}
