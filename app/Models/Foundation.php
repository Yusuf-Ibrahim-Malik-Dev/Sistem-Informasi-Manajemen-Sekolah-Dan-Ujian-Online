<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Foundation extends Model
{
    protected $table = 'core_foundations';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $foundation) {
            $foundation->uuid ??= (string) Str::uuid();
        });
    }

    public function schools()
    {
        return $this->hasMany(School::class, 'foundation_id');
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class, 'foundation_id');
    }

    public function getNamaAttribute(): string
    {
        return $this->foundation_name;
    }
}
