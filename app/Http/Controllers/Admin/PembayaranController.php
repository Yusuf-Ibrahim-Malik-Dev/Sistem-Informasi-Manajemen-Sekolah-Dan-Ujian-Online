<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Pembayaran;
use App\Models\Tes;
use App\Support\SchoolContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PembayaranController extends Controller
{
    public function index(Request $request, SchoolContext $context)
    {
        $base = $this->scopedPayments($context)
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereHas('peserta', fn ($p) => $p
                        ->where('user_firstname', 'like', $term)
                        ->orWhere('user_name', 'like', $term))
                        ->orWhereHas('orangTua', fn ($o) => $o
                            ->where('ortu_nama', 'like', $term)
                            ->orWhere('ortu_username', 'like', $term));
                });
            })
            ->when($request->filled('tes'), fn ($q) => $q->where('pembayaran_tes_id', $request->tes));

        $statistik = [
            'menunggu' => (clone $base)->where('pembayaran_status', Pembayaran::MENUNGGU)->count(),
            'diverifikasi' => (clone $base)->where('pembayaran_status', Pembayaran::DIVERIFIKASI)->count(),
            'ditolak' => (clone $base)->where('pembayaran_status', Pembayaran::DITOLAK)->count(),
            'nilai_verifikasi' => (float) (clone $base)->where('pembayaran_status', Pembayaran::DIVERIFIKASI)->sum('pembayaran_nominal'),
        ];

        $daftar = (clone $base)
            ->with(['peserta.grup', 'tes', 'orangTua', 'verifikator'])
            ->when($request->filled('status'), fn ($q) => $q->where('pembayaran_status', $request->status))
            ->orderByRaw("CASE pembayaran_status WHEN 'menunggu' THEN 0 WHEN 'ditolak' THEN 1 ELSE 2 END")
            ->orderByDesc('pembayaran_id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pembayaran.index', [
            'daftar' => $daftar,
            'statistik' => $statistik,
            'tes' => $context->apply(Tes::query(), 'tes_school_id', 'tes_foundation_id')->orderByDesc('tes_begin_time')->get(),
        ]);
    }

    public function verifikasi(Request $request, Pembayaran $pembayaran, SchoolContext $context)
    {
        $pembayaran = $this->scopedPayments($context)
            ->whereKey($pembayaran->getKey())
            ->firstOrFail();
        $data = $request->validate([
            'aksi' => ['required', Rule::in(['verifikasi', 'tolak'])],
            'catatan_admin' => ['nullable', 'string', 'max:500', 'required_if:aksi,tolak'],
        ]);

        abort_unless($pembayaran->pembayaran_status === Pembayaran::MENUNGGU, 409, 'Pembayaran ini sudah diproses.');

        DB::transaction(function () use ($pembayaran, $data) {
            $locked = Pembayaran::query()->whereKey($pembayaran->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($locked->pembayaran_status === Pembayaran::MENUNGGU, 409, 'Pembayaran ini sudah diproses.');

            $locked->update([
                'pembayaran_status' => $data['aksi'] === 'verifikasi' ? Pembayaran::DIVERIFIKASI : Pembayaran::DITOLAK,
                'pembayaran_catatan_admin' => $data['catatan_admin'] ?? null,
                'pembayaran_verified_by' => auth('web')->id(),
                'pembayaran_verified_at' => now(),
            ]);

            ActivityLog::record(
                auth('web')->user()->username,
                ($data['aksi'] === 'verifikasi' ? 'Verifikasi' : 'Penolakan')
                    .' pembayaran CBT #'.$locked->getKey()
            );
        });

        return back()->with('success', $data['aksi'] === 'verifikasi'
            ? 'Pembayaran berhasil diverifikasi.'
            : 'Pembayaran ditolak. Orang tua dapat mengunggah ulang bukti.');
    }

    public function bukti(Pembayaran $pembayaran, SchoolContext $context)
    {
        $pembayaran = $this->scopedPayments($context)
            ->whereKey($pembayaran->getKey())
            ->firstOrFail();

        abort_unless($pembayaran->pembayaran_bukti && Storage::disk('public')->exists($pembayaran->pembayaran_bukti), 404);

        return Storage::disk('public')->response($pembayaran->pembayaran_bukti);
    }

    /**
     * Tenant isolation uses the student's current school/foundation as the
     * authoritative fallback for older payment rows without tenant columns.
     */
    private function scopedPayments(SchoolContext $context)
    {
        return Pembayaran::query()->whereHas('peserta', function ($query) use ($context) {
            if ($context->schoolId()) {
                $query->where('user_school_id', $context->schoolId());
            } elseif ($context->foundationId()) {
                $query->where('user_foundation_id', $context->foundationId());
            }
        });
    }
}
