<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peserta;
use App\Models\TesUser;
use App\Models\UserGroup;
use App\Support\PrestasiKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PesertaController extends Controller
{
    public function index(Request $request)
    {
        $grups = UserGroup::orderBy('grup_jenjang')
            ->orderBy('grup_tingkat')
            ->orderBy('grup_nama')
            ->get();

        $pesertas = Peserta::with('grup')
            ->when($request->filled('grup'), fn ($q) => $q->where('user_grup_id', $request->grup))
            ->when($request->filled('jenjang'), fn ($q) => $q->whereHas('grup', fn ($grup) => $grup->where('grup_jenjang', $request->jenjang)))
            ->when($request->filled('tingkat'), fn ($q) => $q->whereHas('grup', fn ($grup) => $grup->where('grup_tingkat', $request->tingkat)))
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('user_name', 'like', '%' . $request->q . '%')
                        ->orWhere('user_firstname', 'like', '%' . $request->q . '%')
                        ->orWhere('user_email', 'like', '%' . $request->q . '%');
                });
            })
            ->orderBy('user_firstname')
            ->paginate(10)
            ->withQueryString();

        $chartPrestasi = PrestasiKelas::perKelas();

        return view('admin.peserta.index', compact('grups', 'pesertas', 'chartPrestasi'));
    }

    public function create()
    {
        return view('admin.peserta.form', [
            'peserta' => new Peserta(['user_level' => 1]),
            'grups' => UserGroup::orderBy('grup_nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_grup_id' => ['required', 'exists:cbt_user_grup,grup_id'],
            'user_name' => ['required', 'string', 'max:255', 'unique:cbt_user,user_name'],
            'user_password' => ['required', 'string', 'min:6', 'max:255'],
            'user_firstname' => ['nullable', 'string', 'max:255'],
            'user_email' => ['nullable', 'email', 'max:255'],
            'user_birthdate' => ['nullable', 'date'],
            'user_birthplace' => ['nullable', 'string', 'max:255'],
            'user_detail' => ['nullable', 'string', 'max:25'],
        ]);

        $data['user_password'] = Hash::make($data['user_password']);
        $data['user_level'] = 1;
        $data['user_regdate'] = now();

        Peserta::create($data);

        return redirect()->route('admin.peserta.index')->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function edit(Peserta $peserta)
    {
        return view('admin.peserta.form', [
            'peserta' => $peserta,
            'grups' => UserGroup::orderBy('grup_nama')->get(),
        ]);
    }

    public function update(Request $request, Peserta $peserta)
    {
        $data = $request->validate([
            'user_grup_id' => ['required', 'exists:cbt_user_grup,grup_id'],
            'user_name' => ['required', 'string', 'max:255', 'unique:cbt_user,user_name,' . $peserta->user_id . ',user_id'],
            'user_password' => ['nullable', 'string', 'min:6', 'max:255'],
            'user_firstname' => ['nullable', 'string', 'max:255'],
            'user_email' => ['nullable', 'email', 'max:255'],
            'user_birthdate' => ['nullable', 'date'],
            'user_birthplace' => ['nullable', 'string', 'max:255'],
            'user_detail' => ['nullable', 'string', 'max:25'],
        ]);

        if (! empty($data['user_password'])) {
            $data['user_password'] = Hash::make($data['user_password']);
        } else {
            unset($data['user_password']);
        }

        $peserta->update($data);

        return redirect()->route('admin.peserta.index')->with('success', 'Data peserta berhasil diperbarui.');
    }

    public function destroy(Peserta $peserta)
    {
        if (TesUser::where('tesuser_user_id', $peserta->user_id)->exists()) {
            return back()->with('error', 'Peserta sudah memiliki riwayat ujian sehingga tidak dapat dihapus.');
        }

        DB::transaction(function () use ($peserta) {
            $peserta->orangTuas()->detach();

            foreach ($peserta->pembayarans as $pembayaran) {
                if ($pembayaran->pembayaran_bukti) {
                    Storage::disk('public')->delete($pembayaran->pembayaran_bukti);
                }

                $pembayaran->delete();
            }

            $peserta->absensis()->delete();
            $peserta->delete();
        });

        return back()->with('success', 'Peserta berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $header = array_map(
            fn ($item) => strtolower(trim(str_replace("\xEF\xBB\xBF", '', (string) $item))),
            $header ?: []
        );

        $map = array_flip($header);
        $wajib = ['user_name', 'user_password', 'user_firstname', 'grup_nama'];

        foreach ($wajib as $kolom) {
            if (! isset($map[$kolom])) {
                fclose($handle);

                return back()->with('error', "Kolom {$kolom} tidak ditemukan pada file CSV.");
            }
        }

        $berhasil = 0;
        $diperbarui = 0;

        DB::transaction(function () use ($handle, $map, &$berhasil, &$diperbarui) {
            while (($row = fgetcsv($handle)) !== false) {
                if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }

                $namaGrup = trim((string) ($row[$map['grup_nama']] ?? ''));
                if ($namaGrup === '') {
                    continue;
                }

                $grup = UserGroup::firstOrCreate(['grup_nama' => $namaGrup]);

                if ($grup->wasRecentlyCreated) {
                    [$jenjang, $tingkat] = UserGroup::tebakJenjangTingkat($namaGrup);
                    $grup->update(['grup_jenjang' => $jenjang, 'grup_tingkat' => $tingkat]);
                }

                $username = trim((string) ($row[$map['user_name']] ?? ''));

                if ($username === '') {
                    continue;
                }

                $payload = [
                    'user_grup_id' => $grup->grup_id,
                    'user_password' => Hash::make(trim((string) ($row[$map['user_password']] ?? '123456'))),
                    'user_firstname' => trim((string) ($row[$map['user_firstname']] ?? '')) ?: $username,
                    'user_email' => isset($map['user_email']) ? trim((string) ($row[$map['user_email']] ?? '')) : null,
                    'user_detail' => isset($map['user_detail']) ? trim((string) ($row[$map['user_detail']] ?? '')) : null,
                    'user_level' => 1,
                    'user_regdate' => now(),
                ];

                $peserta = Peserta::where('user_name', $username)->first();

                if ($peserta) {
                    $peserta->update($payload);
                    $diperbarui++;
                } else {
                    Peserta::create($payload + ['user_name' => $username]);
                    $berhasil++;
                }
            }
        });

        fclose($handle);

        return back()->with('success', "Import selesai: {$berhasil} peserta baru, {$diperbarui} diperbarui.");
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['user_name', 'user_password', 'user_firstname', 'grup_nama', 'user_email', 'user_detail']);
            fputcsv($out, ['siswa01', '123456', 'Nama Siswa', 'XI MM', 'siswa@example.com', 'Ruang 1, Sesi 1']);
            fclose($out);
        }, 'template-import-peserta.csv');
    }
}
