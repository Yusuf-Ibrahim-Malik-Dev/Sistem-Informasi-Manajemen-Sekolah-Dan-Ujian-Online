<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TesTopikSet extends Model
{
    protected $table = 'cbt_tes_topik_set';
    protected $primaryKey = 'tset_id';
    public $timestamps = false;
    protected $guarded = [];

    public function tes()
    {
        return $this->belongsTo(Tes::class, 'tset_tes_id', 'tes_id');
    }

    public function topik()
    {
        return $this->belongsTo(Topik::class, 'tset_topik_id', 'topik_id');
    }
}
