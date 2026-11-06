<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    protected $table = 'att_attendance_corrections';

    protected $guarded = [];

    protected $casts = [
        'old_check_in_at' => 'datetime',
        'new_check_in_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function record()
    {
        return $this->belongsTo(AttendanceRecord::class, 'record_id');
    }

    public function requester()
    {
        return $this->belongsTo(Admin::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}
