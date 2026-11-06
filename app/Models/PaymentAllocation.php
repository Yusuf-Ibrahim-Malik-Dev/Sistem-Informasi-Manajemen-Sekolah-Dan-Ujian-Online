<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentAllocation extends Model {
    protected $table = 'fin_payment_allocations';
    protected $guarded = [];
    protected $casts = ['allocated_amount' => 'decimal:2'];
    public function payment() { return $this->belongsTo(SchoolPayment::class, 'payment_id'); }
    public function bill() { return $this->belongsTo(StudentBill::class, 'bill_id'); }
}
