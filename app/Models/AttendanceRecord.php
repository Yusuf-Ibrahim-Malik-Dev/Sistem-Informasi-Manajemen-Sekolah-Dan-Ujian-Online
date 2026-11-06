<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    public const HADIR = 'hadir';

    public const TERLAMBAT = 'terlambat';

    public const IZIN = 'izin';

    public const SAKIT = 'sakit';

    public const DISPENSASI = 'dispensasi';

    public const ALPHA = 'alpha';

    public const METHOD_GPS = 'gps';

    public const METHOD_MANUAL = 'manual';

    protected $table = 'att_attendance_records';

    protected $guarded = [];

    protected $casts = [
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'distance_meters' => 'float',
        'accuracy_meters' => 'float',
        'is_override' => 'boolean',
    ];

    public static function statusOptions(): array
    {
        return [
            self::HADIR => 'Hadir',
            self::TERLAMBAT => 'Terlambat',
            self::IZIN => 'Izin',
            self::SAKIT => 'Sakit',
            self::DISPENSASI => 'Dispensasi',
            self::ALPHA => 'Alpha',
        ];
    }

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    public function student()
    {
        return $this->belongsTo(Peserta::class, 'student_id', 'user_id');
    }

    public function location()
    {
        return $this->belongsTo(AttendanceLocation::class, 'location_id');
    }

    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class, 'record_id');
    }

    public function isPresent(): bool
    {
        return in_array($this->status, [self::HADIR, self::TERLAMBAT], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::HADIR => 'success',
            self::TERLAMBAT => 'warning',
            self::IZIN => 'info',
            self::SAKIT => 'warning',
            self::DISPENSASI => 'info',
            default => 'danger',
        };
    }

    public function getMethodLabelAttribute(): string
    {
        return $this->method === self::METHOD_GPS ? 'GPS' : 'Manual';
    }
}
