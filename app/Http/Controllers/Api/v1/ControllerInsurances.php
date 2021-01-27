<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InsuranceLists;

class ControllerInsurances extends Controller
{
    public function index() {
    	$insurances = InsuranceLists::orderBy('sort', 'desc')->get();

    	foreach ($insurances as $key => $insurance) {
    		$insurances[$key]->price = number_format($insurance->price, 0, "", " ");
    	}

    	return response()->json([
            'status' => true,
            'data' => $insurances
        ]);
    }
}
