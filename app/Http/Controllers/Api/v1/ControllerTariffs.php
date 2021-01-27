<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tarrifs;

class ControllerTariffs extends Controller
{
    public function index()
    {
    	$status = true;
    	$tarrifs = Tarrifs::orderBy('sort', 'desc')->get();
    	if( ! $tarrifs )
    		$status = false;

    	return response()->json([
		    'status' => $status,
		    'data' => $tarrifs
		]);
    }
}
