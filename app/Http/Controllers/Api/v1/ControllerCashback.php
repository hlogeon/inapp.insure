<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cashback;
use App\Models\User;

class ControllerCashback extends Controller
{
    public function index()
    {
    	$status = true;
    	$cashbacks = Cashback::orderBy('sort', 'desc')->get();
    	if( ! $cashbacks )
    		$status = false;

    	return response()->json([
		    'status' => $status,
		    'data' => $cashbacks
		]);
    }

    public function publicable()
    {
        $status = true;
        $cashbacks = Cashback::where("public", 1)
            ->orderBy('sort', 'desc')
            ->get();

        $user = (new User)->currentUser();

        if( ! $cashbacks )
            $status = false;

        return response()->json([
            'status' => $status,
            'data' => $cashbacks,
            'user' => $user
        ]);
    }
}
