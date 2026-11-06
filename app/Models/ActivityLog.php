<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'user_log';
    public $timestamps = false;
    protected $guarded = [];

    public static function record(string $username, string $log): void
    {
        static::create(['username' => $username, 'log' => $log, 'ts' => now()]);
    }
}
