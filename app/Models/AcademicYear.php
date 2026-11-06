<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $table = 'core_academic_years';

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function foundation()
    {
        return $this->belongsTo(Foundation::class, 'foundation_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class, 'academic_year_id');
    }

    public function getNamaAttribute(): string
    {
        return $this->year_name;
    }
}
