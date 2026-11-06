<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $table = 'user_preferences';

    protected $guarded = [];

    protected $casts = [
        'sidebar_collapsed' => 'boolean',
    ];
}
