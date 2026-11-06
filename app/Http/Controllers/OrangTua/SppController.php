<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrangTua\Concerns\ResolvesAnak;
use App\Models\SchoolPayment;
use App\Models\SchoolPaymentMethod;
use App\Models\StudentBill;
use App\Support\SppPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SppController extends Controller
{
    use ResolvesAnak;

    public function index()
    {
        $orangTua = auth('orangtua')->user();
        [$anak, $daftarAnak] = $this->anakAktif($orangTua);

        if (! $anak) {
            return view('orangtua.spp.index', compact('orangTua', 'daftarAnak', 'anak'));
        }

        $bills = StudentBill::where('student_id', $anak->user_id)
            ->with(['school', 'items', 'pendingPayments', 'allocations.payment'])
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->get();

        $payments = SchoolPayment::where('student_id', $anak->user_id)
            ->with(['bill', 'method'])
            ->orderByDesc('id')
            ->get();

        $rekening = $this->rekeningForSchool($anak->user_school_id ?: $anak->grup?->grup_school_id);

        $statistik = [
            'total' => (float) $bills->sum('total_amount'),
            'terbayar' => (float) $bills->sum('paid_amount'),
            'sisa' => (float) $bills->sum(fn (StudentBill $bill) => $bill->remaining_amount),
            'menunggu' => $payments->where('status', SchoolPayment::STATUS_PENDING)->count(),
            'ditolak' => $payments->where('status', SchoolPayment::STATUS_REJECTED)->count(),
        ];

        return view('orangtua.spp.index', compact('orangTua', 'daftarAnak', 'anak', 'bills', 'payments', 'statistik', 'rekening'));
    }

    public function show(StudentBill $bill)
    {
        $orangTua = auth('orangtua')->user();
        [$anak, $daftarAnak] = $this->anakAktif($orangTua, (int) $bill->student_id);

        abort_unless($anak && (int) $bill->student_id === (int) $anak->user_id, 403);

        $bill->load(['school', 'items.category', 'pendingPayments', 'allocations.payment']);

        $payments = SchoolPayment::where('bill_id', $bill->id)
            ->with('method')
            ->orderByDesc('id')
            ->get();

        $rekening = $this->rekeningForSchool($bill->school_id);

        return view('orangtua.spp.show', compact('orangTua', 'daftarAnak', 'anak', 'bill', 'payments', 'rekening'));
    }

    public function storeProof(Request $request, StudentBill $bill, SppPaymentService $service)
    {
        $orangTua = auth('orangtua')->user();
        [$anak] = $this->anakAktif($orangTua, (int) $bill->student_id);

        abort_unless($anak && (int) $bill->student_id === (int) $anak->user_id, 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $service->submitProof($bill, $orangTua, $request->file('proof'), (float) $data['amount'], $data['notes'] ?? null);

        return redirect()
            ->route('orangtua.spp.show', ['bill' => $bill, 'anak' => $anak->user_id])
            ->with('success', 'Bukti pembayaran SPP berhasil diunggah. Menunggu verifikasi admin.');
    }

    public function receipt(SchoolPayment $payment)
    {
        $orangTua = auth('orangtua')->user();
        [$anak] = $this->anakAktif($orangTua, (int) $payment->student_id);

        abort_unless($anak && (int) $payment->student_id === (int) $anak->user_id, 403);
        abort_unless($payment->status === SchoolPayment::STATUS_VERIFIED, 404);

        $payment->load(['student.grup', 'method', 'school', 'bill.items', 'allocations.bill', 'verifier']);

        return view('orangtua.spp.kwitansi', compact('payment', 'orangTua'));
    }

    public function proof(SchoolPayment $payment)
    {
        $orangTua = auth('orangtua')->user();
        [$anak] = $this->anakAktif($orangTua, (int) $payment->student_id);

        abort_unless($anak && (int) $payment->student_id === (int) $anak->user_id, 403);
        abort_unless($payment->proof_path && Storage::disk('public')->exists($payment->proof_path), 404);

        return Storage::disk('public')->response($payment->proof_path);
    }

    private function rekeningForSchool(?int $schoolId)
    {
        if (! $schoolId) {
            return collect();
        }

        return SchoolPaymentMethod::query()
            ->where('school_id', $schoolId)
            ->visibleToGuardian()
            ->orderBy('sort_order')
            ->orderBy('bank_name')
            ->get();
    }
}
