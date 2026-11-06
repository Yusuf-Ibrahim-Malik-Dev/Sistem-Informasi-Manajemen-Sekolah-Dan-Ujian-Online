<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Raport extends Model
{
    protected $table = 'cbt_raport';
    protected $primaryKey = 'raport_id';
    protected $guarded = [];

    protected $casts = [
        'raport_nilai' => 'float',
        'raport_tingkat' => 'integer',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'raport_user_id', 'user_id');
    }

    public function modul()
    {
        return $this->belongsTo(Modul::class, 'raport_modul_id', 'modul_id');
    }

    public function guru()
    {
        return $this->belongsTo(Admin::class, 'raport_guru_id', 'id');
    }

    public function getJenjangLabelAttribute(): string
    {
        return UserGroup::jenjangOptions()[$this->raport_jenjang] ?? $this->raport_jenjang;
    }

    public function getKelasLabelAttribute(): string
    {
        return $this->jenjang_label . ' Kelas ' . (int) $this->raport_tingkat;
    }

    public function getPredikatAttribute(): string
    {
        $nilai = (float) $this->raport_nilai;

        return match (true) {
            $nilai >= 90 => 'A',
            $nilai >= 80 => 'B',
            $nilai >= 70 => 'C',
            default => 'D',
        };
    }
}
