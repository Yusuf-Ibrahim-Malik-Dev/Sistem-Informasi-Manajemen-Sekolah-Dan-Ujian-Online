<?php

namespace App\Models;

use App\Support\HtmlText;
use Illuminate\Database\Eloquent\Model;

class Jawaban extends Model
{
    protected $table = 'cbt_jawaban';
    protected $primaryKey = 'jawaban_id';
    public $timestamps = false;
    protected $guarded = [];

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'jawaban_soal_id', 'soal_id');
    }

    public function getDetailHtmlAttribute(): string
    {
        return e(HtmlText::plain($this->jawaban_detail));
    }
}
