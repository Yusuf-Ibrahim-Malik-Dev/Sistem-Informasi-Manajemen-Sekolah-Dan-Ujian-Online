@extends('layouts.admin')

@section('title', $peserta->exists ? 'Edit Peserta' : 'Tambah Peserta')
@section('heading', $peserta->exists ? 'Edit Peserta' : 'Tambah Peserta')
@section('eyebrow', 'Data Peserta')

@section('content')
    <form method="POST" action="{{ $peserta->exists ? route('admin.peserta.update', $peserta) : route('admin.peserta.store') }}" data-aos="fade-up">
        @csrf
        @if ($peserta->exists)
            @method('PUT')
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-card card-pad">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Informasi Peserta</h3>
                            <p class="section-sub">Data ini digunakan peserta untuk masuk ke aplikasi ujian.</p>
                        </div>
                        <a href="{{ route('admin.peserta.index') }}" class="btn btn-ghost btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="user_firstname" class="form-control" value="{{ old('user_firstname', $peserta->user_firstname) }}" placeholder="Nama peserta" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" name="user_name" class="form-control" value="{{ old('user_name', $peserta->user_name) }}" placeholder="Username unik" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password {{ $peserta->exists ? '(kosongkan jika tidak diubah)' : '' }}</label>
                            <input type="password" name="user_password" class="form-control" placeholder="Minimal 3 karakter" @required(! $peserta->exists)>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Group</label>
                            <select name="user_grup_id" class="form-select" required>
                                <option value="">Pilih group</option>
                                @foreach ($grups as $grup)
                                    <option value="{{ $grup->grup_id }}" @selected(old('user_grup_id', $peserta->user_grup_id) == $grup->grup_id)>{{ $grup->grup_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="user_email" class="form-control" value="{{ old('user_email', $peserta->user_email) }}" placeholder="email@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ruang / Sesi</label>
                            <input type="text" name="user_detail" class="form-control" value="{{ old('user_detail', $peserta->user_detail) }}" placeholder="Contoh: Ruang 1, Sesi 1" maxlength="25">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="user_birthplace" class="form-control" value="{{ old('user_birthplace', $peserta->user_birthplace) }}" placeholder="Kota kelahiran">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="user_birthdate" class="form-control" value="{{ old('user_birthdate', $peserta->user_birthdate ? \Illuminate\Support\Carbon::parse($peserta->user_birthdate)->format('Y-m-d') : '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card card-pad">
                    <div class="section-head">
                        <div>
                            <h3 class="section-title">Simpan</h3>
                            <p class="section-sub">Pastikan data sudah benar.</p>
                        </div>
                    </div>
                    <button class="btn btn-gradient w-100 mb-2"><i class="bi bi-save me-1"></i> {{ $peserta->exists ? 'Simpan Perubahan' : 'Simpan Peserta' }}</button>
                    <a href="{{ route('admin.peserta.index') }}" class="btn btn-ghost w-100">Batal</a>

                    @if (! $peserta->exists)
                        <div class="mt-4 p-3" style="border-radius:14px;background:rgba(99,102,241,.07);border:1px solid rgba(99,102,241,.14);font-size:.78rem;color:#475569">
                            <i class="bi bi-info-circle me-1 text-primary"></i>
                            Password akan disimpan dengan enkripsi bcrypt.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>
@endsection
