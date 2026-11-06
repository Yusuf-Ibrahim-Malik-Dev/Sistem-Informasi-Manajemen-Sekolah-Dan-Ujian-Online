<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLocation extends Model
{
    protected $table = 'att_attendance_locations';

    protected $guarded = [];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'radius_meters' => 'integer',
        'max_accuracy_meters' => 'integer',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function foundation()
    {
        return $this->belongsTo(Foundation::class, 'foundation_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function sessions()
    {
        return $this->hasMany(AttendanceSession::class, 'location_id');
    }

    public function isActiveOn(\DateTimeInterface $date): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $day = $date->format('Y-m-d');

        if ($this->valid_from && $this->valid_from->format('Y-m-d') > $day) {
            return false;
        }

        if ($this->valid_to && $this->valid_to->format('Y-m-d') < $day) {
            return false;
        }

        return true;
    }
}
