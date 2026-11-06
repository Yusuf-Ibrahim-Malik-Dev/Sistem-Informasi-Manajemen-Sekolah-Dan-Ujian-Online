<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TesSoal extends Model
{
    protected $table = 'cbt_tes_soal';
    protected $primaryKey = 'tessoal_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'tessoal_creation_time' => 'datetime',
        'tessoal_display_time' => 'datetime',
        'tessoal_change_time' => 'datetime',
    ];

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'tessoal_soal_id', 'soal_id');
    }

    public function tesUser()
    {
        return $this->belongsTo(TesUser::class, 'tessoal_tesuser_id', 'tesuser_id');
    }

    public function pilihan()
    {
        return $this->hasMany(TesSoalJawaban::class, 'soaljawaban_tessoal_id', 'tessoal_id')
            ->orderBy('soaljawaban_order');
    }

    public function getSudahDijawabAttribute(): bool
    {
        return $this->tessoal_change_time !== null;
    }

    public function getPilihanTerpilihAttribute(): ?int
    {
        $pilihan = $this->pilihan()->where('soaljawaban_selected', 1)->first();

        return $pilihan?->soaljawaban_jawaban_id;
    }
}
