<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modul extends Model
{
    protected $table = 'cbt_modul';
    protected $primaryKey = 'modul_id';
    public $timestamps = false;
    protected $guarded = [];

    public function topiks()
    {
        return $this->hasMany(Topik::class, 'topik_modul_id', 'modul_id');
    }

    public function rapors()
    {
        return $this->hasMany(Raport::class, 'raport_modul_id', 'modul_id');
    }
}
