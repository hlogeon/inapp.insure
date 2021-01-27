<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TopMenu;
use App\Models\SiteInformation;

class MenuTop extends Controller
{
    public function index()
    {
    	$status = true;
    	$menu = TopMenu::where("active", 1)->orderBy('sort', 'desc')->get();
        $info = SiteInformation::where("global_name", 'telegram')
            ->orWhere("global_name", 'whatsapp')
            ->orWhere("global_name", 'instagram')
            ->orWhere("global_name", 'facebook')
            ->get();
    	if( ! $menu ) 
    		$status = false; 

    	return response()->json([
		    'status' => $status,
		    'data' => [
                "menu"  => $menu,
                "info"  => $info
            ]
		]);
    }
}
