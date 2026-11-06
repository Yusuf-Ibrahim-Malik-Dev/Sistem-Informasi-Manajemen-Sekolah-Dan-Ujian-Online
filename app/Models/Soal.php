<?php

namespace App\Models;

use App\Support\HtmlText;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    protected $table = 'cbt_soal';
    protected $primaryKey = 'soal_id';
    public $timestamps = false;
    protected $guarded = [];

    public const TIPE_GANDA = 1;
    public const TIPE_ESSAY = 2;
    public const TIPE_SINGKAT = 3;

    public function topik()
    {
        return $this->belongsTo(Topik::class, 'soal_topik_id', 'topik_id');
    }

    public function jawabans()
    {
        return $this->hasMany(Jawaban::class, 'jawaban_soal_id', 'soal_id')->orderBy('jawaban_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'soal_creator_id', 'id');
    }

    public function getTipeLabelAttribute(): string
    {
        return match ((int) $this->soal_tipe) {
            self::TIPE_GANDA => 'Pilihan Ganda',
            self::TIPE_ESSAY => 'Essay',
            self::TIPE_SINGKAT => 'Jawaban Singkat',
            default => 'Tidak diketahui',
        };
    }

    public function getDetailHtmlAttribute(): string
    {
        return e(HtmlText::plain($this->soal_detail));
    }

    public function getAudioUrlAttribute(): ?string
    {
        return $this->soal_audio ? asset('uploads/topik_' . $this->soal_topik_id . '/' . $this->soal_audio) : null;
    }
}
