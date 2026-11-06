<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    protected $table = 'user_akses';
    public $timestamps = false;
    protected $guarded = [];
}
