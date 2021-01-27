<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BottomMenu1;
use App\Models\BottomMenu2;
use App\Models\SiteInformation;


class ControllerMenuBottom extends Controller
{
    public function index(Request $request)
    {
    	$data = [];
    	$BottomMenu1 = BottomMenu1::where("active", 1)->orderBy("sort", "desc")->get();
    	$BottomMenu2 = BottomMenu2::where("active", 1)->orderBy("sort", "desc")->get();
    	$info = SiteInformation::where("global_name", "phone")
    		->orWhere("global_name", 'footer_information')
            ->orWhere("global_name", 'telegram')
            ->orWhere("global_name", 'whatsapp')
            ->orWhere("global_name", 'instagram')
            ->orWhere("global_name", 'facebook')
    		->get();
    	$data['bottom_menu_1'] = $BottomMenu1;
    	$data['bottom_menu_2'] = $BottomMenu2;
    	$data['info'] = $info;
    	return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
