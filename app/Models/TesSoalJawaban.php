<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TesSoalJawaban extends Model
{
    protected $table = 'cbt_tes_soal_jawaban';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    public function jawaban()
    {
        return $this->belongsTo(Jawaban::class, 'soaljawaban_jawaban_id', 'jawaban_id');
    }

    public function tesSoal()
    {
        return $this->belongsTo(TesSoal::class, 'soaljawaban_tessoal_id', 'tessoal_id');
    }
}
