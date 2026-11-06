@extends('layouts.orangtua')

@section('title', 'Pembayaran Ujian')

@section('content')
    @if (! $anak)
        <div class="glass-card card-pad" data-aos="fade-up">
            <div class="empty-state">
                <i class="bi bi-person-x"></i>
                Akun Anda belum terhubung dengan data peserta. Silakan hubungi admin sekolah.
            </div>
        </div>
    @else
        @php $anakQuery = ['anak' => $anak->user_id]; @endphp

        <div class="stat-grid mb-4">
            <div class="stat-card" data-aos="fade-up">
                <div class="stat-icon bg-grad-1"><i class="bi bi-receipt"></i></div>
                <div class="stat-value" style="font-size:1.25rem">Rp {{ number_format($ringkasan['tagihan'], 0, ',', '.') }}</div>
                <div class="stat-label">Total Tagihan</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="40">
                <div class="stat-icon bg-grad-4"><i class="bi bi-patch-check"></i></div>
                <div class="stat-value" style="font-size:1.25rem">Rp {{ number_format($ringkasan['terverifikasi'], 0, ',', '.') }}</div>
                <div class="stat-label">Terverifikasi</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="80">
                <div class="stat-icon bg-grad-3"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-value">{{ $ringkasan['menunggu'] }}</div>
                <div class="stat-label">Menunggu Verifikasi</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="120">
                <div class="stat-icon bg-grad-2"><i class="bi bi-x-circle"></i></div>
                <div class="stat-value">{{ $ringkasan['ditolak'] }}</div>
                <div class="stat-label">Ditolak</div>
            </div>
        </div>

        @if ($rekening->isNotEmpty())
            <div class="glass-card card-pad mb-4" data-aos="fade-up">
                <div class="section-head">
                    <div>
                        <h3 class="section-title">Rekening Tujuan Transfer</h3>
                        <p class="section-sub">Transfer ke salah satu rekening sekolah berikut, lalu unggah bukti pembayaran.</p>
                    </div>
                    <span class="badge-soft"><i class="bi bi-bank2"></i> {{ $rekening->count() }} rekening</span>
                </div>
                @include('orangtua.partials.rekening', ['rekening' => $rekening])
            </div>
        @endif

        <div class="glass-card card-pad" data-aos="fade-up">
            <div class="section-head">
                <div>
                    <h3 class="section-title">Pembayaran &middot; {{ $anak->nama }}</h3>
                    <p class="section-sub">Transfer sesuai nominal, lalu unggah bukti pembayaran (JPG, PNG, atau PDF maksimal 4 MB). Admin akan memverifikasi.</p>
                </div>
                <span class="badge-soft"><i class="bi bi-mortarboard"></i> {{ $anak->grup?->grup_nama ?? '-' }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-modern align-middle">
                    <thead>
                        <tr>
                            <th>Ujian</th>
                            <th class="text-end">Nominal</th>
                            <th class="text-center">Status</th>
                            <th>Bukti / Catatan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tes as $item)
                            @php
                                $bayar = $pembayarans[$item->tes_id] ?? null;
                                $status = $bayar?->pembayaran_status;
                                $gratis = (float) $item->tes_biaya <= 0;
                            @endphp
                            <tr id="tes{{ $item->tes_id }}">
                                <td>
                                    <div class="fw-semibold">{{ $item->tes_nama }}</div>
                                    <div class="text-muted-2" style="font-size:.74rem">{{ $item->tes_begin_time?->translatedFormat('d M Y') }} &middot; {{ $item->tes_end_time?->translatedFormat('d M Y') }}</div>
                                </td>
                                <td class="text-end fw-800" style="color:#4338ca">
                                    {{ $gratis ? 'Gratis' : 'Rp ' . number_format($item->tes_biaya, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if ($gratis)
                                        <span class="badge-soft success">Gratis</span>
                                    @elseif ($status === \App\Models\Pembayaran::DIVERIFIKASI)
                                        <span class="badge-soft success"><i class="bi bi-check2-circle"></i> Terverifikasi</span>
                                    @elseif ($status === \App\Models\Pembayaran::MENUNGGU)
                                        <span class="badge-soft warning"><i class="bi bi-hourglass-split"></i> Menunggu</span>
                                    @elseif ($status === \App\Models\Pembayaran::DITOLAK)
                                        <span class="badge-soft danger"><i class="bi bi-x-circle"></i> Ditolak</span>
                                    @else
                                        <span class="badge-soft neutral">Belum dibayar</span>
                                    @endif
                                </td>
                                <td style="min-width:220px">
                                    @if ($bayar)
                                        <div class="d-flex flex-column gap-1">
                                            @if ($bayar->pembayaran_bukti)
                                                <a href="{{ route('orangtua.pembayaran.bukti', $bayar) }}" target="_blank" class="fw-semibold" style="font-size:.8rem;color:#4f46e5">
                                                    <i class="bi bi-paperclip"></i> Lihat bukti pembayaran
                                                </a>
                                            @endif
                                            @if ($bayar->pembayaran_catatan_admin)
                                                <div class="text-muted-2" style="font-size:.76rem">Catatan admin: {{ $bayar->pembayaran_catatan_admin }}</div>
                                            @endif
                                            @if ($bayar->pembayaran_verified_at)
                                                <div class="text-muted-2" style="font-size:.72rem">Diverifikasi {{ $bayar->pembayaran_verified_at->translatedFormat('d M Y H:i') }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted-2" style="font-size:.78rem">{{ $gratis ? 'Tidak perlu pembayaran.' : 'Belum ada bukti diunggah.' }}</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if (! $gratis && $status !== \App\Models\Pembayaran::DIVERIFIKASI)
                                        <button class="btn btn-gradient btn-sm" data-bs-toggle="modal" data-bs-target="#bayar{{ $item->tes_id }}">
                                            <i class="bi bi-upload me-1"></i> {{ $bayar ? 'Unggah Ulang' : 'Unggah Bukti' }}
                                        </button>
                                    @elseif ($status === \App\Models\Pembayaran::DIVERIFIKASI)
                                        <span class="badge-soft success">Lunas</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="bi bi-journal-x"></i>
                                        Belum ada ujian untuk kelas anak Anda.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @foreach ($tes as $item)
            @php $gratis = (float) $item->tes_biaya <= 0; @endphp
            @continue($gratis)
            <div class="modal fade" id="bayar{{ $item->tes_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form method="POST" action="{{ route('orangtua.pembayaran.store', $anakQuery) }}" enctype="multipart/form-data" class="modal-content">
                        @csrf
                        <input type="hidden" name="tes_id" value="{{ $item->tes_id }}">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title fw-bold">Unggah Bukti Pembayaran</h5>
                                <div class="text-muted-2" style="font-size:.78rem">{{ $item->tes_nama }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="p-3 mb-3" style="border-radius:14px;background:#eef2ff;border:1px solid #c7d2fe">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted-2" style="font-size:.8rem">Nominal yang harus dibayar</span>
                                    <span class="fw-800" style="font-size:1.1rem;color:#4338ca">Rp {{ number_format($item->tes_biaya, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            @if ($rekening->isNotEmpty())
                                <div class="mb-3">
                                    <div class="fw-semibold mb-2" style="font-size:.82rem">Transfer ke rekening sekolah:</div>
                                    @include('orangtua.partials.rekening', ['rekening' => $rekening])
                                </div>
                                <div class="alert alert-info d-flex align-items-start gap-2" style="font-size:.8rem">
                                    <i class="bi bi-info-circle-fill mt-1"></i>
                                    <div>Transfer sesuai nominal, lalu unggah foto/scan bukti transfer. Pastikan nominal dan nama pengirim terlihat jelas.</div>
                                </div>
                            @else
                                <div class="alert alert-info d-flex align-items-start gap-2" style="font-size:.8rem">
                                    <i class="bi bi-info-circle-fill mt-1"></i>
                                    <div>Silakan transfer sesuai nominal ke rekening sekolah, kemudian unggah foto/scan bukti transfer. Hubungi admin sekolah jika informasi rekening belum tersedia.</div>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Bukti Pembayaran <span class="text-danger">*</span></label>
                                <input type="file" name="bukti" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                <div class="text-muted-2 mt-1" style="font-size:.74rem">Format JPG, JPEG, PNG, atau PDF. Maksimal 4 MB.</div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label">Catatan untuk Admin</label>
                                <textarea name="catatan" class="form-control" rows="2" maxlength="500" placeholder="Contoh: transfer atas nama orang tua..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-gradient"><i class="bi bi-upload me-1"></i> Kirim Bukti</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif
@endsection
