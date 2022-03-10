<?php
namespace App\Http\Traits;

/**
 * Created by PhpStorm.
 * User: hosseini
 * Date: 1/27/18
 * Time: 12:43 PM
 */
use Illuminate\Support\Facades\DB;
use App\Models\Comp_Ben\setup;
trait CsvValidateTrait
{
    function csvToArray($headers,$filename = '', $delimiter = ',')
    {
       
      if (!file_exists($filename) || !is_readable($filename))
          return false;
  
      $header = null;
      $data = array();
      if (($handle = fopen($filename, 'r')) !== false)
      {
          while (($row = fgetcsv($handle, 10000, $delimiter)) !== false)
          {
              if (!$header){
                $result = array_filter($row, function($v){
                    return trim($v);
                 });
                
                  $header = $result;
               
                }
              else{ 
            

                  $data[] = array_combine($header, $row);

              }
          }
          fclose($handle);
      }
      //for check differece in result
      $result = array_diff($header, $headers);
      if(count($result) == 0){
          
          return $data;
      }else{
          return null;
      }
     }
}