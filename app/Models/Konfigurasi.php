<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Konfigurasi extends Model
{
    protected $table = 'cbt_konfigurasi';
    protected $primaryKey = 'konfigurasi_id';
    public $timestamps = false;
    protected $guarded = [];

    public static function get(string $kode, $default = null)
    {
        $all = Cache::rememberForever('cbt_konfigurasi', function () {
            return static::query()->pluck('konfigurasi_isi', 'konfigurasi_kode')->toArray();
        });

        return $all[$kode] ?? $default;
    }

    public static function set(string $kode, string $isi, string $keterangan = ''): void
    {
        static::updateOrCreate(
            ['konfigurasi_kode' => $kode],
            ['konfigurasi_isi' => $isi, 'konfigurasi_keterangan' => $keterangan ?: $kode]
        );

        Cache::forget('cbt_konfigurasi');
    }
}
