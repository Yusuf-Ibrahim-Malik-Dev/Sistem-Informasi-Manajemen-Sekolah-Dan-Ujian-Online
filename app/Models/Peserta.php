<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Peserta extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'cbt_user';

    protected $primaryKey = 'user_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['user_password'];

    public function getAuthPassword()
    {
        return $this->user_password;
    }

    public function grup()
    {
        return $this->belongsTo(UserGroup::class, 'user_grup_id', 'grup_id');
    }

    public function foundation()
    {
        return $this->belongsTo(Foundation::class, 'user_foundation_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'user_school_id');
    }

    public function tesUsers()
    {
        return $this->hasMany(TesUser::class, 'tesuser_user_id', 'user_id');
    }

    public function rapors()
    {
        return $this->hasMany(Raport::class, 'raport_user_id', 'user_id');
    }

    public function orangTuas()
    {
        return $this->belongsToMany(OrangTua::class, 'cbt_orang_tua_peserta', 'user_id', 'ortu_id');
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'absensi_user_id', 'user_id');
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'pembayaran_user_id', 'user_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id', 'user_id');
    }

    public function getNamaAttribute(): string
    {
        return $this->user_firstname ?: $this->user_name;
    }

    public function jenjangSekolah(): ?string
    {
        $jenjang = UserGroup::jenjangDariKodeSekolah($this->school?->level?->level_code);

        if ($jenjang !== null) {
            return $jenjang;
        }

        $jenjangGrup = $this->grup?->grup_jenjang;

        return isset(UserGroup::jenjangOptions()[$jenjangGrup]) ? $jenjangGrup : null;
    }
}
