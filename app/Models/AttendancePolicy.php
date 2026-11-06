<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendancePolicy extends Model
{
    public const TYPE_DAILY = 'daily';

    public const TYPE_EXAM = 'exam';

    protected $table = 'att_attendance_policies';

    protected $guarded = [];

    protected $casts = [
        'require_location' => 'boolean',
        'allow_manual' => 'boolean',
        'require_checkin' => 'boolean',
        'require_checkout' => 'boolean',
        'late_tolerance_minutes' => 'integer',
        'max_accuracy_meters' => 'integer',
        'is_required' => 'boolean',
    ];

    public function foundation()
    {
        return $this->belongsTo(Foundation::class, 'foundation_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
