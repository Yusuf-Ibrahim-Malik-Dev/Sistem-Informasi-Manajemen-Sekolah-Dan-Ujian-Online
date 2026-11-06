<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class StudentBill extends Model {
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'fin_student_bills';
    protected $guarded = [];
    protected $casts = ['issue_date' => 'date', 'due_date' => 'date', 'total_amount' => 'decimal:2', 'paid_amount' => 'decimal:2'];

    protected static function booted(): void
    {
        static::creating(function (self $bill) {
            $bill->uuid ??= (string) Str::uuid();
        });
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PARTIAL => 'Sebagian',
            self::STATUS_PAID => 'Lunas',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    public function items() { return $this->hasMany(StudentBillItem::class, 'bill_id'); }
    public function student() { return $this->belongsTo(Peserta::class, 'student_id', 'user_id'); }
    public function school() { return $this->belongsTo(School::class, 'school_id'); }
    public function foundation() { return $this->belongsTo(Foundation::class, 'foundation_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class, 'academic_year_id'); }
    public function allocations() { return $this->hasMany(PaymentAllocation::class, 'bill_id'); }
    public function payments() { return $this->hasManyThrough(SchoolPayment::class, PaymentAllocation::class, 'bill_id', 'id', 'id', 'payment_id'); }
    public function pendingPayments() { return $this->hasMany(SchoolPayment::class, 'bill_id')->whereIn('status', [SchoolPayment::STATUS_PENDING, SchoolPayment::STATUS_REJECTED]); }
    public function allPayments() { return $this->hasMany(SchoolPayment::class, 'bill_id'); }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'success',
            self::STATUS_PARTIAL => 'info',
            self::STATUS_CANCELLED => 'neutral',
            default => 'warning',
        };
    }
}
