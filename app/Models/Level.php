<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $table = 'user_level';
    public $timestamps = false;
    protected $guarded = [];

    public function admins()
    {
        return $this->hasMany(Admin::class, 'level', 'level');
    }
}
