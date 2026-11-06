<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $table = 'cbt_user_grup';
    protected $primaryKey = 'grup_id';
    public $timestamps = false;
    protected $guarded = [];

    public const JENJANG_SD = 'SD';
    public const JENJANG_SMP = 'SMP';
    public const JENJANG_SMA = 'SMA';
    public const JENJANG_SMK = 'SMK';

    public static function jenjangOptions(): array
    {
        return [
            self::JENJANG_SD => 'SD',
            self::JENJANG_SMP => 'SMP/MTs',
            self::JENJANG_SMA => 'SMA/MA',
            self::JENJANG_SMK => 'SMK',
        ];
    }

    public static function jenjangDariKodeSekolah(?string $kode): ?string
    {
        return match (strtoupper((string) $kode)) {
            'SD', 'MI' => self::JENJANG_SD,
            'SMP', 'MTS' => self::JENJANG_SMP,
            'SMA', 'MA' => self::JENJANG_SMA,
            'SMK' => self::JENJANG_SMK,
            default => null,
        };
    }

    public static function tingkatOptions(?string $jenjang): array
    {
        $maks = $jenjang === self::JENJANG_SD ? 6 : 3;
        $opsi = [];

        for ($i = 1; $i <= $maks; $i++) {
            $opsi[$i] = 'Kelas ' . $i;
        }

        return $opsi;
    }

    public static function tebakJenjangTingkat(string $nama): array
    {
        $nama = strtoupper(trim($nama));
        $jenjang = self::JENJANG_SMA;

        if (preg_match('/\bSD\b|\bSEKOLAH DASAR\b/', $nama)) {
            $jenjang = self::JENJANG_SD;
        } elseif (preg_match('/\bSMP\b|\bMTS\b|\bMADRASAH TSANAWIYAH\b/', $nama)) {
            $jenjang = self::JENJANG_SMP;
        } elseif (preg_match('/\bSMK\b|\bSEKOLAH MENENGAH KEJURUAN\b/', $nama)) {
            $jenjang = self::JENJANG_SMK;
        }

        if ($jenjang === self::JENJANG_SD && preg_match('/\b(?:KELAS\s*)?([1-6])\b/', $nama, $match)) {
            return [$jenjang, (int) $match[1]];
        }

        $romawi = [
            'XII' => 3,
            'XI' => 2,
            'X' => 1,
            'IX' => 3,
            'VIII' => 2,
            'VII' => 1,
            'VI' => 6,
            'V' => 5,
            'IV' => 4,
            'III' => 3,
            'II' => 2,
            'I' => 1,
        ];

        if (preg_match('/\b(XII|XI|IX|VIII|VII|X|VI|V|IV|III|II|I)\b/', $nama, $match)) {
            $tingkat = $romawi[$match[1]];

            return [$jenjang, min($tingkat, count(self::tingkatOptions($jenjang)))];
        }

        if (preg_match('/\b([1-6])\b/', $nama, $match)) {
            return [$jenjang, min((int) $match[1], count(self::tingkatOptions($jenjang)))];
        }

        return [$jenjang, 1];
    }

    public function getJenjangLabelAttribute(): string
    {
        return self::jenjangOptions()[$this->grup_jenjang] ?? $this->grup_jenjang;
    }

    public function getKelasLabelAttribute(): string
    {
        return $this->jenjang_label . ' Kelas ' . (int) $this->grup_tingkat;
    }

    public function pesertas()
    {
        return $this->hasMany(Peserta::class, 'user_grup_id', 'grup_id');
    }

    public function tes()
    {
        return $this->belongsToMany(Tes::class, 'cbt_tesgrup', 'tstgrp_grup_id', 'tstgrp_tes_id');
    }

    public function foundation()
    {
        return $this->belongsTo(Foundation::class, 'grup_foundation_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'grup_school_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'grup_academic_year_id');
    }
}
