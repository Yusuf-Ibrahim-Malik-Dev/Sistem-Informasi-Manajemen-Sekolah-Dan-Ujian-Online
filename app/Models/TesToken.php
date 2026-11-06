<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TesToken extends Model
{
    protected $table = 'cbt_tes_token';
    protected $primaryKey = 'token_id';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'token_ts' => 'datetime',
        'token_aktif' => 'integer',
        'token_tes_id' => 'integer',
    ];

    public function tes()
    {
        return $this->belongsTo(Tes::class, 'token_tes_id', 'tes_id');
    }

    public function user()
    {
        return $this->belongsTo(Admin::class, 'token_user_id', 'id');
    }

    public function getPemakaianAttribute(): int
    {
        return TesUser::where('tesuser_token', $this->token_isi)->count();
    }

    public static function generate(string $isi, int $userId, int $tesId = 0, int $aktif = 1): self
    {
        return static::create([
            'token_isi' => $isi,
            'token_user_id' => $userId,
            'token_ts' => now(),
            'token_aktif' => $aktif,
            'token_tes_id' => $tesId,
        ]);
    }

    public static function validFor(string $token, int $tesId): ?self
    {
        $kandidat = static::where('token_isi', $token)
            ->whereDate('token_ts', now()->toDateString())
            ->where(fn ($q) => $q->where('token_tes_id', 0)->orWhere('token_tes_id', $tesId))
            ->latest('token_id')
            ->get();

        return $kandidat->first(function (self $found) {
            if ((int) $found->token_aktif === 1) {
                return true;
            }

            if (! $found->token_ts) {
                return false;
            }

            return $found->token_ts->copy()
                ->addMinutes((int) $found->token_aktif)
                ->isFuture();
        });
    }
}
