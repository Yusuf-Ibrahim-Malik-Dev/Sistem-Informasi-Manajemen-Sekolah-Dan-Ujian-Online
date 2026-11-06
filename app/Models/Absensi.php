<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    public const HADIR = 'hadir';

    public const IZIN = 'izin';

    public const SAKIT = 'sakit';

    public const ALPHA = 'alpha';

    public const SUMBER_UJIAN = 'ujian';

    public const SUMBER_MANUAL = 'manual';

    protected $table = 'cbt_absensi';

    protected $primaryKey = 'absensi_id';

    protected $guarded = [];

    protected $casts = [
        'absensi_tanggal' => 'date',
    ];

    public static function statusOptions(): array
    {
        return [
            self::HADIR => 'Hadir',
            self::IZIN => 'Izin',
            self::SAKIT => 'Sakit',
            self::ALPHA => 'Alpha',
        ];
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'absensi_user_id', 'user_id');
    }

    public function tes()
    {
        return $this->belongsTo(Tes::class, 'absensi_tes_id', 'tes_id');
    }

    public function foundation()
    {
        return $this->belongsTo(Foundation::class, 'absensi_foundation_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'absensi_school_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->absensi_status] ?? ucfirst((string) $this->absensi_status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->absensi_status) {
            self::HADIR => 'success',
            self::IZIN => 'info',
            self::SAKIT => 'warning',
            default => 'danger',
        };
    }
}
