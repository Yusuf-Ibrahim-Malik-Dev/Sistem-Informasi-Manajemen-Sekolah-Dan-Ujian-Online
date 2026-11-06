<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TesUser extends Model
{
    protected $table = 'cbt_tes_user';
    protected $primaryKey = 'tesuser_id';
    public $timestamps = false;
    protected $guarded = [];

    public const STATUS_BERJALAN = 1;
    public const STATUS_SELESAI = 4;

    protected $casts = [
        'tesuser_creation_time' => 'datetime',
    ];

    public function tes()
    {
        return $this->belongsTo(Tes::class, 'tesuser_tes_id', 'tes_id');
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'tesuser_user_id', 'user_id');
    }

    public function soals()
    {
        return $this->hasMany(TesSoal::class, 'tessoal_tesuser_id', 'tesuser_id')->orderBy('tessoal_order');
    }

    public function getBatasWaktuAttribute(): Carbon
    {
        return $this->tesuser_creation_time->copy()->addMinutes((int) ($this->tes->tes_duration_time ?? 0));
    }

    public function getSisaDetikAttribute(): int
    {
        return max(0, now()->diffInSeconds($this->batas_waktu, false));
    }

    public function getIsBerjalanAttribute(): bool
    {
        return (int) $this->tesuser_status === self::STATUS_BERJALAN;
    }

    public function getIsKedaluwarsaAttribute(): bool
    {
        return $this->is_berjalan && now()->gte($this->batas_waktu);
    }

    public function getNilaiAttribute(): float
    {
        return (float) $this->soals()->sum('tessoal_nilai');
    }

    public function getJumlahDijawabAttribute(): int
    {
        return $this->soals()->whereNotNull('tessoal_change_time')->count();
    }

    public function getJumlahSoalAttribute(): int
    {
        return $this->soals()->count();
    }

    public function getJumlahRaguAttribute(): int
    {
        return $this->soals()->where('tessoal_ragu', 1)->count();
    }

    public function finalisasi(): void
    {
        if (! $this->is_berjalan) {
            return;
        }

        $nilaiKosong = (float) ($this->tes?->tes_score_unanswered ?? 0);

        $this->soals()
            ->whereNull('tessoal_change_time')
            ->update(['tessoal_nilai' => $nilaiKosong]);

        $this->update(['tesuser_status' => self::STATUS_SELESAI]);
    }

    public function tandaiSelesaiJikaKedaluwarsa(): void
    {
        if ($this->is_kedaluwarsa) {
            $this->finalisasi();
        }
    }
}
