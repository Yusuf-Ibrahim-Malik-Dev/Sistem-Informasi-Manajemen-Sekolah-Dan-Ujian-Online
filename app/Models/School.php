<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class School extends Model
{
    protected $table = 'core_schools';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $school) {
            $school->uuid ??= (string) Str::uuid();
        });
    }

    public function foundation()
    {
        return $this->belongsTo(Foundation::class, 'foundation_id');
    }

    public function level()
    {
        return $this->belongsTo(SchoolLevel::class, 'school_level_id');
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class, 'school_id');
    }

    public function activeAcademicYear()
    {
        return $this->hasOne(AcademicYear::class, 'school_id')->where('is_active', true);
    }

    public function groups()
    {
        return $this->hasMany(UserGroup::class, 'grup_school_id', 'id');
    }

    public function students()
    {
        return $this->hasMany(Peserta::class, 'user_school_id', 'id');
    }

    public function tests()
    {
        return $this->hasMany(Tes::class, 'tes_school_id', 'id');
    }

    public function attendanceLocations()
    {
        return $this->hasMany(AttendanceLocation::class, 'school_id', 'id');
    }

    public function attendancePolicies()
    {
        return $this->hasMany(AttendancePolicy::class, 'school_id', 'id');
    }

    public function getNamaAttribute(): string
    {
        return $this->school_name;
    }

    public function getLevelCodeAttribute(): string
    {
        return (string) ($this->level?->level_code ?? '');
    }

    public function getLabelAttribute(): string
    {
        return $this->school_name.' ('.($this->level?->level_code ?? '-').')';
    }
}
