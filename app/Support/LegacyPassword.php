<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

class LegacyPassword
{
    /**
     * Cocokkan password dengan hash lama (sha1), plain text, atau bcrypt.
     */
    public static function verify(?string $plain, ?string $hash): bool
    {
        if ($plain === null || $hash === null || $hash === '') {
            return false;
        }

        if (Hash::check($plain, $hash)) {
            return true;
        }

        if (hash_equals($hash, $plain)) {
            return true;
        }

        return hash_equals(strtolower($hash), sha1($plain));
    }

    /**
     * Upgrade hash lama ke bcrypt setelah login sukses.
     */
    public static function upgrade(Authenticatable $user, string $plain, string $field = 'password'): void
    {
        $current = $user->getAttribute($field);

        if ($current && Hash::needsRehash($current)) {
            $user->setAttribute($field, Hash::make($plain));
            $user->save();
        }
    }
}
