<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Route;

class RouteHelper
{
  public static function routePath($module)
  {
    $path=dirname(__DIR__).'/../Modules/'.$module.'/Routes/api.php';
    if(file_exists($path))
    {
      require($path);
    }
  }
  public static function callRoute($module)
  {
    $namespace='\Modules\\'.$module.'\Http\Controllers';
    Route::group(['namespace' =>$namespace], function() use ($module){
      self::routePath($module);    
    });
  }
}
