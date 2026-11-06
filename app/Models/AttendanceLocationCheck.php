<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLocationCheck extends Model
{
    public const VALID = 'valid';

    public const OUT_OF_RADIUS = 'out_of_radius';

    public const POOR_ACCURACY = 'poor_accuracy';

    public const INVALID_LOCATION = 'invalid_location';

    public const OUTSIDE_WINDOW = 'outside_window';

    public const INVALID_PAYLOAD = 'invalid_payload';

    protected $table = 'att_attendance_location_checks';

    protected $guarded = [];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy_meters' => 'float',
        'distance_meters' => 'float',
        'checked_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    public function student()
    {
        return $this->belongsTo(Peserta::class, 'student_id', 'user_id');
    }

    public function record()
    {
        return $this->belongsTo(AttendanceRecord::class, 'record_id');
    }

    public function location()
    {
        return $this->belongsTo(AttendanceLocation::class, 'location_id');
    }
}
