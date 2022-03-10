<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Band extends Model
{
    protected $table = 'employee_bands';
    protected $fillable = [
        'band_description','band_desc','clientid', 'unified_band', 'band_status'];
    public $timestamps = false;
    public function employeeDetails(){
        return $this->belongsTo('App\Models\Employee','id','designation');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee','id');
    }
}
