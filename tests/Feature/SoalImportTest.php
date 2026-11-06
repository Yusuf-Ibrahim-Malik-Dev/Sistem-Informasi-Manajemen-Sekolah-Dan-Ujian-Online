<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Soal;
use App\Models\Topik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class SoalImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_halaman_soal_menampilkan_tombol_import_dan_template(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $topik = Topik::where('topik_guru_id', $admin->id)->firstOrFail();

        $this->actingAs($admin, 'web');

        $this->get('/admin/soal')
            ->assertOk()
            ->assertSee('Import Soal')
            ->assertSee('Template CSV')
            ->assertSee('Template Excel')
            ->assertSee('Grup Soal Tujuan');

        $this->get(route('admin.soal.show', $topik))
            ->assertOk()
            ->assertSee('Import Soal')
            ->assertSee('Template CSV')
            ->assertSee('Template Excel');
    }

    public function test_admin_dapat_mengunduh_template_soal(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $this->actingAs($admin, 'web');

        $this->get(route('admin.soal.template'))
            ->assertOk()
            ->assertDownload('template-import-soal.csv');

        $this->get(route('admin.soal.template', ['format' => 'xlsx']))
            ->assertOk()
            ->assertDownload('template-import-soal.xlsx');
    }

    public function test_admin_dapat_mengimport_soal_dari_csv(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $topik = Topik::where('topik_guru_id', $admin->id)->firstOrFail();
        $sebelum = $topik->soals()->count();

        $this->actingAs($admin, 'web');

        $this->post(route('admin.soal.import'), [
            'topik_id' => $topik->topik_id,
            'file' => UploadedFile::fake()->createWithContent('soal.csv', $this->csvContoh()),
        ])->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame($sebelum + 3, $topik->soals()->count());

        $pg = Soal::where('soal_detail', 'Ibu kota Indonesia adalah ...')->firstOrFail();
        $this->assertSame(Soal::TIPE_GANDA, (int) $pg->soal_tipe);
        $this->assertSame(4, $pg->jawabans()->count());
        $this->assertSame(1, $pg->jawabans()->where('jawaban_benar', true)->count());
        $this->assertSame('Jakarta', $pg->jawabans()->where('jawaban_benar', true)->value('jawaban_detail'));

        $essay = Soal::where('soal_detail', 'Jelaskan proses terjadinya hujan.')->firstOrFail();
        $this->assertSame(Soal::TIPE_ESSAY, (int) $essay->soal_tipe);
        $this->assertSame(0, $essay->jawabans()->count());

        $singkat = Soal::where('soal_detail', 'Hasil dari 5 + 6 adalah ...')->firstOrFail();
        $this->assertSame(Soal::TIPE_SINGKAT, (int) $singkat->soal_tipe);
        $this->assertSame('11', $singkat->soal_kunci);
    }

    public function test_admin_dapat_mengimport_soal_dari_excel(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $topik = Topik::where('topik_guru_id', $admin->id)->firstOrFail();
        $sebelum = $topik->soals()->count();

        $path = $this->buatFileExcel();

        try {
            $this->actingAs($admin, 'web');

            $this->post(route('admin.soal.import'), [
                'topik_id' => $topik->topik_id,
                'file' => new UploadedFile(
                    $path,
                    'soal.xlsx',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    null,
                    true
                ),
            ])->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');
        } finally {
            @unlink($path);
        }

        $this->assertSame($sebelum + 2, $topik->soals()->count());

        $pg = Soal::where('soal_detail', 'Soal dari Excel: ibu kota Jawa Barat?')->firstOrFail();
        $this->assertSame(Soal::TIPE_GANDA, (int) $pg->soal_tipe);
        $this->assertSame(3, $pg->jawabans()->count());
        $this->assertSame('Bandung', $pg->jawabans()->where('jawaban_benar', true)->value('jawaban_detail'));

        $essay = Soal::where('soal_detail', 'Soal dari Excel: jelaskan fotosintesis.')->firstOrFail();
        $this->assertSame(Soal::TIPE_ESSAY, (int) $essay->soal_tipe);
    }

    public function test_guru_tidak_dapat_mengimport_ke_grup_soal_guru_lain(): void
    {
        $admin = Admin::where('username', 'admin')->firstOrFail();
        $guru = Admin::where('username', 'gurua')->firstOrFail();
        $topikAdmin = Topik::where('topik_guru_id', $admin->id)->firstOrFail();
        $sebelum = $topikAdmin->soals()->count();

        $this->actingAs($guru, 'web');

        $this->post(route('admin.soal.import'), [
            'topik_id' => $topikAdmin->topik_id,
            'file' => UploadedFile::fake()->createWithContent('soal.csv', $this->csvContoh()),
        ])->assertRedirect()->assertSessionHas('popup_error');

        $this->assertSame($sebelum, $topikAdmin->soals()->count());
    }

    private function csvContoh(): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [
            'soal_detail', 'soal_tipe', 'soal_difficulty', 'soal_kunci',
            'jawaban_a', 'jawaban_b', 'jawaban_c', 'jawaban_d', 'jawaban_e',
            'jawaban_benar', 'soal_aktif',
        ]);
        fputcsv($handle, [
            'Ibu kota Indonesia adalah ...', 'pg', '1', '',
            'Jakarta', 'Bandung', 'Surabaya', 'Medan', '',
            'A', '1',
        ]);
        fputcsv($handle, [
            'Jelaskan proses terjadinya hujan.', 'essay', '2', '',
            '', '', '', '', '',
            '', '1',
        ]);
        fputcsv($handle, [
            'Hasil dari 5 + 6 adalah ...', 'singkat', '1', '11',
            '', '', '', '', '',
            '', '1',
        ]);

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    private function buatFileExcel(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            [
                'soal_detail', 'soal_tipe', 'soal_difficulty', 'soal_kunci',
                'jawaban_a', 'jawaban_b', 'jawaban_c', 'jawaban_d', 'jawaban_e',
                'jawaban_benar', 'soal_aktif',
            ],
            [
                'Soal dari Excel: ibu kota Jawa Barat?', 'pg', 1, '',
                'Bandung', 'Semarang', 'Surabaya', '', '',
                'A', 1,
            ],
            [
                'Soal dari Excel: jelaskan fotosintesis.', 'essay', 2, '',
                '', '', '', '', '',
                '', 1,
            ],
        ]);

        $base = tempnam(sys_get_temp_dir(), 'soal-xlsx-');
        $path = $base . '.xlsx';
        @unlink($base);

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }
}

