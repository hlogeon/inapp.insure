<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControllerMainHttpHandler extends Controller
{
    public function index()
    {
    	return view('index');
    }

    public function any($any) {
    	echo $any;
    }
}
