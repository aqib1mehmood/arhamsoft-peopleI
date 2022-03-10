<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupRoleMenu extends Model
{
    protected $table = 'group_role_menu';
    protected $fillable = [
        'menus_id', 
        'group_role_id', 
        'component_name',
        'file_name',
        'description',
        'delete',
        'right',
        'update',
        'view',
        'portal_type',
        'status'
    ];
    public $timestamps = false;


}
