<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payments;
use App\Models\FlocktoryCashback;
use App\Models\FavoriteCashback;
use App\Models\CashbackActivation;
use App\Models\Polisies;
use App\Models\Risks;
use App\Models\Tarrifs;
use App\Models\Plans;
use App\Models\System;
use App\Models\InsuranceLists;
use Carbon\Carbon;
use App\Myclasses\SmsSender;
use App\Myclasses\PolisPDF;
use App\Myclasses\Client;
use Illuminate\Support\Facades\Http;

class ControllerPersonal extends Controller
{
    public function __construct()
    {
    	$this->middleware('auth')
            ->except('landingBonuses')->except('landingAllBonuses')
            ->except('getPlans');
    }

    private function getUser()
    {
        $user = new User;
        return $user->currentUser();
    }

    public function landingBonuses()
    {
        $bonuses = FlocktoryCashback::where([
            'deleted_at' => null,
            'landing' => true,
        ])->with('cashbackCompany')->take(6)->get();
        foreach ($bonuses as $key => $bonus) {
            $b = $bonus->toArray();
            $b['site'] = $b['cashback_company'];
            $b['logo'] = $b['cashback_company']['logo'];
            unset($b['activation_url']);
            $bonuses[$key] = $b;
        }
        return response()->json([
            'status' => true,
            'data' => $bonuses
        ]);
    }

    public function landingAllBonuses()
    {
        $bonuses = FlocktoryCashback::where([
            'deleted_at' => null,
        ])->with('cashbackCompany')->get();
        foreach ($bonuses as $key => $bonus) {
            $b = $bonus->toArray();
            $b['site'] = $b['cashback_company'];
            $b['logo'] = $b['cashback_company']['logo'];
            unset($b['activation_url']);
            $bonuses[$key] = $b;
        }
        return response()->json([
            'status' => true,
            'data' => $bonuses
        ]);
    }

    public function getBonusList()
    {
        $user = $this->getUser();
        // if($user->tarrif_id === null) {
        //     return response()->json([
        //         'status' => false,
        //         'data' => [],
        //     ]);
        // }
        $bonuses = FlocktoryCashback::where([
            'deleted_at' => null,
        ])->with('cashbackCompany')->get();
        $favorites = FavoriteCashback::where([ 'user_id' => $user->id ])->get();
        $activated = CashbackActivation::where(['user_id' => $user->id])->get();
        foreach ($bonuses as $key => $bonus) {
            $b = $bonus->toArray();
            $b['activationUrl'] = $b['activation_url'];
            $b['site'] = $b['cashback_company'];
            $b['logo'] = $b['cashback_company']['logo'];
            foreach ($favorites as $like) {
                if ($like->cashback_id == $bonus->id) {
                    $b['favorite'] = $like->value;
                }
            }
            foreach($activated as $activation) {
                if ($activation->flocktory_cashback_id == $bonus->id) {
                    $b['activated'] = true;
                }
            }
            $bonuses[$key] = $b;
        }

        return response()->json([
            'status' => true,
            'data' => $bonuses
        ]);
    }

    public function favoriteBonus(Request $request)
    {
        $id = $request->id;
        $favorite = $request->favorite;
        $user = $this->getUser();
        // if($user->tarrif_id === null) {
        //     return response()->json([
        //         'status' => false,
        //         'data' => [],
        //     ]);
        // }
        $cashback = FlocktoryCashback::find($id);
        if (!$cashback) { abort(502); }
        $like = FavoriteCashback::where(['user_id' => $user->id, 'cashback_id' => $id])->first();
        if (!$like) {
            $like = FavoriteCashback::create([
                'user_id' => $user->id,
                'cashback_id' => $id,
                'value' => (bool) $favorite,
            ]);
        } else {
            $like->value = (bool) $favorite;
            $like->save();
        }
        return response()->json([
            'status' => true,
            'data' => $like->toArray(),
        ]);
    }


    public function activateBonus(Request $request)
    {
        $id = $request['id'];
        $user = $this->getUser();
        // if($user->tarrif_id === null) {
        //     return response()->json([
        //         'status' => false,
        //         'data' => [],
        //     ]);
        // }
        $cashback = FlocktoryCashback::where('flocktory_id', $id)->first();
        if (!$cashback) { abort(502); }
        $activated = CashbackActivation::where('flocktory_cashback_id', $cashback->id)->first();
        if (!$activated) {
            $activation = CashbackActivation::create([
                'user_id' => $user->id,
                'flocktory_cashback_id' => $cashback->id,
            ]);
            $cashback->activations++;
            $cashback->save();
        }
        $response = Http::get('https://client.flocktory.com/v2/exchange/accept-campaign', [
            'token' => env('FLOCKTORY_TOKEN', '36c7afaf0080ddbe1f6c5339045963af'),
            'site_id' => env('FLOCKTORY_SITE_ID', 3169),
            'email' => $user->email,
            'campaign_id' => $id,

        ])->json();
        return response()->json([
            'status' => true,
            'data' => $response
        ]);
    }

    public function getPdf($id = null)
    {
        $user = $this->getUser();

        if($user) {
            $polic = Polisies::where([
                'id'        => $id,
                'user_id'   => $user->id
            ])->first();
            if($polic) {

                $tarrif = Tarrifs::find($polic->tarrif_id);
                $pdf = (new PolisPDF)->genPolicyPdf(
                    $polic->bso,
                    $polic->address . ", ".$polic->appartment,
                    $user->user_name . " " . $user->user_surname,
                    date('d.m.Y', strtotime($user->user_birsday)),
                    "",
                    $tarrif->price,
                    date('d.m.Y', strtotime($polic->start)),
                    date('d.m.Y', strtotime($polic->finish)),
                    "vsk",
                    $user->phone,
                    $user->user_email
                );
                return $pdf;
            }
        }
        return false;
    }

    public function personalInfo()
    {
    	$status = false;
    	$data = [];
    	$userData = $this->getUser();
    	if($userData) {
    		$status = true;
    		$data["user"] = $userData;
    		$paymentsData = [];
    		$payments = Payments::select("payments.*", "polisies.link")
                ->join("polisies", "polisies.id", "payments.polic_id")
                ->where('payments.user_id', $userData->id)
                ->get();

    		foreach ($payments as $key => $payment) {
    			$polic = Polisies::where([
                    'id'        => $payment->polic_id,
                    'active'    => 1
                ])->first();

    			if($polic) {
                    $start = Carbon::createFromTimestamp(strtotime($polic->start));//->subDays(3);
                    $finish = Carbon::createFromTimestamp(strtotime($polic->finish));//->subDays(3);

    				$paymentsData[$key]["id"] = $payment->id;
    				$paymentsData[$key]["address"] = $polic->address . ", " . $polic->appartment;
    				$paymentsData[$key]["price"] = $payment->Amount." ₽";
    				$paymentsData[$key]["period"] = date('d.m.Y', $start->timestamp) . "-" . date('d.m.Y', $finish->timestamp);
                    $paymentsData[$key]["link"] = $payment->link;
                    $paymentsData[$key]["polic_id"] = $payment->polic_id;
    			}
    		}
            $card = Payments::select("CardType", "CardLastFour")
                ->where([
                    "user_id" => $userData->id,
                    "Status"  => "Completed"
                ])
                ->orderBy('id', "desc")
                ->first();

            $data["card"] = $card;

    		$data["payments"] = $paymentsData;
    	}

    	return response()->json([
		    'status' => $status,
		    'data' => $data
		]);
    }

    public function changePhone(Request $request)
    {
        $phone = $request["phone"];
        $data = [];
        $checkingUser = User::where('name', preg_replace("/[\D]/", "", $phone))->first();
        if( ! $checkingUser ) {
            $code = rand(1000, 9999);
            if($phone || $phone && isset($request["again"]))
                request()->session()->put('change_phone', $phone);

            if($code)
                request()->session()->put('change_code', $code);

            $user = $this->getUser();
            if( $user ) {
                $status = true;
                $data["code"] = $code;
                $send = new SmsSender("+".preg_replace("/[\D]/", "", $phone), $code . " — ваш код подтверждения для входа в inapp.insure");
                $smsData = $send->getResponse();
                if($smsData->status == "ERROR") {
                   $status = false;
                   $data["errors"][] = $smsData->status_text;
                }
            } else
                $status = false;
        } else {
            $status = false;
            $data["errors"][] = "Пользователь с таким телефоном уже сущестует";
        }

        return response()->json([
            'status' => $status,
            'data' => $data,
            //'session' => request()->session()->get('change_code')
        ]);
    }

    public function checkingPhoneAfterSentSms(Request $request)
    {
        $phone = request()->session()->get('change_phone');

        return response()->json([
            'status' => true,
            'data' => $phone
        ]);
    }

    public function checkMessage() {
        $message = request()->session()->get('account_message');
        request()->session()->put('account_message', '');
        return response()->json([
            $message
        ]);
    }

    public function changePhoneSms(Request $request)
    {
        $code = $request["sms"];
        $data = [];
        $user = $this->getUser();
        if( $user ) {
            $phone = request()->session()->get('change_phone');
            $checkingCode = request()->session()->get('change_code');

            if($checkingCode != $code) {
                $status = false;
                $data["errors"][] = ["sms" => "Неверный код подтверждения"];
            } else {
                $status = true;
                $data["phone_changed"] = true;
                request()->session()->put('change_phone', '');
                request()->session()->put('change_code', '');
                User::where('id', $user->id)
                ->update([
                   'name'   => preg_replace("/[\D]/", "", $phone),
                   'phone'  => $phone
                ]);
                request()->session()->put('account_message', 'Ваш телефон успешно изменен');
            }
        } else
            $status = false;

        return response()->json([
            'status' => $status,
            'data' => $data,
            //'session' => request()->session()->get('change_code')
        ]);
    }

    public function getRisks()
    {
        $risks = Risks::orderBy("sort", "desc")->get();

        return response()->json([
            'status' => true,
            'data' => $risks
        ]);
    }

    public function getIinsurances()
    {
        $insurances = InsuranceLists::orderBy("sort", "desc")->get();

        foreach ($insurances as $key => $insurance) {
            $insurances[$key]->price = $this->priceFormat($insurance->price);
        }

        return response()->json([
            'status' => true,
            'data' => $insurances
        ]);
    }

    public function getPolices(Request $request)
    {
        $id = $request["id"];
        $user = $this->getUser();
        $data = [];
        
        $polices = Polisies::where([
            "user_id"   => $user->id,
            "active"    => 1
        ])->get();

        foreach ($polices as $key => $police) {
            $polices[$key]->finish = Carbon::createFromTimestamp(strtotime($police->finish))->subDays(3);
        }
        
        if($id > 0) {
            $polices = $this->sortPolices($polices, $id);
        }

        if(isset($polices[0])) {
            $police = $polices[0];
            $tarrif = Tarrifs::find($police->tarrif_id);
            if($tarrif) {
                $police->tarrif = $tarrif;
            }
            $payment = Payments::where([
                'user_id' => $user->id,
                "Status"  => "Completed"
            ])->first();

            if($payment) {
                $police->card = [
                    'type' => $payment->CardType,
                    'number' => $payment->CardLastFour
                ];
            }
            $data["current"] = $police;
        }
        
        $data["polices"] = $polices;
        $data["user"] = $user;

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function enableSubscribe(Request $request)
    {
        $user = $this->getUser();
        $id = (isset($request['id'])) ? $request['id'] : false;

        if($user && $id > 0) {
            $police = Polisies::where([
                'id'        => $id, 
                "user_id"   => $user->id
            ])->first();
            if($police) {
                $police->update([
                    'subscribed' => 1
                ]);
            }
        }
        return response()->json([
            'status' => true
        ]);
    }

    public function addAPolic()
    {
        request()->session()->put('register_address', '');
        request()->session()->put('register_appartment', '');
        request()->session()->put('register_confirm', '');
        request()->session()->put('register_payment_status', '');
        request()->session()->put('register_tarrif_id', '');
        request()->session()->put('register_cart', '');
        request()->session()->put('register_password', '');
        request()->session()->put('register_polis_id', '');
        request()->session()->put('register_cash_id', '');
        request()->session()->put('register_another_polic', '');

        $user = $this->getUser();
        request()->session()->put('register_another_polic', true);

        request()->session()->put('register_phone', $user->phone);
        request()->session()->put('register_email', $user->email);
        request()->session()->put('register_user_id', $user->id);

        return response()->json([
            'status' => true,
            'data' => request()->session()->get('register_another_polic')
        ]);
    }

    public function getPlans(Request $request)
    {
        return Plans::all();
    }

    public function getTarrifs(Request $request)
    {
        $id = $request["id"];
        $data = [];
        if( $id ) {
            $tarrifs = Tarrifs::orderBy('sort', 'desc')->get();
            $user = $this->getUser();
            $policy = Polisies::find($id);
            if( $policy && $policy->user_id == $user->id ) {
                $status = true;

                foreach ($tarrifs as $key => $tarrif) {
                    $tarrifs[$key]->amount = $this->priceFormat($tarrif->price);
                }

                $policy->finish = Carbon::createFromTimestamp(strtotime($policy->finish))->subDays(3);

                $data["policy"] = $policy;
                $data["tarrifs"] = $tarrifs;
            } else {
                $status = false;
            }
            return response()->json([
                'status' => $status,
                'data' => $data
            ]);
        }
    }

    public function setTarrifs(Request $request)
    {
        $tarrif_id = $request["tarrif_id"];
        $police_id = $request["police_id"];
        $time = null;
        $data = [];
        if( $tarrif_id > 0 && $police_id > 0 ) {
            $tarrif = Tarrifs::find($tarrif_id);
            $policy = Polisies::find($police_id);
            $user = $this->getUser();
            if( $policy && $policy->user_id == $user->id ) {
                $status = true;
                $time = time();
                if($policy->finish && strtotime($policy->finish) > $time) {
                    $time = strtotime($policy->finish);
                }
                Polisies::where('id', $police_id)
                ->update([
                    "start"             => date('Y-m-d H:i:s', $time),
                    "changed_tarrif"    => $tarrif_id
                ]);
                //request()->session()->put('account_message', 'Вы успешно изменили тариф');
                //System::create([
                //    "user_id" => $user->id
                //    "name"  => "Изменение тарифа пользователя",
                //    "value" => $tarrif_id
                //]);

            } else {
                $status = false;
            }
            
        }

        return response()->json([
            'status' => $status,
            'data' => ($time > 0) ? date('d.m.Y', $time) : false
        ]);
    }

    public function canselSubscribe(Request $request)
    {
        $status = true;
        $police_id = $request["police_id"];
        $policy = Polisies::find($police_id);
        $user = $this->getUser();
        $data = [];
        if($policy && $policy->user_id == $user->id) {

            $update = [
                "subscribed"      => 0
            ];

            $start = Carbon::createFromTimestamp(strtotime($policy->start));
            $start->addDays(14);

            if(time() > $start->timestamp) {
                $update["active"] = 0;
                $this->sendToAmo(2, $policy->bso, $user->phone);
            }else
                $this->sendToAmo(1, $policy->bso, $user->phone);


            Polisies::where('id', $police_id)->update($update);
            request()->session()->put('account_message', 'Вы успешно отписались от подписки');
            
        } else
            $status = false;
        return response()->json([
            'status' => $status,
            'data' => $data
        ]); 
    }

    public function sendToAmo($status, $number, $phone){
        $client = new Client();
        $crm = $client->crm();



        $contact = null;
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

    public function canselVote(Request $request)
    {
        $status = true;
        $police_id = $request["police_id"];
        $police_vote = $request["vote"];
        $policy = Polisies::find($police_id);
        $user = $this->getUser();
        if($policy && $police_vote) {
            if($policy->user_id == $user->id) {
                Polisies::where('id', $police_id)
                ->update([
                    "vote"      => htmlspecialchars($police_vote)
                ]);
            } else
                $status = false;
        }
        return response()->json([
            'status' => $status
        ]); 
    }

    public function priceFormat($price)
    {
        return number_format($price, 0, "", " ");
    }

    private function sortPolices($polices, $id)
    {
        $arr1 = [];
        $arr2 = [];
        foreach ($polices as $key => $police) {
            if( $police->id == $id ) {
                $arr1[] = $police;
            }
            else
                $arr2[] = $police;
        }

        return array_merge($arr1, $arr2);
    }
}
