<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalRole extends Model
{
    protected $table = 'portal_roles';
    protected $fillable = [
        'portal_id',
        'role_id' ,
        'app_view_config',
    ];
    public $timestamps = false;
}
