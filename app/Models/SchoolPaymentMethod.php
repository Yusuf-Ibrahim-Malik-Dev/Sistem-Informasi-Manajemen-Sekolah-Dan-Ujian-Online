<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolPaymentMethod extends Model
{
    protected $table = 'fin_payment_methods';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'show_to_guardian' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function scopeVisibleToGuardian($query)
    {
        return $query
            ->where('is_active', true)
            ->where('show_to_guardian', true)
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->bank_name ?: $this->method_name;
    }
}
