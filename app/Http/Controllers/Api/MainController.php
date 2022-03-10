<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

class MainController extends Controller
{

    public function index()
    {
        return \File::get(public_path() . '\dist\index.html');
    }

}
