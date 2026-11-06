@extends('layouts.admin')

@section('title', 'Buat Tagihan SPP')
@section('heading', 'Buat Tagihan SPP')
@section('eyebrow', 'Keuangan')

@php
    $initialItems = old('items', [['fee_category_id' => '', 'description' => '', 'quantity' => 1, 'unit_amount' => 0]]);
    $structureData = $structures->map(fn ($structure) => [
        'school_id' => (int) $structure->school_id,
        'fee_category_id' => (int) $structure->fee_category_id,
        'amount' => (float) $structure->amount,
    ])->values();
@endphp

@section('content')
    <div class="glass-card card-pad" data-aos="fade-up"
         x-data="billForm({{ \Illuminate\Support\Js::from($initialItems) }}, {{ \Illuminate\Support\Js::from($structureData) }})">
        <div class="section-head">
            <div>
                <h3 class="section-title">Form Tagihan Baru</h3>
                <p class="section-sub">Pilih peserta, lalu tambahkan satu atau lebih item tagihan.</p>
            </div>
            <a href="{{ route('admin.spp.index') }}" class="btn btn-ghost"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        <form method="POST" action="{{ route('admin.spp.store') }}">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <label class="form-label">Peserta</label>
                    <select name="student_id" x-ref="student" class="form-select" required>
                        <option value="">Pilih peserta</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->user_id }}"
                                    data-school="{{ $student->user_school_id }}"
                                    @selected((string) old('student_id') === (string) $student->user_id)>
                                {{ $student->nama }} ({{ $student->grup?->grup_nama ?? '-' }}) &middot; {{ $student->school?->school_name ?? 'Tanpa sekolah' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal Terbit</label>
                    <input type="date" name="issue_date" value="{{ old('issue_date', now()->toDateString()) }}" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jatuh Tempo</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tahun Ajaran</label>
                    <input type="text" class="form-control" value="Mengikuti sekolah peserta" disabled>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle">
                    <thead>
                        <tr>
                            <th style="min-width:200px">Kategori</th>
                            <th style="min-width:240px">Deskripsi</th>
                            <th style="width:110px">Qty</th>
                            <th style="width:160px">Nominal</th>
                            <th class="text-end" style="width:150px">Subtotal</th>
                            <th style="width:60px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td>
                                    <select class="form-select form-select-sm" x-model="item.fee_category_id"
                                            :name="'items[' + index + '][fee_category_id]'" @change="pickCategory(item)">
                                        <option value="">Tanpa kategori</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" data-school="{{ $category->school_id }}">{{ $category->category_name }} &middot; {{ $category->school?->school_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" x-model="item.description"
                                           :name="'items[' + index + '][description]'" placeholder="Contoh: SPP Bulan November" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" x-model="item.quantity" min="0.01" step="0.01"
                                           :name="'items[' + index + '][quantity]'" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" x-model="item.unit_amount" min="0" step="0.01"
                                           :name="'items[' + index + '][unit_amount]'" required>
                                </td>
                                <td class="text-end text-nowrap fw-semibold" x-text="'Rp ' + format(lineTotal(item))"></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-ghost btn-sm text-danger" @click="removeItem(index)" :disabled="items.length === 1">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total Tagihan</td>
                            <td class="text-end fw-bold text-nowrap" x-text="'Rp ' + format(total)"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex flex-wrap gap-2 align-items-center mt-3">
                <button type="button" class="btn btn-soft" @click="addItem()"><i class="bi bi-plus-lg me-1"></i> Tambah Item</button>
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('admin.spp.index') }}" class="btn btn-ghost">Batal</a>
                    <button class="btn btn-gradient"><i class="bi bi-save me-1"></i> Simpan Tagihan</button>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Catatan</label>
                <textarea name="notes" rows="2" class="form-control" placeholder="Catatan opsional untuk tagihan ini">{{ old('notes') }}</textarea>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    window.billForm = function (initialItems, structures) {
        const fallback = [{ fee_category_id: '', description: '', quantity: 1, unit_amount: 0 }];
        const source = Array.isArray(initialItems) && initialItems.length ? initialItems : fallback;

        return {
            items: source.map(function (item) {
                return {
                    fee_category_id: item.fee_category_id || '',
                    description: item.description || '',
                    quantity: item.quantity || 1,
                    unit_amount: item.unit_amount || 0,
                };
            }),
            structures: structures || [],
            addItem() {
                this.items.push({ fee_category_id: '', description: '', quantity: 1, unit_amount: 0 });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },
            pickCategory(item) {
                const select = this.$refs.student;
                const schoolId = select && select.selectedOptions.length ? select.selectedOptions[0].dataset.school : '';
                if (!schoolId || !item.fee_category_id) return;
                const match = this.structures.find(function (structure) {
                    return String(structure.school_id) === String(schoolId)
                        && String(structure.fee_category_id) === String(item.fee_category_id);
                });
                if (match && Number(item.unit_amount) === 0) {
                    item.unit_amount = match.amount;
                }
            },
            lineTotal(item) {
                return (Number(item.quantity) || 0) * (Number(item.unit_amount) || 0);
            },
            get total() {
                return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
            },
            format(value) {
                return new Intl.NumberFormat('id-ID').format(Number(value) || 0);
            }
        };
    };
</script>
@endpush
