<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class group extends Model
{
    protected $table = 'groups';
    protected $fillable = ['group_id',"groupTitle","group_type","group_logo","menu_script","seqno","group_abbreviation","group_desc"];
    public $timestamps = false;

}
