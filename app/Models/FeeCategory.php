<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FeeCategory extends Model {
    protected $table = 'fin_fee_categories';
    protected $guarded = [];
    protected $casts = ['is_recurring' => 'boolean', 'is_active' => 'boolean'];
    public function feeStructures() { return $this->hasMany(FeeStructure::class, 'fee_category_id'); }
    public function school() { return $this->belongsTo(School::class, 'school_id'); }
}
