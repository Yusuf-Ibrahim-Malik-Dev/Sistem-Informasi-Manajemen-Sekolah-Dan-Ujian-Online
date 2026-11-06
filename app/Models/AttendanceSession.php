<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    public const TYPE_DAILY = 'daily';

    public const TYPE_EXAM = 'exam';

    protected $table = 'att_attendance_sessions';

    protected $guarded = [];

    protected $casts = [
        'session_date' => 'date',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'requires_checkin' => 'boolean',
        'allow_manual' => 'boolean',
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

    public function exam()
    {
        return $this->belongsTo(Tes::class, 'exam_id', 'tes_id');
    }

    public function location()
    {
        return $this->belongsTo(AttendanceLocation::class, 'location_id');
    }

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class, 'session_id');
    }

    public function checks()
    {
        return $this->hasMany(AttendanceLocationCheck::class, 'session_id');
    }

    public function isExam(): bool
    {
        return $this->session_type === self::TYPE_EXAM;
    }
}
