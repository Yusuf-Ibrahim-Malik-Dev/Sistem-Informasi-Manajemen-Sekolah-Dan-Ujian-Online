<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Peserta;
use App\Models\School;
use App\Models\SchoolPayment;
use App\Models\SchoolPaymentMethod;
use App\Models\StudentBill;
use App\Models\StudentBillItem;
use App\Support\SchoolContext;
use App\Support\SppPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SppController extends Controller
{
    public function index(Request $request, SchoolContext $context)
    {
        $base = $this->scopedBills($context);

        $statistik = [
            'jumlah' => (clone $base)->count(),
            'total_tagihan' => (float) (clone $base)->sum('total_amount'),
            'total_terbayar' => (float) (clone $base)->sum('paid_amount'),
            'outstanding' => (float) (clone $base)->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as s')->value('s'),
        ];

        $daftar = (clone $base)
            ->with(['student.grup', 'school'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->q.'%';
                $query->whereHas('student', fn ($p) => $p
                    ->where('user_firstname', 'like', $term)
                    ->orWhere('user_name', 'like', $term)
                    ->orWhere('user_nis', 'like', $term));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.spp.index', [
            'daftar' => $daftar,
            'statistik' => $statistik,
            'statusOptions' => StudentBill::statusOptions(),
        ]);
    }

    public function setup(SchoolContext $context)
    {
        $schoolIds = $context->availableSchools()->pluck('id');

        return view('admin.spp.setup', [
            'schools' => $context->availableSchools(),
            'categories' => FeeCategory::whereIn('school_id', $schoolIds)->with('school')->orderBy('category_name')->get(),
            'structures' => FeeStructure::whereIn('school_id', $schoolIds)->with(['category', 'school', 'academicYear'])->orderByDesc('id')->get(),
            'methods' => SchoolPaymentMethod::whereIn('school_id', $schoolIds)->with('school')->orderBy('method_name')->get(),
        ]);
    }

    public function storeCategory(Request $request, SchoolContext $context)
    {
        $school = $this->resolveSchool($request, $context);

        $data = $request->validate([
            'category_code' => [
                'required', 'string', 'max:30', 'alpha_dash',
                Rule::unique('fin_fee_categories', 'category_code')->where(fn ($q) => $q->where('school_id', $school->id)),
            ],
            'category_name' => ['required', 'string', 'max:120'],
            'is_recurring' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        FeeCategory::create([
            'foundation_id' => $school->foundation_id,
            'school_id' => $school->id,
            'category_code' => strtoupper($data['category_code']),
            'category_name' => $data['category_name'],
            'is_recurring' => $request->boolean('is_recurring'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Kategori biaya berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, FeeCategory $feeCategory, SchoolContext $context)
    {
        $this->authorizeSchool($feeCategory->school_id, $context);

        $data = $request->validate([
            'category_code' => [
                'required', 'string', 'max:30', 'alpha_dash',
                Rule::unique('fin_fee_categories', 'category_code')
                    ->where(fn ($q) => $q->where('school_id', $feeCategory->school_id))
                    ->ignore($feeCategory->id),
            ],
            'category_name' => ['required', 'string', 'max:120'],
            'is_recurring' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $feeCategory->update([
            'category_code' => strtoupper($data['category_code']),
            'category_name' => $data['category_name'],
            'is_recurring' => $request->boolean('is_recurring'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Kategori biaya berhasil diperbarui.');
    }

    public function destroyCategory(FeeCategory $feeCategory, SchoolContext $context)
    {
        $this->authorizeSchool($feeCategory->school_id, $context);

        if ($feeCategory->feeStructures()->exists() || StudentBillItem::where('fee_category_id', $feeCategory->id)->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih dipakai pada tarif atau tagihan.');
        }

        $feeCategory->delete();

        return back()->with('success', 'Kategori biaya berhasil dihapus.');
    }

    public function storeStructure(Request $request, SchoolContext $context)
    {
        $school = $this->resolveSchool($request, $context);

        $data = $request->validate([
            'fee_category_id' => [
                'required',
                Rule::exists('fin_fee_categories', 'id')->where(fn ($q) => $q->where('school_id', $school->id)),
            ],
            'academic_year_id' => [
                'nullable',
                Rule::exists('core_academic_years', 'id')->where(fn ($q) => $q->where('school_id', $school->id)),
            ],
            'grade_code' => ['nullable', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        FeeStructure::create([
            'foundation_id' => $school->foundation_id,
            'school_id' => $school->id,
            'academic_year_id' => $data['academic_year_id'] ?? null,
            'fee_category_id' => $data['fee_category_id'],
            'grade_code' => $data['grade_code'] ?? null,
            'amount' => $data['amount'],
            'effective_from' => $data['effective_from'] ?? null,
            'effective_until' => $data['effective_until'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Tarif biaya berhasil ditambahkan.');
    }

    public function updateStructure(Request $request, FeeStructure $feeStructure, SchoolContext $context)
    {
        $this->authorizeSchool($feeStructure->school_id, $context);

        $data = $request->validate([
            'fee_category_id' => [
                'required',
                Rule::exists('fin_fee_categories', 'id')->where(fn ($q) => $q->where('school_id', $feeStructure->school_id)),
            ],
            'academic_year_id' => [
                'nullable',
                Rule::exists('core_academic_years', 'id')->where(fn ($q) => $q->where('school_id', $feeStructure->school_id)),
            ],
            'grade_code' => ['nullable', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['nullable', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $feeStructure->update([
            'academic_year_id' => $data['academic_year_id'] ?? null,
            'fee_category_id' => $data['fee_category_id'],
            'grade_code' => $data['grade_code'] ?? null,
            'amount' => $data['amount'],
            'effective_from' => $data['effective_from'] ?? null,
            'effective_until' => $data['effective_until'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Tarif biaya berhasil diperbarui.');
    }

    public function destroyStructure(FeeStructure $feeStructure, SchoolContext $context)
    {
        $this->authorizeSchool($feeStructure->school_id, $context);

        $feeStructure->delete();

        return back()->with('success', 'Tarif biaya berhasil dihapus.');
    }

    public function storeMethod(Request $request, SchoolContext $context)
    {
        $school = $this->resolveSchool($request, $context);

        $data = $request->validate([
            'method_code' => [
                'required', 'string', 'max:30', 'alpha_dash',
                Rule::unique('fin_payment_methods', 'method_code')->where(fn ($q) => $q->where('school_id', $school->id)),
            ],
            'method_name' => ['required', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:60'],
            'account_holder' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
            'show_to_guardian' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        SchoolPaymentMethod::create([
            'school_id' => $school->id,
            'method_code' => strtoupper($data['method_code']),
            'method_name' => $data['method_name'],
            'bank_name' => $data['bank_name'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'account_holder' => $data['account_holder'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'show_to_guardian' => $request->boolean('show_to_guardian', true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return back()->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    public function updateMethod(Request $request, SchoolPaymentMethod $paymentMethod, SchoolContext $context)
    {
        $this->authorizeSchool($paymentMethod->school_id, $context);

        $data = $request->validate([
            'method_code' => [
                'required', 'string', 'max:30', 'alpha_dash',
                Rule::unique('fin_payment_methods', 'method_code')
                    ->where(fn ($q) => $q->where('school_id', $paymentMethod->school_id))
                    ->ignore($paymentMethod->id),
            ],
            'method_name' => ['required', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:60'],
            'account_holder' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
            'show_to_guardian' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $paymentMethod->update([
            'method_code' => strtoupper($data['method_code']),
            'method_name' => $data['method_name'],
            'bank_name' => $data['bank_name'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'account_holder' => $data['account_holder'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'show_to_guardian' => $request->boolean('show_to_guardian'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        return back()->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    public function destroyMethod(SchoolPaymentMethod $paymentMethod, SchoolContext $context)
    {
        $this->authorizeSchool($paymentMethod->school_id, $context);

        if (SchoolPayment::where('payment_method_id', $paymentMethod->id)->exists()) {
            return back()->with('error', 'Metode pembayaran tidak dapat dihapus karena sudah dipakai transaksi.');
        }

        $paymentMethod->delete();

        return back()->with('success', 'Metode pembayaran berhasil dihapus.');
    }

    public function create(SchoolContext $context)
    {
        $schoolIds = $context->availableSchools()->pluck('id');

        return view('admin.spp.create', [
            'students' => $context->apply(Peserta::query(), 'user_school_id', 'user_foundation_id')
                ->with(['school', 'grup'])->orderBy('user_firstname')->get(),
            'categories' => FeeCategory::whereIn('school_id', $schoolIds)->where('is_active', true)->with('school')->orderBy('category_name')->get(),
            'structures' => FeeStructure::whereIn('school_id', $schoolIds)->where('is_active', true)->get(),
        ]);
    }

    public function storeBill(Request $request, SchoolContext $context)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:cbt_user,user_id'],
            'academic_year_id' => ['nullable', 'exists:core_academic_years,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.fee_category_id' => ['nullable', 'integer'],
            'items.*.description' => ['required', 'string', 'max:180'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $student = $context->apply(Peserta::query(), 'user_school_id', 'user_foundation_id')
            ->whereKey($data['student_id'])->firstOrFail();

        abort_unless($student->user_school_id, 422, 'Peserta belum memiliki sekolah.');
        $this->authorizeSchool((int) $student->user_school_id, $context);
        $school = School::findOrFail($student->user_school_id);

        if (! empty($data['academic_year_id'])) {
            abort_unless(
                $school->academicYears()->whereKey($data['academic_year_id'])->exists(),
                422,
                'Tahun ajaran tidak sesuai dengan sekolah peserta.'
            );
        }

        $categoryIds = collect($data['items'])->pluck('fee_category_id')->filter()->unique()->all();

        if (! empty($categoryIds)) {
            abort_unless(
                FeeCategory::whereIn('id', $categoryIds)->where('school_id', $school->id)->count() === count($categoryIds),
                422,
                'Kategori biaya tidak sesuai dengan sekolah peserta.'
            );
        }

        $bill = DB::transaction(function () use ($data, $student, $school) {
            $bill = StudentBill::create([
                'uuid' => (string) Str::uuid(),
                'foundation_id' => $school->foundation_id,
                'school_id' => $school->id,
                'academic_year_id' => $data['academic_year_id'] ?? null,
                'student_id' => $student->user_id,
                'bill_number' => $this->generateBillNumber($school),
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'] ?? null,
                'total_amount' => 0,
                'paid_amount' => 0,
                'status' => StudentBill::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth('web')->id(),
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {
                $lineTotal = round((float) $item['quantity'] * (float) $item['unit_amount'], 2);
                $total += $lineTotal;

                StudentBillItem::create([
                    'bill_id' => $bill->id,
                    'fee_category_id' => $item['fee_category_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_amount' => $item['unit_amount'],
                    'line_total' => $lineTotal,
                ]);
            }

            $bill->update(['total_amount' => $total]);

            return $bill;
        });

        return redirect()->route('admin.spp.show', $bill)->with('success', 'Tagihan SPP berhasil dibuat.');
    }

    public function show(StudentBill $studentBill, SchoolContext $context)
    {
        $studentBill = $this->scopedBills($context)
            ->with(['student.grup', 'school', 'academicYear', 'items.category', 'allocations.payment.method'])
            ->whereKey($studentBill->getKey())
            ->firstOrFail();

        return view('admin.spp.show', [
            'bill' => $studentBill,
            'methods' => SchoolPaymentMethod::where('school_id', $studentBill->school_id)
                ->where('is_active', true)->orderBy('method_name')->get(),
        ]);
    }

    public function destroyBill(StudentBill $studentBill, SchoolContext $context)
    {
        $studentBill = $this->scopedBills($context)->whereKey($studentBill->getKey())->firstOrFail();

        if ($studentBill->allocations()->exists() || (float) $studentBill->paid_amount > 0) {
            return back()->with('error', 'Tagihan tidak dapat dihapus karena sudah memiliki pembayaran.');
        }

        DB::transaction(function () use ($studentBill) {
            $studentBill->items()->delete();
            $studentBill->delete();
        });

        return redirect()->route('admin.spp.index')->with('success', 'Tagihan berhasil dihapus.');
    }

    public function storePayment(Request $request, StudentBill $studentBill, SchoolContext $context, SppPaymentService $service)
    {
        $studentBill = $this->scopedBills($context)->whereKey($studentBill->getKey())->firstOrFail();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method_id' => [
                'nullable',
                Rule::exists('fin_payment_methods', 'id')->where(fn ($q) => $q->where('school_id', $studentBill->school_id)),
            ],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service->recordManual($studentBill, $data, auth('web')->id());

        return back()->with('success', 'Pembayaran SPP berhasil dicatat.');
    }

    public function payments(Request $request, SchoolContext $context)
    {
        $base = $this->scopedPayments($context);

        $statistik = [
            'jumlah' => (clone $base)->count(),
            'total' => (float) (clone $base)->where('status', SchoolPayment::STATUS_VERIFIED)->sum('amount'),
            'menunggu' => (clone $base)->where('status', SchoolPayment::STATUS_PENDING)->count(),
            'ditolak' => (clone $base)->where('status', SchoolPayment::STATUS_REJECTED)->count(),
        ];

        $daftar = (clone $base)
            ->with(['student.grup', 'method', 'school', 'bill', 'allocations.bill', 'verifier'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->q.'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('payment_number', 'like', $term)
                        ->orWhereHas('student', fn ($p) => $p
                            ->where('user_firstname', 'like', $term)
                            ->orWhere('user_name', 'like', $term));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('dari'), fn ($query) => $query->whereDate('created_at', '>=', $request->dari))
            ->when($request->filled('sampai'), fn ($query) => $query->whereDate('created_at', '<=', $request->sampai))
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END")
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.spp.payments', [
            'daftar' => $daftar,
            'statistik' => $statistik,
            'statusOptions' => SchoolPayment::statusOptions(),
        ]);
    }

    public function paymentShow(SchoolPayment $payment, SchoolContext $context)
    {
        $payment = $this->scopedPayments($context)
            ->with(['student.grup', 'student.school', 'method', 'school', 'bill.items', 'allocations.bill', 'verifier', 'guardian'])
            ->whereKey($payment->getKey())
            ->firstOrFail();

        return view('admin.spp.payment-show', [
            'payment' => $payment,
            'methods' => SchoolPaymentMethod::where('school_id', $payment->school_id)
                ->where('is_active', true)->orderBy('method_name')->get(),
        ]);
    }

    public function verifyPayment(Request $request, SchoolPayment $payment, SchoolContext $context, SppPaymentService $service)
    {
        $payment = $this->scopedPayments($context)->whereKey($payment->getKey())->firstOrFail();

        $data = $request->validate([
            'aksi' => ['required', Rule::in(['verifikasi', 'tolak'])],
            'alasan' => ['nullable', 'string', 'max:500', 'required_if:aksi,tolak'],
        ]);

        if ($data['aksi'] === 'verifikasi') {
            $service->verify($payment, auth('web')->id());
            ActivityLog::record(auth('web')->user()->username, 'Verifikasi pembayaran SPP #'.$payment->getKey());
            $message = 'Pembayaran SPP berhasil diverifikasi.';
        } else {
            $service->reject($payment, auth('web')->id(), $data['alasan']);
            ActivityLog::record(auth('web')->user()->username, 'Penolakan pembayaran SPP #'.$payment->getKey());
            $message = 'Pembayaran SPP ditolak. Orang tua dapat mengunggah ulang bukti.';
        }

        return back()->with('success', $message);
    }

    public function cancelPayment(SchoolPayment $payment, SchoolContext $context, SppPaymentService $service)
    {
        $payment = $this->scopedPayments($context)->whereKey($payment->getKey())->firstOrFail();

        $service->cancel($payment, auth('web')->id());
        ActivityLog::record(auth('web')->user()->username, 'Membatalkan pembayaran SPP #'.$payment->getKey());

        return back()->with('success', 'Pembayaran SPP berhasil dibatalkan.');
    }

    public function receipt(SchoolPayment $payment, SchoolContext $context)
    {
        $payment = $this->scopedPayments($context)
            ->with(['student.grup', 'method', 'school', 'bill.items', 'allocations.bill', 'verifier'])
            ->whereKey($payment->getKey())
            ->firstOrFail();

        abort_unless($payment->status === SchoolPayment::STATUS_VERIFIED, 404);

        return view('admin.spp.kwitansi', compact('payment'));
    }

    public function proof(SchoolPayment $payment, SchoolContext $context)
    {
        $payment = $this->scopedPayments($context)->whereKey($payment->getKey())->firstOrFail();

        abort_unless($payment->proof_path && Storage::disk('public')->exists($payment->proof_path), 404);

        return Storage::disk('public')->response($payment->proof_path);
    }

    private function scopedBills(SchoolContext $context)
    {
        return $context->apply(StudentBill::query(), 'school_id', 'foundation_id');
    }

    private function scopedPayments(SchoolContext $context)
    {
        return $context->apply(SchoolPayment::query(), 'school_id', 'foundation_id');
    }

    private function resolveSchool(Request $request, SchoolContext $context): School
    {
        $schoolId = $context->schoolId() ?? (int) $request->input('school_id');
        $school = $context->availableSchools()->firstWhere('id', $schoolId);

        abort_unless($school, 422, 'Pilih sekolah yang valid terlebih dahulu.');

        return $school;
    }

    private function authorizeSchool(int $schoolId, SchoolContext $context): void
    {
        abort_unless($context->availableSchools()->contains('id', $schoolId), 403);
    }

    private function generateBillNumber(School $school): string
    {
        return 'SPP-'.$school->school_code.'-'.now()->format('Ym').'-'.strtoupper(Str::random(5));
    }

    private function generatePaymentNumber(StudentBill $bill): string
    {
        return 'PAY-'.$bill->school->school_code.'-'.now()->format('Ym').'-'.strtoupper(Str::random(6));
    }
}
