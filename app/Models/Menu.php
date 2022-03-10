<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    protected $fillable = [
        'menu_title', 
        'parent_id', 
        'menu_order',
        'menu_type_id',
        'level',
        'client_id',
        'icon',
        'img',
        'page_menu_title',
        'created_by',
        'updated_at',
        'created_at'
    ];

}
