<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plans;

class ControllerPlans extends Controller
{
    public function index()
    {
    	$status = true;
    	$plans = Plans::get();
    	if (!$plans)
    		$status = false;

    	return response()->json([
		    'status' => $status,
		    'data' => $plans,
		]);
    }
}
