<?php

namespace App\Myclasses;

use App\Models\User;
use App\Models\Clicks;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class Unicom 
{

    protected $API_URL = 'https://unicom24.ru/offer/postback/';

    public function send($clickId, $status)
    {
      $resp = Http::get($API_URL . $clickId . '/?status=' . $status);
    }

    public function findOrSaveClickId($clickId, $userId)
    {
      $click = Clicks::where(['user_id' => $userId])->first();
      if (!$click) {
        $click = Clicks::create(['user_id' => $userId, 'click_id' => $clickId]);
      }
      return $click;
    }

}