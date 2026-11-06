<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topik extends Model
{
    public const TIPE_UM = 'um';
    public const TIPE_UTS = 'uts';
    public const TIPE_UAS = 'uas';

    protected $table = 'cbt_topik';
    protected $primaryKey = 'topik_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'topik_creation_time' => 'datetime',
    ];

    public static function tipeUjianOptions(): array
    {
        return [
            self::TIPE_UM => 'UM (Umum)',
            self::TIPE_UTS => 'UTS',
            self::TIPE_UAS => 'UAS',
        ];
    }

    public function modul()
    {
        return $this->belongsTo(Modul::class, 'topik_modul_id', 'modul_id');
    }

    public function guru()
    {
        return $this->belongsTo(Admin::class, 'topik_guru_id', 'id');
    }

    public function soals()
    {
        return $this->hasMany(Soal::class, 'soal_topik_id', 'topik_id');
    }

    public function dapatDikelolaOleh(?Admin $admin): bool
    {
        return $admin !== null
            && $this->topik_guru_id !== null
            && (int) $this->topik_guru_id === (int) $admin->id;
    }

    public function getTipeUjianLabelAttribute(): string
    {
        return self::tipeUjianOptions()[$this->topik_tipe_ujian] ?? 'UM (Umum)';
    }
}
