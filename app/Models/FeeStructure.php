<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FeeStructure extends Model {
    protected $table = 'fin_fee_structures';
    protected $guarded = [];
    protected $casts = ['amount' => 'decimal:2', 'effective_from' => 'date', 'effective_until' => 'date', 'is_active' => 'boolean'];
    public function category() { return $this->belongsTo(FeeCategory::class, 'fee_category_id'); }
    public function school() { return $this->belongsTo(School::class, 'school_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class, 'academic_year_id'); }
}
