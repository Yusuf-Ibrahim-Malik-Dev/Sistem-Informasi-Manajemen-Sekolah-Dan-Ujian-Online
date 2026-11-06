<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolLevel extends Model
{
    protected $table = 'core_school_levels';

    protected $guarded = [];

    public static function options(): array
    {
        return self::orderBy('id')->pluck('level_name', 'id')->all();
    }

    public function schools()
    {
        return $this->hasMany(School::class, 'school_level_id');
    }
}
