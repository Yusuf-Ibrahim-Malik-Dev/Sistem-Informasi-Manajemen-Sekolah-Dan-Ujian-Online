<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Jawaban;
use App\Models\Modul;
use App\Models\Soal;
use App\Models\TesSoal;
use App\Models\TesSoalJawaban;
use App\Models\TesTopikSet;
use App\Models\Topik;
use App\Support\HtmlText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SoalController extends Controller
{
    public function index(Request $request)
    {
        $moduls = Modul::orderBy('modul_nama')->get();
        $gurus = Admin::orderBy('nama')->get();
        $tipeUjian = Topik::tipeUjianOptions();

        $topiks = Topik::with([
            'modul',
            'guru:id,nama',
            'soals' => fn ($query) => $query->select('soal_id', 'soal_topik_id', 'soal_creator_id'),
            'soals.creator:id,nama',
        ])
            ->withCount('soals')
            ->when($request->filled('modul'), fn ($q) => $q->where('topik_modul_id', $request->modul))
            ->when($request->filled('tipe'), fn ($q) => $q->where('topik_tipe_ujian', $request->tipe))
            ->when($request->filled('status'), fn ($q) => $q->where('topik_aktif', $request->status === 'aktif'))
            ->when($request->filled('q'), fn ($q) => $q->where('topik_nama', 'like', '%' . $request->q . '%'))
            ->orderBy('topik_nama')
            ->paginate(10)
            ->withQueryString();

        $totalTopik = Topik::count();
        $totalSoal = Soal::count();
        $topikImport = $this->topikMilikUser();

        return view('admin.soal.index', compact('topiks', 'moduls', 'gurus', 'tipeUjian', 'totalTopik', 'totalSoal', 'topikImport'));
    }

    public function show(Topik $topik)
    {
        $topik->load(['modul', 'guru']);

        $soals = $topik->soals()
            ->with('creator')
            ->withCount('jawabans')
            ->orderBy('soal_id')
            ->get();

        return view('admin.soal.show', compact('topik', 'soals'));
    }

    public function storeTopik(Request $request)
    {
        $data = $this->validateTopik($request);

        Topik::create([
            'topik_modul_id' => $data['topik_modul_id'],
            'topik_nama' => $data['topik_nama'],
            'topik_tipe_ujian' => $data['topik_tipe_ujian'],
            'topik_guru_id' => $data['topik_guru_id'],
            'topik_detail' => $data['topik_detail'] ?? null,
            'topik_aktif' => $request->boolean('topik_aktif'),
        ]);

        return back()->with('success', 'Grup soal berhasil ditambahkan.');
    }

    public function updateTopik(Request $request, Topik $topik)
    {
        if ($redirect = $this->pastikanBolehKelola($topik)) {
            return $redirect;
        }

        $data = $this->validateTopik($request);

        $topik->update([
            'topik_modul_id' => $data['topik_modul_id'],
            'topik_nama' => $data['topik_nama'],
            'topik_tipe_ujian' => $data['topik_tipe_ujian'],
            'topik_guru_id' => $data['topik_guru_id'],
            'topik_detail' => $data['topik_detail'] ?? null,
            'topik_aktif' => $request->boolean('topik_aktif'),
        ]);

        return back()->with('success', 'Grup soal berhasil diperbarui.');
    }

    public function destroyTopik(Topik $topik)
    {
        if ($redirect = $this->pastikanBolehKelola($topik)) {
            return $redirect;
        }

        $soalIds = $topik->soals()->pluck('soal_id');

        if (TesSoal::whereIn('tessoal_soal_id', $soalIds)->exists()) {
            return back()->with('error', 'Grup soal tidak dapat dihapus karena soalnya sudah dipakai pada ujian. Hapus soal dari ujian terlebih dahulu.');
        }

        DB::transaction(function () use ($topik, $soalIds) {
            Jawaban::whereIn('jawaban_soal_id', $soalIds)->delete();
            Soal::whereIn('soal_id', $soalIds)->delete();
            TesTopikSet::where('tset_topik_id', $topik->topik_id)->delete();
            $topik->delete();
        });

        return redirect()->route('admin.soal.index')->with('success', 'Grup soal berhasil dihapus.');
    }

    private function validateTopik(Request $request): array
    {
        return $request->validate([
            'topik_modul_id' => ['required', 'exists:cbt_modul,modul_id'],
            'topik_nama' => ['required', 'string', 'max:255'],
            'topik_guru_id' => ['required', 'exists:user,id'],
            'topik_tipe_ujian' => ['required', Rule::in(array_keys(Topik::tipeUjianOptions()))],
            'topik_detail' => ['nullable', 'string'],
            'topik_aktif' => ['nullable', 'boolean'],
        ]);
    }

    public function create(Request $request)
    {
        if ($request->filled('topik')) {
            $topik = Topik::findOrFail($request->integer('topik'));
            if ($redirect = $this->pastikanBolehKelola($topik)) {
                return $redirect;
            }
        }

        $soal = new Soal([
            'soal_tipe' => 1,
            'soal_difficulty' => 1,
            'soal_aktif' => true,
            'soal_topik_id' => $request->integer('topik'),
        ]);

        return view('admin.soal.form', [
            'soal' => $soal,
            'topiks' => $this->topikMilikUser(),
            'jawabans' => collect(),
        ]);
    }

    public function template(Request $request): StreamedResponse
    {
        $format = strtolower((string) $request->query('format', 'csv'));

        if (! in_array($format, ['csv', 'xlsx'], true)) {
            $format = 'csv';
        }

        $headers = [
            'soal_detail', 'soal_tipe', 'soal_difficulty', 'soal_kunci',
            'jawaban_a', 'jawaban_b', 'jawaban_c', 'jawaban_d', 'jawaban_e',
            'jawaban_benar', 'soal_aktif',
        ];

        $contoh = [
            [
                'Ibu kota Indonesia adalah ...', 'pg', '1', '',
                'Jakarta', 'Bandung', 'Surabaya', 'Medan', '',
                'A', '1',
            ],
            [
                'Jelaskan proses terjadinya hujan.', 'essay', '2', '',
                '', '', '', '', '',
                '', '1',
            ],
            [
                'Hasil dari 5 + 6 adalah ...', 'singkat', '1', '11',
                '', '', '', '', '',
                '', '1',
            ],
        ];

        if ($format === 'xlsx') {
            return response()->streamDownload(function () use ($headers, $contoh) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->fromArray(array_merge([$headers], $contoh));
                $sheet->getStyle('A1:K1')->getFont()->setBold(true);

                foreach (range('A', 'K') as $kolom) {
                    $sheet->getColumnDimension($kolom)->setAutoSize(true);
                }

                (new Xlsx($spreadsheet))->save('php://output');
                $spreadsheet->disconnectWorksheets();
            }, 'template-import-soal.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return response()->streamDownload(function () use ($headers, $contoh) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);

            foreach ($contoh as $baris) {
                fputcsv($out, $baris);
            }

            fclose($out);
        }, 'template-import-soal.csv');
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'topik_id' => ['required', 'exists:cbt_topik,topik_id'],
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ]);

        $topik = Topik::findOrFail($data['topik_id']);

        if ($redirect = $this->pastikanBolehKelola($topik)) {
            return $redirect;
        }

        $rows = $this->bacaBarisImport($request->file('file'));

        if ($rows === []) {
            return back()->with('error', 'File import kosong atau tidak dapat dibaca.');
        }

        $header = array_map(
            fn ($item) => strtolower(trim(str_replace("\xEF\xBB\xBF", '', (string) $item))),
            array_shift($rows)
        );

        $map = array_flip($header);

        foreach (['soal_detail', 'soal_tipe'] as $kolom) {
            if (! isset($map[$kolom])) {
                return back()->with('error', "Kolom {$kolom} tidak ditemukan pada file import.");
            }
        }

        $berhasil = 0;
        $errors = [];
        $nomor = 1;

        DB::transaction(function () use ($rows, $map, $topik, &$berhasil, &$errors, &$nomor) {
            foreach ($rows as $row) {
                $nomor++;

                if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }

                $get = fn (string $kolom) => isset($map[$kolom])
                    ? trim((string) ($row[$map[$kolom]] ?? ''))
                    : '';

                $detail = HtmlText::plain($get('soal_detail'));

                if ($detail === '') {
                    $errors[] = "Baris {$nomor}: isi soal kosong.";
                    continue;
                }

                $tipe = $this->parseTipeSoal($get('soal_tipe'));

                if ($tipe === null) {
                    $errors[] = "Baris {$nomor}: tipe soal tidak dikenali (gunakan pg, essay, atau singkat).";
                    continue;
                }

                $difficulty = $get('soal_difficulty') === '' ? 1 : (int) $get('soal_difficulty');

                if ($difficulty < 1 || $difficulty > 5) {
                    $errors[] = "Baris {$nomor}: tingkat kesulitan harus antara 1 sampai 5.";
                    continue;
                }

                $kunci = HtmlText::plain($get('soal_kunci'));
                $pilihan = [];

                foreach (['a', 'b', 'c', 'd', 'e'] as $huruf) {
                    $isi = HtmlText::plain($get('jawaban_' . $huruf));

                    if ($isi !== '') {
                        $pilihan[$huruf] = $isi;
                    }
                }

                $kunciPilihan = null;

                if ($tipe === Soal::TIPE_GANDA) {
                    if (count($pilihan) < 2) {
                        $errors[] = "Baris {$nomor}: soal pilihan ganda minimal punya dua jawaban (jawaban_a sampai jawaban_e).";
                        continue;
                    }

                    $kunciPilihan = $this->parseKunciPilihan($get('jawaban_benar'), array_keys($pilihan));

                    if ($kunciPilihan === null) {
                        $errors[] = "Baris {$nomor}: jawaban_benar harus huruf A-E yang sesuai dengan pilihan yang diisi.";
                        continue;
                    }
                }

                if ($tipe === Soal::TIPE_SINGKAT && $kunci === '') {
                    $errors[] = "Baris {$nomor}: soal jawaban singkat wajib punya soal_kunci.";
                    continue;
                }

                $aktif = ! in_array(strtolower($get('soal_aktif')), ['0', 'false', 'tidak', 'no', 'nonaktif'], true);

                $soal = Soal::create([
                    'soal_topik_id' => $topik->topik_id,
                    'soal_creator_id' => auth('web')->id(),
                    'soal_detail' => $detail,
                    'soal_tipe' => $tipe,
                    'soal_kunci' => $kunci,
                    'soal_difficulty' => $difficulty,
                    'soal_aktif' => $aktif,
                ]);

                if ($tipe === Soal::TIPE_GANDA) {
                    foreach ($pilihan as $huruf => $isi) {
                        $soal->jawabans()->create([
                            'jawaban_detail' => $isi,
                            'jawaban_benar' => $huruf === $kunciPilihan,
                            'jawaban_aktif' => true,
                        ]);
                    }
                }

                $berhasil++;
            }
        });

        if ($berhasil === 0) {
            return back()
                ->with('error', 'Import soal gagal. Tidak ada baris yang valid.')
                ->withErrors(array_slice($errors, 0, 20));
        }

        $pesan = "Import selesai: {$berhasil} soal ditambahkan ke grup {$topik->topik_nama}.";

        if ($errors !== []) {
            return back()
                ->with('success', $pesan)
                ->withErrors(array_slice($errors, 0, 20));
        }

        return back()->with('success', $pesan);
    }

    private function bacaBarisImport(UploadedFile $file): array
    {
        $ekstensi = strtolower($file->getClientOriginalExtension());

        if (in_array($ekstensi, ['xlsx', 'xls'], true)) {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            $spreadsheet->disconnectWorksheets();

            return $rows;
        }

        $handle = fopen($file->getRealPath(), 'r');
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function parseTipeSoal(string $nilai): ?int
    {
        return match (strtolower(trim($nilai))) {
            '1', 'pg', 'pilihan ganda', 'pilihan_ganda', 'ganda' => Soal::TIPE_GANDA,
            '2', 'essay', 'esai', 'uraian' => Soal::TIPE_ESSAY,
            '3', 'singkat', 'jawaban singkat', 'jawaban_singkat', 'isian' => Soal::TIPE_SINGKAT,
            default => null,
        };
    }

    private function parseKunciPilihan(string $nilai, array $pilihan): ?string
    {
        $nilai = strtolower(trim($nilai));

        if ($nilai === '') {
            return null;
        }

        if (in_array($nilai, $pilihan, true)) {
            return $nilai;
        }

        if (ctype_digit($nilai)) {
            $posisi = (int) $nilai;

            if ($posisi >= 1 && $posisi <= count($pilihan)) {
                return $pilihan[$posisi - 1];
            }
        }

        return null;
    }

    public function store(Request $request)
    {
        $this->validatePayload($request);

        if ($redirect = $this->pastikanBolehKelola(Topik::findOrFail($request->integer('soal_topik_id')))) {
            return $redirect;
        }

        DB::transaction(function () use ($request) {
            $soal = Soal::create($this->payload($request));

            if ((int) $soal->soal_tipe === Soal::TIPE_GANDA) {
                $this->syncJawaban($soal, $request);
            }
        });

        return redirect()->route('admin.soal.index')
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    public function edit(Soal $soal)
    {
        $soal->load('jawabans', 'topik');

        if ($redirect = $this->pastikanBolehKelola($soal->topik)) {
            return $redirect;
        }

        return view('admin.soal.form', [
            'soal' => $soal,
            'topiks' => $this->topikMilikUser(),
            'jawabans' => $soal->jawabans,
        ]);
    }

    public function update(Request $request, Soal $soal)
    {
        if ($redirect = $this->pastikanBolehKelola($soal->topik)) {
            return $redirect;
        }

        $this->validatePayload($request);

        if ($redirect = $this->pastikanBolehKelola(Topik::findOrFail($request->integer('soal_topik_id')))) {
            return $redirect;
        }

        DB::transaction(function () use ($request, $soal) {
            $payload = $this->payload($request, $soal);
            $soal->update($payload);

            if ((int) $soal->soal_tipe === Soal::TIPE_GANDA) {
                $this->syncJawaban($soal, $request);
            } else {
                $this->pastikanJawabanBolehDihapus($soal->jawabans()->pluck('jawaban_id'));
                $soal->jawabans()->delete();
            }
        });

        return redirect()->route('admin.soal.index')
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Soal $soal)
    {
        if ($redirect = $this->pastikanBolehKelola($soal->topik)) {
            return $redirect;
        }

        if (TesSoal::where('tessoal_soal_id', $soal->soal_id)->exists()) {
            return back()->with('error', 'Soal sudah pernah dipakai pada ujian sehingga tidak dapat dihapus. Nonaktifkan saja soal ini.');
        }

        DB::transaction(function () use ($soal) {
            $soal->jawabans()->delete();
            $soal->delete();
        });

        return back()->with('success', 'Soal berhasil dihapus.');
    }

    public function upload(Request $request)
    {
        $data = $request->validate([
            'topik_id' => ['required', 'exists:cbt_topik,topik_id'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp3,wav,ogg,m4a,mp4,webm', 'max:20480'],
        ]);

        $topik = Topik::findOrFail($data['topik_id']);

        if (! $topik->dapatDikelolaOleh(auth('web')->user())) {
            return response()->json(['error' => 'Grup soal ini dikelola oleh guru lain.'], 200);
        }

        $folder = 'uploads/topik_' . $data['topik_id'];
        $directory = public_path($folder);
        File::ensureDirectoryExists($directory);

        $file = $request->file('file');
        $ekstensi = $file->extension() ?: $file->getClientOriginalExtension();
        $nama = time() . '_' . Str::random(8) . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            . '.' . strtolower($ekstensi);

        $file->move($directory, $nama);

        return response()->json([
            'url' => asset($folder . '/' . $nama),
            'path' => $folder . '/' . $nama,
            'name' => $nama,
        ]);
    }

    private function topikMilikUser()
    {
        return Topik::with('modul')
            ->where('topik_guru_id', auth('web')->id())
            ->orderBy('topik_nama')
            ->get();
    }

    private function pastikanBolehKelola(?Topik $topik, string $route = 'admin.soal.index'): ?RedirectResponse
    {
        if ($topik && $topik->dapatDikelolaOleh(auth('web')->user())) {
            return null;
        }

        return redirect()->route($route)->with(
            'popup_error',
            'Grup soal ini dikelola oleh guru lain. Anda hanya bisa melihat datanya, tanpa bisa menambah, mengubah, atau menghapus apa pun di dalamnya.'
        );
    }

    private function validatePayload(Request $request): void
    {
        $request->validate([
            'soal_topik_id' => ['required', 'exists:cbt_topik,topik_id'],
            'soal_detail' => ['required', 'string'],
            'soal_tipe' => ['required', 'in:1,2,3'],
            'soal_difficulty' => ['required', 'integer', 'min:1', 'max:5'],
            'soal_kunci' => ['nullable', 'string', 'max:255'],
            'soal_audio' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:20480'],
            'soal_audio_play' => ['nullable', 'boolean'],
            'soal_timer' => ['nullable', 'integer', 'min:0'],
            'soal_inline_answers' => ['nullable', 'boolean'],
            'soal_auto_next' => ['nullable', 'boolean'],
            'jawaban' => ['nullable', 'array'],
            'jawaban.*.detail' => ['nullable', 'string'],
            'jawaban_benar' => ['nullable', 'integer'],
        ]);

        if (HtmlText::plain($request->input('soal_detail')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages(['soal_detail' => 'Isi soal wajib diisi.']);
        }

        if ((int) $request->input('soal_tipe') === Soal::TIPE_GANDA) {
            $items = $this->normalizedAnswers($request);

            if ($items->count() < 2) {
                throw \Illuminate\Validation\ValidationException::withMessages(['jawaban' => 'Minimal isi dua pilihan jawaban.']);
            }

            if (! $items->pluck('index')->contains((int) $request->input('jawaban_benar'))) {
                throw \Illuminate\Validation\ValidationException::withMessages(['jawaban_benar' => 'Pilih satu jawaban yang benar.']);
            }
        }

        if ((int) $request->input('soal_tipe') === Soal::TIPE_SINGKAT && HtmlText::plain($request->input('soal_kunci')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages(['soal_kunci' => 'Kunci jawaban singkat wajib diisi.']);
        }
    }

    private function payload(Request $request, ?Soal $soal = null): array
    {
        $payload = [
            'soal_topik_id' => $request->integer('soal_topik_id'),
            'soal_detail' => HtmlText::plain($request->input('soal_detail')),
            'soal_tipe' => $request->integer('soal_tipe'),
            'soal_kunci' => HtmlText::plain($request->input('soal_kunci')),
            'soal_difficulty' => $request->integer('soal_difficulty'),
            'soal_aktif' => $request->boolean('soal_aktif'),
            'soal_audio_play' => $request->boolean('soal_audio_play'),
            'soal_timer' => $request->filled('soal_timer') ? $request->integer('soal_timer') : null,
            'soal_inline_answers' => $request->boolean('soal_inline_answers'),
            'soal_auto_next' => $request->boolean('soal_auto_next'),
        ];

        if (! $soal) {
            $payload['soal_creator_id'] = auth('web')->id();
        }

        if ($request->hasFile('soal_audio')) {
            $folder = 'uploads/topik_' . $request->integer('soal_topik_id');
            $directory = public_path($folder);
            File::ensureDirectoryExists($directory);

            $file = $request->file('soal_audio');
            $ekstensi = $file->extension() ?: $file->getClientOriginalExtension();
            $nama = time() . '_audio_' . Str::random(8) . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                . '.' . strtolower($ekstensi);
            $file->move($directory, $nama);
            $payload['soal_audio'] = $nama;
        } elseif ($request->boolean('hapus_audio')) {
            $payload['soal_audio'] = null;
        }

        return $payload;
    }

    private function normalizedAnswers(Request $request)
    {
        return collect($request->input('jawaban', []))
            ->map(function ($item, $index) {
                return [
                    'index' => (int) $index,
                    'id' => ! empty($item['id']) ? (int) $item['id'] : null,
                    'detail' => HtmlText::plain($item['detail'] ?? ''),
                ];
            })
            ->filter(fn ($item) => $item['detail'] !== '')
            ->values();
    }

    private function syncJawaban(Soal $soal, Request $request): void
    {
        $items = $this->normalizedAnswers($request);
        $benar = (int) $request->input('jawaban_benar');
        $kept = [];

        foreach ($items as $item) {
            $data = [
                'jawaban_detail' => $item['detail'],
                'jawaban_benar' => $item['index'] === $benar,
                'jawaban_aktif' => true,
            ];

            if ($item['id'] && $soal->jawabans()->whereKey($item['id'])->exists()) {
                $jawaban = Jawaban::find($item['id']);
                $jawaban->update($data);
                $kept[] = $jawaban->jawaban_id;
            } else {
                $jawaban = $soal->jawabans()->create($data);
                $kept[] = $jawaban->jawaban_id;
            }
        }

        $dihapus = $soal->jawabans()->whereNotIn('jawaban_id', $kept)->pluck('jawaban_id');

        $this->pastikanJawabanBolehDihapus($dihapus);

        $soal->jawabans()->whereIn('jawaban_id', $dihapus)->delete();
    }

    private function pastikanJawabanBolehDihapus($jawabanIds): void
    {
        if ($jawabanIds->isEmpty()) {
            return;
        }

        if (TesSoalJawaban::whereIn('soaljawaban_jawaban_id', $jawabanIds)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'jawaban' => 'Pilihan jawaban yang sudah dipakai pada ujian tidak dapat dihapus. Nonaktifkan saja soal ini.',
            ]);
        }
    }
}
