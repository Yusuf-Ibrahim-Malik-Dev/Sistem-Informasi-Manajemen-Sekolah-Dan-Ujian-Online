<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class OrangTua extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'cbt_orang_tua';

    protected $primaryKey = 'ortu_id';

    public $timestamps = true;

    protected $guarded = [];

    protected $hidden = ['ortu_password'];

    public function getAuthPassword()
    {
        return $this->ortu_password;
    }

    public function getNamaAttribute(): string
    {
        return $this->ortu_nama;
    }

    public function getUsernameAttribute(): string
    {
        return $this->ortu_username;
    }

    public function pesertas()
    {
        return $this->belongsToMany(Peserta::class, 'cbt_orang_tua_peserta', 'ortu_id', 'user_id');
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'pembayaran_ortu_id', 'ortu_id');
    }
}
