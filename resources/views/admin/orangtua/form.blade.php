@extends('layouts.admin')

@section('title', $orangTua->exists ? 'Edit Orang Tua' : 'Tambah Orang Tua')
@section('heading', $orangTua->exists ? 'Edit Akun Orang Tua' : 'Tambah Akun Orang Tua')
@section('eyebrow', 'Data Peserta')

@section('content')
    @php
        $terpilih = array_map('strval', old('peserta', $terpilih));
    @endphp

    <form method="POST" action="{{ $orangTua->exists ? route('admin.orangtua.update', $orangTua) : route('admin.orangtua.store') }}" data-aos="fade-up">
        @csrf
        @if ($orangTua->exists)
            @method('PUT')
        @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="glass-card card-pad mb-4">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Informasi Akun</h3>
                            <p class="section-sub">Username dan password digunakan orang tua untuk masuk ke portal.</p>
                        </div>
                        <a href="{{ route('admin.orangtua.index') }}" class="btn btn-ghost btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Orang Tua</label>
                            <input type="text" name="ortu_nama" class="form-control" value="{{ old('ortu_nama', $orangTua->ortu_nama) }}" placeholder="Nama lengkap" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="ortu_username" class="form-control" value="{{ old('ortu_username', $orangTua->ortu_username) }}" placeholder="Contoh: orangtua1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password {{ $orangTua->exists ? '(opsional)' : '' }}</label>
                            <input type="password" name="password" class="form-control" placeholder="{{ $orangTua->exists ? 'Kosongkan jika tidak diubah' : 'Minimal 5 karakter' }}" {{ $orangTua->exists ? '' : 'required' }}>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="ortu_email" class="form-control" value="{{ old('ortu_email', $orangTua->ortu_email) }}" placeholder="email@contoh.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Telepon / WhatsApp</label>
                            <input type="text" name="ortu_telepon" class="form-control" value="{{ old('ortu_telepon', $orangTua->ortu_telepon) }}" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="glass-card card-pad">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Anak Terhubung</h3>
                            <p class="section-sub">Pilih satu atau beberapa peserta yang merupakan anak dari orang tua ini.</p>
                        </div>
                    </div>

                    <div style="max-height:420px;overflow:auto" class="d-flex flex-column gap-2 pe-1">
                        @forelse ($pesertas as $peserta)
                            <label class="answer-option" style="cursor:pointer">
                                <input class="form-check-input mt-1" type="checkbox" name="peserta[]" value="{{ $peserta->user_id }}" @checked(in_array((string) $peserta->user_id, $terpilih, true))>
                                <span>
                                    <span class="fw-semibold d-block" style="font-size:.84rem">{{ $peserta->nama }}</span>
                                    <span class="text-muted-2" style="font-size:.74rem">{{ $peserta->user_name }} &middot; {{ $peserta->grup?->grup_nama ?? 'Tanpa kelas' }}</span>
                                </span>
                            </label>
                        @empty
                            <div class="empty-state" style="padding:24px 12px">
                                <i class="bi bi-people"></i>
                                Belum ada peserta.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="glass-card card-pad mt-4">
                    <button class="btn btn-gradient w-100 mb-2"><i class="bi bi-save me-1"></i> {{ $orangTua->exists ? 'Simpan Perubahan' : 'Simpan Akun' }}</button>
                    <a href="{{ route('admin.orangtua.index') }}" class="btn btn-ghost w-100">Batal</a>
                </div>
            </div>
        </div>
    </form>
@endsection
