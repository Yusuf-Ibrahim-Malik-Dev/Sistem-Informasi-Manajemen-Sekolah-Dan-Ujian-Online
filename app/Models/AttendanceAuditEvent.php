<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceAuditEvent extends Model
{
    protected $table = 'att_attendance_audit_events';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public static function record(
        string $eventType,
        ?AttendanceSession $session = null,
        ?int $studentId = null,
        ?AttendanceRecord $record = null,
        ?string $actorType = null,
        ?int $actorId = null,
        array $metadata = [],
        ?string $ip = null
    ): self {
        return static::create([
            'event_type' => $eventType,
            'session_id' => $session?->id,
            'student_id' => $studentId,
            'record_id' => $record?->id,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'metadata' => $metadata,
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }
}
