<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMenuAccess extends Model
{
    protected $table = 'user_menu_user_access';

    protected $guarded = [];

    protected $casts = [
        'is_allowed' => 'boolean',
        'can_add' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
        'can_approve' => 'boolean',
    ];
}
