<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrangTua\Concerns\ResolvesAnak;
use App\Models\Pembayaran;
use App\Models\SchoolPaymentMethod;
use App\Models\Tes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    use ResolvesAnak;

    public function index()
    {
        $orangTua = auth('orangtua')->user();
        [$anak, $daftarAnak] = $this->anakAktif($orangTua);

        if (! $anak) {
            return view('orangtua.pembayaran', compact('orangTua', 'daftarAnak', 'anak'));
        }

        $rekening = $this->rekeningForSchool($anak->user_school_id ?: $anak->grup?->grup_school_id);

        $tes = Tes::whereHas('groups', fn ($q) => $q->where('grup_id', $anak->user_grup_id))
            ->orderByDesc('tes_begin_time')
            ->get();

        $pembayarans = $anak->pembayarans()->get()->keyBy('pembayaran_tes_id');

        $ringkasan = [
            'tagihan' => (float) $tes->where('tes_biaya', '>', 0)->sum('tes_biaya'),
            'terverifikasi' => (float) $pembayarans->where('pembayaran_status', Pembayaran::DIVERIFIKASI)->sum('pembayaran_nominal'),
            'menunggu' => $pembayarans->where('pembayaran_status', Pembayaran::MENUNGGU)->count(),
            'ditolak' => $pembayarans->where('pembayaran_status', Pembayaran::DITOLAK)->count(),
        ];

        return view('orangtua.pembayaran', compact('orangTua', 'daftarAnak', 'anak', 'tes', 'pembayarans', 'ringkasan', 'rekening'));
    }

    public function store(Request $request)
    {
        $orangTua = auth('orangtua')->user();
        [$anak] = $this->anakAktif($orangTua);
        abort_unless($anak, 404);

        $data = $request->validate([
            'tes_id' => ['required', 'integer'],
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $tes = Tes::whereHas('groups', fn ($q) => $q->where('grup_id', $anak->user_grup_id))
            ->where('tes_id', $data['tes_id'])
            ->firstOrFail();

        if ((float) $tes->tes_biaya <= 0) {
            return back()->with('error', 'Tes ini tidak memiliki biaya pembayaran.');
        }

        $existing = Pembayaran::where('pembayaran_user_id', $anak->user_id)
            ->where('pembayaran_tes_id', $tes->tes_id)
            ->first();

        if ($existing && $existing->pembayaran_status === Pembayaran::DIVERIFIKASI) {
            return back()->with('error', 'Pembayaran tes ini sudah diverifikasi dan tidak dapat diubah.');
        }

        $path = $request->file('bukti')->store('bukti-pembayaran', 'public');

        if ($existing && $existing->pembayaran_bukti && $existing->pembayaran_bukti !== $path) {
            Storage::disk('public')->delete($existing->pembayaran_bukti);
        }

        Pembayaran::updateOrCreate(
            [
                'pembayaran_user_id' => $anak->user_id,
                'pembayaran_tes_id' => $tes->tes_id,
            ],
            [
                'pembayaran_ortu_id' => $orangTua->ortu_id,
                'pembayaran_foundation_id' => $anak->user_foundation_id,
                'pembayaran_school_id' => $anak->user_school_id,
                'pembayaran_nominal' => $tes->tes_biaya,
                'pembayaran_status' => Pembayaran::MENUNGGU,
                'pembayaran_bukti' => $path,
                'pembayaran_catatan' => $data['catatan'] ?? null,
                'pembayaran_catatan_admin' => null,
                'pembayaran_verified_by' => null,
                'pembayaran_verified_at' => null,
            ]
        );

        return redirect()
            ->route('orangtua.pembayaran.index', ['anak' => $anak->user_id])
            ->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
    }

    public function bukti(Pembayaran $pembayaran)
    {
        $orangTua = auth('orangtua')->user();

        $milikAnak = $orangTua->pesertas()
            ->where('cbt_user.user_id', $pembayaran->pembayaran_user_id)
            ->exists();

        abort_unless($milikAnak, 403);
        abort_unless($pembayaran->pembayaran_bukti && Storage::disk('public')->exists($pembayaran->pembayaran_bukti), 404);

        return Storage::disk('public')->response($pembayaran->pembayaran_bukti);
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
