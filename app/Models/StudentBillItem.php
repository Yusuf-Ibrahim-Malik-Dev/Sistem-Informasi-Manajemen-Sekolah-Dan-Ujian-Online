<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StudentBillItem extends Model {
    protected $table = 'fin_student_bill_items';
    protected $guarded = [];
    protected $casts = ['quantity' => 'decimal:2', 'unit_amount' => 'decimal:2', 'line_total' => 'decimal:2'];
    public function bill() { return $this->belongsTo(StudentBill::class, 'bill_id'); }
    public function category() { return $this->belongsTo(FeeCategory::class, 'fee_category_id'); }
}
