@extends('layouts.admin')

@section('title', 'Token Ujian')
@section('heading', 'Token Ujian')
@section('eyebrow', 'Manajemen Ujian')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4" data-aos="fade-up">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Buat Token</h3>
                        <p class="section-sub">Token berlaku pada tanggal pembuatan.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.token.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Untuk Tes</label>
                        <select name="token_tes_id" class="form-select" required>
                            <option value="0">Semua tes</option>
                            @foreach ($tes as $item)
                                <option value="{{ $item->tes_id }}" @selected(old('token_tes_id') == $item->tes_id)>{{ $item->tes_nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Isi Token</label>
                        <input type="text" name="token_isi" class="form-control" value="{{ old('token_isi') }}" placeholder="Kosongkan untuk acak 6 digit" maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Masa Berlaku</label>
                        <select name="token_aktif" class="form-select" required>
                            <option value="1" @selected(old('token_aktif') == 1)>Sepanjang hari</option>
                            <option value="15" @selected(old('token_aktif') == 15)>15 menit</option>
                            <option value="30" @selected(old('token_aktif') == 30)>30 menit</option>
                            <option value="60" @selected(old('token_aktif') == 60)>60 menit</option>
                            <option value="120" @selected(old('token_aktif') == 120)>120 menit</option>
                        </select>
                    </div>
                    <button class="btn btn-gradient w-100"><i class="bi bi-key me-1"></i> Generate Token</button>
                </form>

                <div class="mt-4 p-3" style="border-radius:14px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.18)">
                    <div class="d-flex gap-2">
                        <i class="bi bi-shield-lock text-warning"></i>
                        <div style="font-size:.78rem;color:#92400e">Bagikan token hanya kepada peserta yang berhak tepat sebelum ujian dimulai.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="60">
            <div class="glass-card card-pad">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Riwayat Token</h3>
                        <p class="section-sub">{{ $tokens->total() }} token tercatat.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Token</th>
                                <th>Tes</th>
                                <th>Masa Berlaku</th>
                                <th>Pemakaian</th>
                                <th>Dibuat</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tokens as $token)
                                @php
                                    $kedaluwarsa = ! $token->token_ts?->isToday()
                                        || ((int) $token->token_aktif !== 1 && $token->token_ts?->copy()->addMinutes((int) $token->token_aktif)->isPast());
                                @endphp
                                <tr>
                                    <td>
                                        <span class="fw-800" style="font-size:1.05rem;letter-spacing:.16em;color:#4338ca">{{ $token->token_isi }}</span>
                                    </td>
                                    <td style="font-size:.82rem">{{ $token->token_tes_id === 0 ? 'Semua tes' : ($token->tes?->tes_nama ?? '-') }}</td>
                                    <td>
                                        @if ($kedaluwarsa)
                                            <span class="badge-soft danger"><i class="bi bi-x-circle"></i> Kedaluwarsa</span>
                                        @else
                                            <span class="badge-soft success"><i class="bi bi-check-circle"></i> {{ (int) $token->token_aktif === 1 ? 'Aktif hari ini' : $token->token_aktif . ' menit' }}</span>
                                        @endif
                                    </td>
                                    <td><span class="badge-soft neutral">{{ $token->pemakaian }} peserta</span></td>
                                    <td class="text-muted-2" style="font-size:.78rem">
                                        {{ $token->token_ts?->translatedFormat('d M Y H:i') }}
                                        <div style="font-size:.7rem">{{ $token->user?->nama }}</div>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('admin.token.destroy', $token) }}" onsubmit="return confirm('Hapus token ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-ghost btn-icon text-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="bi bi-key"></i>
                                            Belum ada token yang dibuat.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $tokens->links() }}</div>
            </div>
        </div>
    </div>
@endsection
