<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Bso;
use App\Models\BsoIndexes;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payments;
use App\Models\Polisies;
use App\Models\Tarrifs;

use App\Myclasses\Tinkoff;

class ControllerPayment extends Controller
{
    protected function init()
    {
        return new Tinkoff([
            'TerminalKey' => '1606817480884DEMO', // Терминал
            'Password'    => 'u0npr7cqaz2jkfkp', // Пароль
        ]);
    }

    public function index(Request $request) {
        $user = (new User)->currentUser();
        $response = false;
        if($user) {
           $tarrif_id = isset($request["tarrif_id"]) ? $request["tarrif_id"] : false;
           $tarrif = Tarrifs::find($tarrif_id);
           if($tarrif) {
                if($user->RebillId && $user->RebillId > 0) {
                    $this->charge_flag = true;
                    $tinkoff = $this->configurateData($user, $tarrif);

                    if($tinkoff && $tinkoff->Success) {
                        $result = $tinkoff;
                        $response = $this->makeRecurent($tinkoff, $user);
                        if( isset( $response->Success ) && ! $response->Success )
                            $response = $result;
                    }
                } else
                    $response = $this->createOrder($user, $tarrif);
           }
        }
        
        return response()->json([
           'data' => $response
        ]);

        //$tinkoff = $this->init();
        //////Обычный платеж
		//$tinkoff->AddMainInfo(
		//   array(
		//       'OrderId'     => "1_31",
		//       'Description' => 'Описание заказа',
		//   )
		//);
		//$tinkoff->AddItem(
		//   array(
		//       'Name'     => 'Название товара',
		//       'Price'    => 2000,
		//       "Quantity" => (float) 1.00,
		//       "Tax"      => "none",
		//   )
		//);
		//$tinkoff->SetOrderEmail('flayder111@gmail.com');
        //$tinkoff->SetOrderMobile('79233333573');
		//$tinkoff->SetTaxation('usn_income');
		
        //$result = $tinkoff->AddCustomer("user31");
        //if($result->Success) {
        //    $tinkoff->SetRecurrent();
        //    $params = [
        //        'CustomerKey' => $result->CustomerKey,
        //        'SendEmail' => true,
        //        'InfoEmail' => 'flayder111@gmail.com'
        //    ];
        //    $tinkoff->AddMainInfoParams($params);
        //}
        //$result = $tinkoff->Init();
        //$tinkoff->ClearOrder();
        //////Рекуррентный платеж
        //$params = [
        //   'PaymentId'   => $result->PaymentId,
        //   //'SendEmail'   => true,
        //   //'InfoEmail'   => 'flayder111@gmail.com',
        //   "RebillId"    => "1608030431803"
        //];
        //$tinkoff->AddMainInfoParams($params);
		//$result = $tinkoff->Charge();

        //Возврат денег
		//$result = $tinkoff->Cansel(389596215, 100);

        ////Редирект
        //$result = $tinkoff->doRedirect();

		//print_r($result);
        //exit;
    }

    public function changingCard(Request $request) {
        $user = (new User)->currentUser();
        $response = false;
        if($user) {
           $response = $this->createOrder($user, false);
        }
        
        return response()->json([
           'data' => $response
        ]);
    }

    public function changeCard()
    {
        $user = (new User)->currentUser();
        $response = false;
        if($user) {
            $payment = Payments::select("CardId", "PaymentId")
                ->where([
                    "Amount"    => 1,
                    "user_id"   => $user->id,
                    "Status"    => "CONFIRMED"
                ])
                ->orderBy("id", "desc")
                ->first();

            if($payment && $payment->CardId) {
                $tinkoff = $this->init();
                $params = [
                    "CustomerKey"    => "user{$user->id}"
                ];

                $tinkoff->AddMainInfoParams($params);

                $cards = $tinkoff->GetCardList();
                foreach ($cards as $key => $card) {
                    if($card->CardId != $payment->CardId) {
                        $tinkoff = $this->init();
                        $params = [
                            "CustomerKey"    => "user{$user->id}",
                            "CardId"         => $card->CardId
                        ];

                        $tinkoff->AddMainInfoParams($params);
                        $response = $tinkoff->RemoveCard();

                        $tinkoff = $this->init();
                        $tinkoff->Cansel($payment->PaymentId, 1);
                        break;
                    }
                }
            }
        }
        
        return response()->json([
            'data' => $response
        ]);
    }

    public function checking(Request $request)
    {
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'Apache-HttpClient/') === false) return;

    	$req = $request->all();
    	if($req) {
    		ob_start();
            //file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/storage/logs/payment_checking' . date('dmY') . '.log', date('[H:i:s] ') . "\r\nRESULT:::\r\n" . print_r($req, true) . "\r\n=====\r\n", FILE_APPEND);
            $this->getPayment($req);

    		$out = ob_get_clean();
    	}
        echo "OK";
    }

    protected function makeRecurent($tinkoff, $user)
    {
        $result = $tinkoff;
        //file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/storage/logs/payment_rec_data.log', date('[H:i:s] ') . "\r\nRESULT:::\r\n" . print_r($result, true) . "\r\n=====\r\n", FILE_APPEND);
        //if( ! $tinkoff instanceof Tinkoff  || ! $user instanceof User ) return false;
        $tinkoff = $this->init();
        $tinkoff->ClearOrder();

        $params = [
           'PaymentId'   => $result->PaymentId,
           "RebillId"    => $user->RebillId
        ];

        $tinkoff->AddMainInfoParams($params);

        return $tinkoff->Charge();
    }

    protected function createOrder($user, $tarrif)
    {
        $resp = $this->configurateData($user, $tarrif);

        if( ! $resp || ! is_object($resp) ) return null;

        $response = [
            'Success' => $resp->Success
        ];
        if(isset($resp->Message))
            $response["Message"] = $resp->Message;

        if(isset($resp->PaymentURL))
            $response["PaymentURL"] = $resp->PaymentURL;

        $response['user_id'] = $user->id;

        return $response;
    }

    protected function configurateData($user, $tarrif, $price = false, $confirm_bank = true)
    {
        if( ! $user ) return false;
        $tinkoff = $this->init();

        $payments = false;
        $data = [
            'user_id' => $user->id
        ];
        if( $tarrif )
            $data['tarrif_id'] = $tarrif->id;

        if( ! $price )
            $price = $tarrif->price;
            // * $tarrif->per_month

        if($price > 0) {
            $data["Amount"] = $price;
            $oder_id = request()->session()->get('order_id');
            $payments = Payments::where([
                'id' => $oder_id,
                'Status' => null
            ])->first();

            if($payments) {
                $payments->update($data);
                $payments = Payments::find($oder_id);
            }
            else
                $payments = Payments::create($data);
        }

        if($payments) {
            request()->session()->put('order_id', $payments->id);
            $orderId = $payments->id . "_" . $user->id;
            $tinkoff->AddMainInfo(
                array(
                    'OrderId'     => $orderId,
                    //'Description' => 'Описание заказа',
                )
            );
            $tinkoff->AddItem(
                array(
                    'Name'     => $tarrif->name,
                    'Price'    => $price * 100,
                    "Quantity" => (float) 1.00,
                    "Tax"      => "none",
                )
            );
            $tinkoff->SetTaxation('usn_income');

            if($user->phone)
                $tinkoff->SetOrderMobile(preg_replace("/[\D]/", "", $user->phone));

            if($user->user_email)
                $tinkoff->SetOrderEmail($user->user_email);

          	if($confirm_bank) {
          		$result = $tinkoff->AddCustomer("user{$user->id}");
          		if($result->Success) {
          		    $tinkoff->SetRecurrent();
          		    $params = [
          		        'CustomerKey' => $result->CustomerKey
          		    ];
          		    $tinkoff->AddMainInfoParams($params);
          		}
          	} else {
          		$tinkoff->SetRecurrent();
          		$params = [
          		    'CustomerKey' => "user{$user->id}"
          		];
          		$tinkoff->AddMainInfoParams($params);
          	}

            if(isset($this->charge_flag))
                $tinkoff->charge_flag = true;

            return $tinkoff->Init();
        }
        return false;
    }

    public function checkingInterval(Request $request)
    {
        $response = null;
        $user_id = (isset($request['user_id'])) ? $request['user_id'] : false;
        if(!$user_id) {
            $user = (new User)->currentUser();
            if($user)
                $user_id = $user->id;
        }

        $tarrif_id = (isset($request['tarrif_id'])) ? $request['tarrif_id'] : false;
        $order_id = request()->session()->get('order_id');

        if($order_id > 0) {
            $response = Payments::select("Status")->find($order_id);
            if(!$response)
                $order_id = false;
        }
        if(!$order_id && $user_id && $tarrif_id) {
            $response = Payments::select("Status")->where([
                "user_id"   => $user_id,
                "tarrif_id" => $tarrif_id
            ])->orderBy("id", "desc")->first();
        }
        return response()->json([
            'data' => $response
        ]);
    }

    //Получение id пользователя из массива банка
    protected function getUser($data)
    {
    	if( ! is_array($data) && ! isset($data["OrderId"]) ) return false;
    	$orderArr = explode("_", $data["OrderId"]);
    	if( isset($orderArr[1]) )
    		return User::find($orderArr[1]);

    	return false;
    }

    //Получение id полиса из массива банка
    protected function getPaymentId($data)
    {
    	if( ! is_array($data) ) return false;
    	$orderArr = explode("_", $data["OrderId"]);
    	if( isset($orderArr[0]) && $orderArr[0] > 0)
    		return $orderArr[0];

    	return false;
    }

    //Сбор необходимых для записи в БД данных
    protected function getData($data)
    {
    	if( is_array($data) ) {
    		$arrayData = [];
    		if(isset($data['PaymentId']) && $data['PaymentId'] > 0)
    			$arrayData["PaymentId"] = $data['PaymentId'];

    		if(isset($data['Status']) && $data['Status'])
    			$arrayData["Status"] = $data['Status'];

    		if(isset($data['Amount']) && $data['Amount'] > 0)
    			$arrayData["Amount"] = $data['Amount'] / 100;

    		if(isset($data['CardId']) && $data['CardId'] > 0)
    			$arrayData["CardId"] = $data['CardId'];

    		if(isset($data['Pan']) && $data['Pan'])
    			$arrayData["Pan"] = $data['Pan'];

    		if(isset($data['OrderId']) && $data['OrderId'])
    			$arrayData["OrderId"] = $data['OrderId'];

    		return $arrayData;
    	}

    	return false;
    }

    //Обработка платежей от банка
    protected function getPayment($data) {
        if(isset($data['Status']) && $data['Status'] == "AUTHORIZED") return;

    	$payment_id = $this->getPaymentId($data);
        $user = $this->getUser($data);
    	$payment = false;
    	if($payment_id > 0 && $user) {
    		$payment = Payments::where([
    			"id" 		=> $payment_id,
    			"user_id"	=> $user->id
    		])->first();
    	}


    	if($user && $payment) {
            $this->UpdateRebillId($data, $user);
    		return $this->insertPayment($data, $payment, $user);
    	} else {
            $data = $this->getData($data);
            if($user && is_object($user) && isset($user->id))
                $data['user_id'] = $user->id;
            elseif(request()->session()->get('register_user_id') > 0)
                $data['user_id'] = request()->session()->get('register_user_id');

            Payments::create($data);
        }


        //return response("OK", 200);
    }

    //Созать или обновить платежку в базе
    protected function insertPayment($data, $payment, $user)
    {
        $data = $this->getData($data);

        if(isset($data['PaymentId']) && $data['PaymentId']) {
            $payment = Payments::where([
                "user_id"   => $user->id,
                "id"        => $payment->id,
            ])->first();

            if($payment) {

                $data["user_id"] = $user->id;
                if($payment->update($data)){
//                    $policeId = $this->getPoliseId($payment->id,$user->id);
//                    $arLog = [
//                        "policeID" => $policeId,
//                        "paymentID" => $payment->id,
//                        "userID" => $user->id
//                    ];
//                    file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/storage/logs/payment_BOS3_' . date('dmY') . '.log', date('[H:i:s] ') . "\r\nGET_PAYMENTS 434:::\r\n" . print_r($arLog, true) . "\r\n=====\r\n", FILE_APPEND);
//                    if($policeId){
//                        $this->generateBos($policeId);
//                    }
                    return true;
                }

            }
        }
    }

    //Обновить идентификационный номер плательщика банка. Это нужно для рекуррентных платежей
    protected function UpdateRebillId($data, $user)
    {
        if(isset($data["RebillId"]) && $data["RebillId"] > 0) {
            $user->update([
                'RebillId' => $data["RebillId"]
            ]);
        }
    }

    /***
     * Генерируем BOS для полиса
     */
    public function generateBos($policeId){

        $arBso = Bso::first()->toArray();
        $pattern = $arBso["field_".$arBso["company"]];

        $arNewData = [
            "company" => $arBso["company"],
            "pattern" => $pattern,
            "index" => 1
        ];
        $lastRow = BsoIndexes::where("company",$arBso["company"])
            ->where("pattern",$pattern)
            ->orderBy('index','desc')
            ->first();
        if($lastRow){
            $arLastRow = $lastRow->toArray();
            $policeIndex = intval($arLastRow["index"]) + 1;
        }else{
            BsoIndexes::create($arNewData);
            $policeIndex = 1;
        }

        $arReplace = [
            "YYYY" => date("Y"),
            "YY" => date("y"),
            "******" => sprintf("%'.06d", $policeIndex)
        ];
        $generatedBso = str_replace(array_keys($arReplace),$arReplace,$pattern);
        if(Polisies::where("id",$policeId)->update(["bso"=>$generatedBso,"company"=>$arBso["company"]])){
            if($policeIndex > 1 ){
                $arNewData["index"] = $policeIndex;
                BsoIndexes::create($arNewData);
            }
        }

        return true;
    }

    /***
     * Получить ID полиса
     */
    public function getPoliseId($paymentId, $userId){

        $obPayment = Payments::where('id',$paymentId)
            ->where("user_id",$userId)
            ->first();
        if($obPayment){
            $arPayment = $obPayment->toArray();
            return $arPayment["polic_id"];
        }
        return false;
    }
}
