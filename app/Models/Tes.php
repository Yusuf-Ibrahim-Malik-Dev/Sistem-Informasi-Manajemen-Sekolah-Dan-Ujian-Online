<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Tes extends Model
{
    protected $table = 'cbt_tes';

    protected $primaryKey = 'tes_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'tes_begin_time' => 'datetime',
        'tes_end_time' => 'datetime',
        'tes_results_to_users' => 'boolean',
        'tes_detail_to_users' => 'boolean',
        'tes_ranking_to_users' => 'boolean',
        'tes_token' => 'boolean',
        'tes_biaya' => 'float',
        'tes_absensi_wajib' => 'boolean',
        'tes_checkin_mulai' => 'datetime',
        'tes_checkin_selesai' => 'datetime',
    ];

    public function groups()
    {
        return $this->belongsToMany(UserGroup::class, 'cbt_tesgrup', 'tstgrp_tes_id', 'tstgrp_grup_id');
    }

    public function topikSets()
    {
        return $this->hasMany(TesTopikSet::class, 'tset_tes_id', 'tes_id');
    }

    public function tesUsers()
    {
        return $this->hasMany(TesUser::class, 'tesuser_tes_id', 'tes_id');
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'pembayaran_tes_id', 'tes_id');
    }

    public function attendanceLocation()
    {
        return $this->belongsTo(AttendanceLocation::class, 'tes_absensi_location_id');
    }

    public function attendanceSession()
    {
        return $this->hasOne(AttendanceSession::class, 'exam_id', 'tes_id')
            ->where('session_type', AttendanceSession::TYPE_EXAM);
    }

    public function foundation()
    {
        return $this->belongsTo(Foundation::class, 'tes_foundation_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'tes_school_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'tes_academic_year_id');
    }

    public function getJumlahSoalAttribute(): int
    {
        return (int) $this->topikSets()->sum('tset_jumlah');
    }

    public function getIsOpenAttribute(): bool
    {
        $now = now();

        if ($this->tes_begin_time && $now->lt($this->tes_begin_time)) {
            return false;
        }

        if ($this->tes_end_time && $now->gt($this->tes_end_time)) {
            return false;
        }

        return true;
    }

    public function getStatusAttribute(): string
    {
        $now = now();

        if ($this->tes_begin_time && $now->lt($this->tes_begin_time)) {
            return 'belum';
        }

        if ($this->tes_end_time && $now->gt($this->tes_end_time)) {
            return 'selesai';
        }

        return 'aktif';
    }

    public function scopeOpenNow($query)
    {
        $now = Carbon::now();

        return $query->where(function ($q) use ($now) {
            $q->whereNull('tes_begin_time')->orWhere('tes_begin_time', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('tes_end_time')->orWhere('tes_end_time', '>=', $now);
        });
    }
}
