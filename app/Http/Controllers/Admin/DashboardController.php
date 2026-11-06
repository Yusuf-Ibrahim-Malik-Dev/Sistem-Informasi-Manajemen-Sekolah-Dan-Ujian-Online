<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Konfigurasi;
use App\Models\Modul;
use App\Models\Peserta;
use App\Models\Soal;
use App\Models\Tes;
use App\Models\TesUser;
use App\Models\Topik;
use App\Support\SchoolContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index(SchoolContext $context)
    {
        $pesertaQuery = $context->apply(Peserta::query(), 'user_school_id', 'user_foundation_id');
        $tesQuery = $context->apply(Tes::query(), 'tes_school_id', 'tes_foundation_id');
        $attemptQuery = TesUser::whereHas('tes', fn ($query) => $context->apply($query, 'tes_school_id', 'tes_foundation_id'));

        $stats = [
            'modul' => Modul::count(),
            'topik' => Topik::count(),
            'soal' => Soal::count(),
            'peserta' => (clone $pesertaQuery)->count(),
            'tes' => (clone $tesQuery)->count(),
            'selesai' => (clone $attemptQuery)->where('tesuser_status', TesUser::STATUS_SELESAI)->count(),
        ];

        $chart = collect(range(6, 0))->map(function ($i) use ($attemptQuery) {
            $tanggal = now()->subDays($i);

            return [
                'label' => $tanggal->translatedFormat('d M'),
                'total' => (clone $attemptQuery)->whereDate('tesuser_creation_time', $tanggal->toDateString())->count(),
            ];
        });

        $nilaiRata = (clone $attemptQuery)->where('tesuser_status', TesUser::STATUS_SELESAI)
            ->withSum('soals as total_nilai', 'tessoal_nilai')
            ->get()
            ->avg('total_nilai') ?? 0;

        $terbaru = (clone $attemptQuery)->with(['tes', 'peserta.grup'])
            ->withSum('soals as total_nilai', 'tessoal_nilai')
            ->where('tesuser_status', TesUser::STATUS_SELESAI)
            ->latest('tesuser_creation_time')
            ->take(6)
            ->get();

        $tesAktif = (clone $tesQuery)->withCount('tesUsers')->openNow()->latest('tes_begin_time')->take(4)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'chart' => $chart,
            'nilaiRata' => round($nilaiRata, 2),
            'terbaru' => $terbaru,
            'tesAktif' => $tesAktif,
            'namaCbt' => Konfigurasi::get('cbt_nama', 'CBT Online'),
        ]);
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $admin = $request->user('web');

        if (! Hash::check($data['password_lama'], $admin->password)) {
            return back()->with('error', 'Password lama tidak sesuai.');
        }

        $admin->update(['password' => Hash::make($data['password_baru'])]);
        ActivityLog::record($admin->username, 'Mengubah password administrator');

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
