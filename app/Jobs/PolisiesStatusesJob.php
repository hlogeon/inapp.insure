<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use App\Models\Polisies;
use App\Models\User;
use App\Myclasses\Client;
use Illuminate\Support\Facades\Log;

class PolisiesStatusesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    public function __construct(Request $request)
    {
        $this->data = $request->input();
    }


    public function handle()
    {
        $client = new Client();
        $crm = $client->crm();
        $number = null;
        $phone = null;
        $status = null;

        if (isset($this->data['leads']) && isset($this->data['leads']['update'])) {
            foreach ($this->data['leads']['update'] as $lead) {
                $amo_lead = $crm->leads()->find($lead['id']);

                if(isset($lead['custom_fields'])) {
                    foreach ($lead['custom_fields'] as $field) {
                        if($field['id'] == '335005') {
                            $number = $field['values'][0]['value'];
                        }
                        if($field['id'] == '348799') {
                            $status = $field['values'][0]['value'];
                        }
                    }
                }

                if($amo_lead) {
                    $contact_id = $amo_lead->main_contact_id;
                    if(!is_null($contact_id)) {
                        $amo_contact = $crm->contacts()->find($contact_id);
                        if($amo_contact) {
                            $phone = $amo_contact->cf()->byId(255873)->getValue();
                        }
                    }
                }

                if(!is_null($phone) && !is_null($number)) {
                    $user = User::where('phone', $phone)->get()->first();
                    $polisies = Polisies::where('user_id', $user->id)->where('number', $number)->get()->first();
                    if($polisies && !is_null($status) && $status && $polisies->status_id != 3) {
                        $polisies->status_id = 3;
                        $polisies->save();
                        Log::channel('amocrm')->info('change status polis #3');
                    }
                }
            }
        }

        Log::channel('amocrm')->info($this->data);
    }
}
