<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    public const MENUNGGU = 'menunggu';

    public const DIVERIFIKASI = 'diverifikasi';

    public const DITOLAK = 'ditolak';

    protected $table = 'cbt_pembayaran';

    protected $primaryKey = 'pembayaran_id';

    protected $guarded = [];

    protected $casts = [
        'pembayaran_nominal' => 'float',
        'pembayaran_verified_at' => 'datetime',
    ];

    public static function statusOptions(): array
    {
        return [
            self::MENUNGGU => 'Menunggu Verifikasi',
            self::DIVERIFIKASI => 'Diverifikasi',
            self::DITOLAK => 'Ditolak',
        ];
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'pembayaran_user_id', 'user_id');
    }

    public function tes()
    {
        return $this->belongsTo(Tes::class, 'pembayaran_tes_id', 'tes_id');
    }

    public function orangTua()
    {
        return $this->belongsTo(OrangTua::class, 'pembayaran_ortu_id', 'ortu_id');
    }

    public function verifikator()
    {
        return $this->belongsTo(Admin::class, 'pembayaran_verified_by', 'id');
    }

    public function foundation()
    {
        return $this->belongsTo(Foundation::class, 'pembayaran_foundation_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'pembayaran_school_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->pembayaran_status] ?? ucfirst((string) $this->pembayaran_status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->pembayaran_status) {
            self::DIVERIFIKASI => 'success',
            self::DITOLAK => 'danger',
            default => 'warning',
        };
    }
}
