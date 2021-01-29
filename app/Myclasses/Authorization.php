<?php

namespace App\Myclasses;
use Illuminate\Http\Request;
use App\Models\User;

class Authorization
{
	public function current()
    {
        return response()->json([
            "data" => ((new User)->isAuthorized()) ? 1 : 0
        ]);
    }

    public function currentUser()
    {
        $status = true;
        $userData = (new User)->currentUser();

        if( ! $userData ) $status = false;

        return response()->json([
            "status" => $status,
            "data" => $userData
        ]);
//	->header('Access-Control-Allow-Origin', 'http://172.105.84.217:3000');
    }

    public function logout()
    {
        return (new User)->logout();
    }
}
