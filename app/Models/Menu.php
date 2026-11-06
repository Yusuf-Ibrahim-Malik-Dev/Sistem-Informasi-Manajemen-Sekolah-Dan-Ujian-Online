<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'user_menu';
    public $timestamps = false;
    protected $guarded = [];

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent', 'kode_menu')->orderBy('urutan');
    }
}
