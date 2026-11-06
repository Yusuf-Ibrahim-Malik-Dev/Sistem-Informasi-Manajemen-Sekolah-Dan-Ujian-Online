<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SchoolPayment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'fin_payments';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $payment) {
            $payment->uuid ??= (string) Str::uuid();
        });
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu Verifikasi',
            self::STATUS_VERIFIED => 'Terverifikasi',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    public function student() { return $this->belongsTo(Peserta::class, 'student_id', 'user_id'); }
    public function guardian() { return $this->belongsTo(OrangTua::class, 'guardian_id', 'ortu_id'); }
    public function school() { return $this->belongsTo(School::class, 'school_id'); }
    public function foundation() { return $this->belongsTo(Foundation::class, 'foundation_id'); }
    public function bill() { return $this->belongsTo(StudentBill::class, 'bill_id'); }
    public function allocations() { return $this->hasMany(PaymentAllocation::class, 'payment_id'); }
    public function method() { return $this->belongsTo(SchoolPaymentMethod::class, 'payment_method_id'); }
    public function verifier() { return $this->belongsTo(Admin::class, 'verified_by', 'id'); }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_VERIFIED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'neutral',
            default => 'warning',
        };
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
