<?php

namespace App\Support;

use App\Models\OrangTua;
use App\Models\SchoolPayment;
use App\Models\StudentBill;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SppPaymentService
{
    public function recordManual(StudentBill $bill, array $data, ?int $adminId): SchoolPayment
    {
        return DB::transaction(function () use ($bill, $data, $adminId) {
            $locked = StudentBill::query()->whereKey($bill->getKey())->lockForUpdate()->firstOrFail();
            $this->pastikanDapatDibayar($locked);

            $amount = round((float) $data['amount'], 2);
            $remaining = $this->sisaTagihan($locked);

            if ($amount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'Nominal pembayaran melebihi sisa tagihan (Rp '.number_format($remaining, 0, ',', '.').').',
                ]);
            }

            $payment = SchoolPayment::create([
                'uuid' => (string) Str::uuid(),
                'foundation_id' => $locked->foundation_id,
                'school_id' => $locked->school_id,
                'student_id' => $locked->student_id,
                'bill_id' => $locked->id,
                'guardian_id' => null,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'recorded_by' => $adminId,
                'verified_by' => $adminId,
                'verified_at' => now(),
                'payment_number' => $this->generatePaymentNumber($locked),
                'amount' => $amount,
                'status' => SchoolPayment::STATUS_VERIFIED,
                'paid_at' => $data['paid_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->alokasikan($payment, $locked, $amount);

            return $payment;
        });
    }

    public function submitProof(StudentBill $bill, OrangTua $guardian, UploadedFile $proof, float $amount, ?string $notes): SchoolPayment
    {
        return DB::transaction(function () use ($bill, $guardian, $proof, $amount, $notes) {
            $locked = StudentBill::query()->whereKey($bill->getKey())->lockForUpdate()->firstOrFail();
            $this->pastikanDapatDibayar($locked);

            $amount = round($amount, 2);

            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'Nominal pembayaran harus lebih dari nol.']);
            }

            $pending = SchoolPayment::query()
                ->where('bill_id', $locked->id)
                ->where('status', SchoolPayment::STATUS_PENDING)
                ->first();

            $pendingAmount = (float) SchoolPayment::query()
                ->where('bill_id', $locked->id)
                ->where('status', SchoolPayment::STATUS_PENDING)
                ->when($pending, fn ($q) => $q->whereKeyNot($pending->getKey()))
                ->sum('amount');

            $remaining = $this->sisaTagihan($locked);

            if ($amount + $pendingAmount > $remaining) {
                throw ValidationException::withMessages([
                    'amount' => 'Nominal melebihi sisa tagihan yang belum dibayar (Rp '.number_format($remaining, 0, ',', '.').').',
                ]);
            }

            $path = $proof->store('bukti-spp', 'public');

            if ($pending) {
                if ($pending->proof_path) {
                    Storage::disk('public')->delete($pending->proof_path);
                }

                $pending->update([
                    'guardian_id' => $guardian->ortu_id,
                    'payment_method_id' => null,
                    'amount' => $amount,
                    'proof_path' => $path,
                    'notes' => $notes,
                    'rejection_reason' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                    'paid_at' => null,
                ]);

                return $pending->fresh();
            }

            return SchoolPayment::create([
                'uuid' => (string) Str::uuid(),
                'foundation_id' => $locked->foundation_id,
                'school_id' => $locked->school_id,
                'student_id' => $locked->student_id,
                'bill_id' => $locked->id,
                'guardian_id' => $guardian->ortu_id,
                'payment_method_id' => null,
                'recorded_by' => null,
                'payment_number' => $this->generatePaymentNumber($locked),
                'amount' => $amount,
                'status' => SchoolPayment::STATUS_PENDING,
                'proof_path' => $path,
                'notes' => $notes,
            ]);
        });
    }

    public function verify(SchoolPayment $payment, int $adminId): SchoolPayment
    {
        return DB::transaction(function () use ($payment, $adminId) {
            $locked = SchoolPayment::query()->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== SchoolPayment::STATUS_PENDING) {
                throw ValidationException::withMessages(['status' => 'Pembayaran ini sudah diproses.']);
            }

            $bill = $locked->bill
                ? StudentBill::query()->whereKey($locked->bill_id)->lockForUpdate()->firstOrFail()
                : StudentBill::query()
                    ->where('student_id', $locked->student_id)
                    ->whereNotIn('status', [StudentBill::STATUS_PAID, StudentBill::STATUS_CANCELLED])
                    ->orderBy('issue_date')
                    ->lockForUpdate()
                    ->firstOrFail();

            $remaining = $this->sisaTagihan($bill);

            if ((float) $locked->amount > $remaining) {
                throw ValidationException::withMessages([
                    'status' => 'Nominal pembayaran melebihi sisa tagihan (Rp '.number_format($remaining, 0, ',', '.').').',
                ]);
            }

            $locked->update([
                'bill_id' => $bill->id,
                'status' => SchoolPayment::STATUS_VERIFIED,
                'verified_by' => $adminId,
                'verified_at' => now(),
                'paid_at' => now(),
                'rejection_reason' => null,
            ]);

            $this->alokasikan($locked, $bill, (float) $locked->amount);

            return $locked->fresh();
        });
    }

    public function reject(SchoolPayment $payment, int $adminId, string $reason): SchoolPayment
    {
        return DB::transaction(function () use ($payment, $adminId, $reason) {
            $locked = SchoolPayment::query()->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== SchoolPayment::STATUS_PENDING) {
                throw ValidationException::withMessages(['status' => 'Pembayaran ini sudah diproses.']);
            }

            $locked->update([
                'status' => SchoolPayment::STATUS_REJECTED,
                'verified_by' => $adminId,
                'verified_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $locked->fresh();
        });
    }

    public function cancel(SchoolPayment $payment, int $adminId): SchoolPayment
    {
        return DB::transaction(function () use ($payment, $adminId) {
            $locked = SchoolPayment::query()->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, [SchoolPayment::STATUS_PENDING, SchoolPayment::STATUS_REJECTED], true)) {
                throw ValidationException::withMessages(['status' => 'Pembayaran terverifikasi tidak dapat dibatalkan.']);
            }

            $locked->update([
                'status' => SchoolPayment::STATUS_CANCELLED,
                'verified_by' => $adminId,
                'verified_at' => now(),
            ]);

            return $locked->fresh();
        });
    }

    private function alokasikan(SchoolPayment $payment, StudentBill $bill, float $amount): void
    {
        $payment->allocations()->create([
            'bill_id' => $bill->id,
            'allocated_amount' => $amount,
        ]);

        $paid = (float) $bill->paid_amount + $amount;

        $bill->update([
            'paid_amount' => $paid,
            'status' => $paid >= (float) $bill->total_amount
                ? StudentBill::STATUS_PAID
                : StudentBill::STATUS_PARTIAL,
        ]);
    }

    private function pastikanDapatDibayar(StudentBill $bill): void
    {
        if (in_array($bill->status, [StudentBill::STATUS_CANCELLED, StudentBill::STATUS_PAID], true)) {
            throw ValidationException::withMessages([
                'amount' => 'Tagihan ini sudah lunas atau dibatalkan.',
            ]);
        }
    }

    private function sisaTagihan(StudentBill $bill): float
    {
        return max(0, (float) $bill->total_amount - (float) $bill->paid_amount);
    }

    private function generatePaymentNumber(StudentBill $bill): string
    {
        $schoolCode = $bill->school?->school_code ?? 'SCH';

        do {
            $number = 'PAY-'.$schoolCode.'-'.now()->format('Ym').'-'.strtoupper(Str::random(6));
        } while (SchoolPayment::where('school_id', $bill->school_id)->where('payment_number', $number)->exists());

        return $number;
    }
}
