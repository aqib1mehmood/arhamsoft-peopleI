<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    protected $table = 'pr_documents';
   protected $fillable = [
      'url','designation_name','pr_documentable_type ', 'pr_documentable_id ',
   ];

   public function pr_documentable()
   {
       return $this->morphTo();
   }
}
