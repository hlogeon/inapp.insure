<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Polisies;
use App\Models\User;
use App\Models\Payments;
use Carbon\Carbon;
use App\Myclasses\Client;
use App\Jobs\PolisiesStatusesJob;
use Illuminate\Support\Facades\Log;

class ControllerCrm extends Controller
{
    public function index()
    {
        $client = new Client();
        $crm = $client->crm();

        $contact = null;
        $phone = '+79373963517';    // тут должен быть номер телефона из модели User, что бы найти сделки в амо для этого контакта
        $number = '1';  // тут должен быть номер полиса из модели Polisies, для поиска сдеки в амосрм по нему
        $status = '1'; // отписка до 14 дней = к примеру статус 1
        $status = '2'; // отписка после 14 дней = к примеру статус 2

        $contacts = $crm->contacts()->searchByPhone($phone);
        if ($contact = $contacts->first()) {
            $leads = $contact->leads->filter(function($lead) use(&$number) {
                return $lead->cf()->byId(335005)->getValue() == $number;
            });

            if($leads->count()) {
                if($lead = $leads->first()) {
                    if($status == '1') {
                        $lead->status_id = 35724091;    // отписка до 14 дней
                    } else if ($status == '2') {
                        $lead->status_id = 35724094;    // отписка после 14 дней
                    }
                    $lead->save();
                }
            }
        }
    }

    public function hook(Request $request)
    {
        PolisiesStatusesJob::dispatch($request);
        return true;
    }

    public function auth()
    {
        $client = new Client();
        $crm = $client->crm();
        echo '<pre>';
        print_r($crm->account);
        echo '</pre>';
    }

    public function redirect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code'        => 'string',
            'referer'     => 'string',
            'state'       => 'string',
            'from_widget' => 'optional',
            'error'       => 'optional'
        ]);
        if($validator->fails()) {
            return false;
        }
        $data = $validator->getData();
        $client = new Client();
        $data = $client->crm()->fetchAccessToken($data['code']);
    }
}