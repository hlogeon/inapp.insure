<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Myclasses\Client;
use App\Models\User;

class PolisiesCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        // $tariffs = [
        //     '1' => '1 месяц',
        //     '2' => '3 месяца',
        //     '3' => '6 месяцев',
        //     '4' => '12 месяцев'
        // ];
        // $data = [];
        // $user = User::find($this->data->user_id);
        // if(!is_null($user)) {
        //     $data = [
        //         'user' => [
        //             'id' => $user->id,
        //             'name' => $user->name,
        //             'birthday' => $user->user_birsday,
        //             'phone' => $user->phone,
        //             'email' => $user->email
        //         ],
        //         'police' => [
        //             'active' => $this->data->active,
        //             'user_id' => $this->data->user_id,
        //             'subscribed' => $this->data->subscribed,
        //             'changed_tarrif' => $this->data->changed_tarrif,
        //             'number' => $this->data->number,
        //             'address' => $this->data->address,
        //             'appartment' => $this->data->appartment,
        //             'start' => strtotime($this->data->start),
        //             'finish' => strtotime($this->data->finish),
        //             'tarrif_id' => $this->data->tarrif_id,
        //             'status_id' => $this->data->status_id,
        //             'vote' => $this->data->vote,
        //             'company' => $this->data->company,
        //         ],
        //         'payment' => [
        //             'created_at' => strtotime($this->data->created_at)
        //         ]
        //     ];
        // }

        // if(sizeof($data)) {

        //     $client = new Client();
        //     $crm = $client->crm();

        //     $contact = null;
        //     if(!empty($data['user']) && !empty($data['user']['phone'])) {
        //         $first = false;
        //         $contacts = $crm->contacts()->searchByPhone($data['user']['phone']);

        //         if (!$contact = $contacts->first()) {
        //             $contact = $crm->contacts()->create();
        //             $contact->responsible_user_id = 6430543;
        //             $contact->name = $data['user']['name'];
        //             ($data['user']['email']) ? $contact->cf()->byId(255875)->setValue($data['user']['email'], 'Work') : null;
        //             ($data['user']['phone']) ? $contact->cf()->byId(255873)->setValue($data['user']['phone'], 'Work') : null;
        //             $contact->save();
        //             $first = true;
        //         }

        //         $count_leads = $contact->leads->filter(function($lead) use(&$data) {
        //             return $lead->pipeline_id == 3666040 || $lead->pipeline_id == 3778480;
        //         });
        //         if($count_leads->count() == 0) {
        //             $first = true;
        //         }

        //         $leads = $contact->leads->filter(function($lead) use(&$data) {
        //             return $lead->status_id != 143 && $data['police']['number'] == $lead->cf()->byId(335005)->getValue();
        //         });
        //         if($first) {
        //             $pipeline_id = 3666040;
        //             $status_id = 35724088;
        //         } else {
        //             $pipeline_id = 3778480;
        //             $status_id = 36509749;
        //         }
        //         if($lead = $leads->first()) {
        //             $number = substr($lead->name, 0, 1);
        //             $lead->name = $number . ' Полис ' . $data['police']['number'] . ' на квартиру ' . $data['police']['address'];
        //             !empty($data['police']['tarrif_id']) ? $lead->cf()->byId(334991)->setValue($tariffs[$data['police']['tarrif_id']]) : null;
        //             !empty($data['police']['address']) ? $lead->cf()->byId(335001)->setValue($data['police']['address']) : null;
        //             !empty($data['payment']['created_at']) ? $lead->cf()->byId(334997)->setValue($data['payment']['created_at']) : null;
        //             !empty($data['police']['start']) ? $lead->cf()->byId(334999)->setValue($data['police']['start']) : null;
        //             !empty($data['police']['finish']) ? $lead->cf()->byId(335003)->setValue($data['police']['finish']) : null;
        //             !empty($data['police']['number']) ? $lead->cf()->byId(335005)->setValue($data['police']['number']) : null;
        //             $lead->save();
        //         } else {
        //             $lead = $contact->createLead();
        //             $lead->pipeline_id = $pipeline_id;
        //             $lead->status_id = $status_id;
        //             $lead->name = $count_leads->count() + 1 . ' Полис ' . $data['police']['number'] . ' на квартиру ' . $data['police']['address'];
        //             !empty($data['police']['tarrif_id']) ? $lead->cf()->byId(334991)->setValue($tariffs[$data['police']['tarrif_id']]) : null;
        //             !empty($data['police']['address']) ? $lead->cf()->byId(335001)->setValue($data['police']['address']) : null;
        //             !empty($data['payment']['created_at']) ? $lead->cf()->byId(334997)->setValue($data['payment']['created_at']) : null;
        //             !empty($data['police']['start']) ? $lead->cf()->byId(334999)->setValue($data['police']['start']) : null;
        //             !empty($data['police']['finish']) ? $lead->cf()->byId(335003)->setValue($data['police']['finish']) : null;
        //             !empty($data['police']['number']) ? $lead->cf()->byId(335005)->setValue($data['police']['number']) : null;
        //             $lead->save();
        //         }
        //     }
        // }
    }
}
