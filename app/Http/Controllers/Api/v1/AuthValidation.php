<?php

namespace App\Http\Controllers\Api\v1;

use Cookie;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tarrifs;
use App\Models\Polisies;
use App\Models\Payments;
use App\Myclasses\SmsSender;
use App\Models\Bso;
use App\Models\Plans;
use App\Models\BsoIndexes;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AuthValidation extends Controller
{
    public function index()
    {
    	$request = request()->all();
    	$status = true;
    	$errors = [];
    	$phone = isset($request["phone"]) ? $request["phone"] : "";
    	$sms = null;
    	$data = [];

        if(isset($request["action"]) && $request["action"] == "checking") {

            $data = $this->getAllData();
            $user = (new User)->currentUser();

            if( ! $data['email'] && $user && ! empty($user->user_email) ) {
            	request()->session()->put('register_email', $user->user_email);
                $data['email'] = $user->user_email;
            } else {
                $data['email'] = "";
                request()->session()->put('register_email', "");
            }

            if( ! $data["phone"] && $user ) {
                $data["phone"] = $user->phone;
                request()->session()->put('register_phone', $user->phone);
            }

            if( ! $data["user_id"] && $user ) {
                $data["user_id"] = $user->id;
                request()->session()->put('register_user_id', $user->id);
            }

            if( ! $data["another_polic"] && $user ) {
                $count = Polisies::where('user_id', $user->id)->count();
                if($count > 0) {
                    $data["another_polic"] = true;
                    request()->session()->put('register_another_polic', true);
                } else {
                    $data["another_polic"] = false;
                    request()->session()->put('register_another_polic', '');
                }
            }

            if( ! $data['cash_id'] && $user && is_null($user->cashback_id) ) {
                request()->session()->put('register_cash_id', $user->cashback_id);
                if( $user->cashback_id > 0 ) $data['cash_id'] = $user->cashback_id;
            } else {
                $data['cash_id'] = "";
                request()->session()->put('register_cash_id', "");
            }
        }

        //if(isset($request["remove"]) && $request["remove"])
        //    //$this->removeAllData();

        if(isset($request["action"]) && $request["action"] == "address") {
            $address = $request['address'];
            $appartment = $request['appartment'];
            if(!$address)
                $errors[] = [
                    'address' => "Вы не ввели адрес"
                ];
            elseif(!$appartment)
                $errors[] = [
                    "appartment" => "Вы не ввели номер квартиры"
                ];
            else {
                $polis_checking = Polisies::where([
                    'active' => 1,
                    'address' => $address,
                    'appartment' => $appartment
                ])->first();

                if($polis_checking)
                    $errors[] = ["address" => "Полис с таким адресом и номером квартиры уже существует"];
                else {
                    if( $address && $appartment ) {
                        request()->session()->put('register_address', $address);
                        request()->session()->put('register_appartment', $appartment);
                    }
                }
            }
        }

    	if(isset($request["action"]) && $request["action"] == "phone") {
            $redirect = isset($request['redirect']) ? $request['redirect'] : false;

            $data = $this->getAllData();
    		$code = rand(1000, 9999);
            if($phone || $phone && isset($request["again"]))
                request()->session()->put('register_phone', $phone);
            if($code)
                request()->session()->put('register_code', $code);

            $data = $this->getAllData();
            $sendSms = true;
            if($redirect == "/authpayment") {
                $user = User::where('name', $this->cleanPhone($data["phone"]))->first();
                if( ! $user ) {
                    $user = $this->createUser($data);

                    $this->putId($user->id);

                    (new User)->login($user);
                    $sendSms = false;
                }
            }
            
            if($sendSms) {
              $send = new SmsSender("+".$this->cleanPhone($data["phone"]), $code . " — ваш код подтверждения для входа в inapp.insure");
              $smsData = $send->getResponse();
              if($smsData->status == "ERROR")
              	    $errors[] = $smsData->status_text;
            } else {
                $data["nextStep"] = "/authpayment";
            }

            //$data["sms"] = $smsData;
    	}

    	if(isset($request["action"]) && $request["action"] == "sms") {
    		request()->session()->put('register_confirm', '');
    		$sms = $request['sms'];
            $data = $this->getAllData();
    		if( $data['phone'] ) {
    			$session_code = request()->session()->get('register_code');

    			if($session_code != $sms) {

    				$errors[] = ["sms" => "Неверный код подтверждения"];
    			}
    			else {
                    //Проверка есть ли пользователь в базе, если нет, то создаем нового и пропускаем этап отправки смс
                    $user = User::where('name', $this->cleanPhone($data['phone']))->first();
                    if( ! $user) {

                        $user = $this->createUser($data);

                        if($user) {
                            $authorize = (new User)->login($user);
                            $this->putId($user->id);
                        }
                    } else {
                        $authorize = (new User)->login($user);
                        $this->putId($user->id);
                    }
                    $data['register_confirm'] = true;
                    request()->session()->put('register_confirm', true);
    			}
    		} else
    			$errors[] = ["phone" => "Не найден телефон"];
    	}

        if(isset($request["action"]) && $request["action"] == "payment") {
            $payment_status = $request["payment_status"];
            $tarrif_id = $request["tarrif_id"];
            $data = $this->getAllData();

            if(! $payment_status)
                $errors[] = ["er" => "Вы не оплатали тариф"];

            if(! $tarrif_id)
                $errors[] = ["er" => "Вы не выбрали тариф"];

            if($data['another_polic']) {
                $user_activate = $request['user_activate'];
                if(!$user_activate)
                    $errors[] = ["user_activate" => "Вы не ввели дату активации"];
            }

            if(count($errors) == 0) {
                request()->session()->put('register_payment_status', $payment_status);
                request()->session()->put('register_tarrif_id', $tarrif_id);
                $order_id = request()->session()->get('order_id');
                Log::info('orderID: ', [$order_id]);
                //$order_id = 434;
                
                //Проверка существует ли плата с нужным статусом от банка
                $payment = $this->checkPayment($order_id);
                //Проверка на корректность данных в сессии
                if( ! $this->checkAllData($data) ) {
                    if($payment) {
                        //Создание полиса
                        $polic_id = $this->createPolic(1);
                        Log::info('Creted police', [$polic_id]);
                        // $this->generateBos($polic_id);
                        request()->session()->put('account_message', 'Новый полис успешно создан');
                        if($polic_id) {
                            $tarrif = Plans::find($tarrif_id);
                            if($tarrif) {
                                //Запись полиса в оплату
                                $payment->update([
                                    "polic_id" => $polic_id->id
                                ]);
                                $data = $this->getAllData();
                                if($data['another_polic']) {
                                    //Запись даты активации в полис. Этап когда у пользователя 1 и более полисов
                                    $result = $this->proceccingActivate($user_activate);
                                    $data = $result["data"];
                                    $errors = array_merge($errors, $result["errors"]);
                                    if(count($errors) == 0) {
                                        $this->removeAllData();
                                        $data['another_done'] = true;
                                    }
                                }
                            }
                            
                        }
                    } else 
                       $errors[] = ["er" => "Оплата не была пройдена, пожалуйста повторите попытку позже"]; 
                } else 
                    $errors[] = ["er" => "Произошла ошибка с обработкой данных, пожалуйста обратитесь в администрацию для информации"];
            }
        }

        if(isset($request["action"]) && $request["action"] == "email") {
            $data = $this->getAllData();
            $email = $request["email"];

            // if( $cart != $data["cart"] ) {
            //     request()->session()->put('register_cart', $cart);
            //     $data = $this->getAllData();
            // }
            if( ! $data["user_id"] ) 
                $errors[] = ["er" => "Ваши данные не были зарегистрированы, пожалуйста обратитесь в администрацию"];
            elseif( ! filter_var($email, FILTER_VALIDATE_EMAIL) ) {
                $errors[] = ["email" => "Вы ввели некорректные email"];
            } else
                request()->session()->put('register_email', $email);
            if(count($errors) == 0) {
                $data = $this->getAllData();
                if($data["user_id"]) {
                    User::where('id', $data["user_id"])
                    ->update([
                        'user_email'     => $email
                    ]);
                }
                
                $password = request()->session()->get('register_password');
                \Mail::to($email)->send(new \App\Mail\mailGmail());
                //$to      = $email;
                //$subject = 'Новый пароль для входа';
                //$message = 'Новый пароль: ' . $password;
                //$headers = 'From: webmaster@example.com' . "\r\n" .
                //    'Reply-To: webmaster@example.com' . "\r\n" .
                //    'X-Mailer: PHP/' . phpversion();
                //
                //mail($to, $subject, $message, $headers);

                $data["user_is_activated"] = true;

                //$this->removeAllData();
            }
        }

        if(isset($request["action"]) && $request["action"] == "activate") {
            $user_name = $request['user_name'];
            $user_surname = $request['user_surname'];
            $user_birsday = $request['user_birsday'];
            $user_activate = $request['user_activate'];
            $data = $this->getAllData();
            if( $data['polis_id'] ) {
                if($data["user_id"] && $data["tarrif_id"]) {
                    if(!$user_name)
                        $errors[] = ["user_name" => "Вы не ввели имя"];
                    elseif(!$user_surname)
                        $errors[] = ["user_surname" => "Вы не ввели фамилия"];
                    elseif(!$user_birsday)
                        $errors[] = ["user_birsday" => "Вы не ввели дату рождения"];
                    elseif (!$user_activate) {
                        $errors[] = ["user_activate" => "Вы не ввели дату активации полиса"];
                    } else {

                        //Проверим дату рождения (не младше 18-ти)

                        $firstDateTimeObject = new \DateTime();
                        $secondDateTimeObject = new \DateTime($user_birsday);

                        $delta = $secondDateTimeObject->diff($firstDateTimeObject);

                        if($delta->format('%y')<18){
                            $errors[] = ["user_birsday" => "Сервис не обслуживает лиц младше 18 лет"];
                        }

                        //Запись даты активации полиса в базу. Этап создания первого полиса.
                        $result = $this->proceccingActivate($user_activate);
                        $data = $result["data"];
                        $errors = array_merge($errors, $result["errors"]);

                        $this->userUpdate($data["user_id"], $user_name, $user_surname, $user_birsday);
                    }
                }
            } else
                $errors[] = ["er" => "Полис уже активирован"];
        }

        if(isset($request["action"]) && $request["action"] == "cashback") {
            $id = $request["id"];
            $data = [];

            if(!empty($id))
                $id = self::getCashbackIds($id);


            if(count($id) > 0) {
                $user = (new User)->currentUser();
                if($user) {
                    $this->updateCashback($user->id, json_encode($id));
                    request()->session()->put('register_cash_id', json_encode($id));
                    $data["cash_id"] = true;
                }
                
            }
        }

    	if( $errors && count($errors) > 0){
    		$status = false;
    		$data["errors"] = $errors;
    	} elseif($phone)
            $data["phone"] = $phone;

    	return response()->json([
		    'status' => $status,
		    'data' => $data,
		    //'session' => request()->session()->get('register_code'),
		    //'phone' => $phone,
		    //'session_phone' => request()->session()->get('register_phone')
		]);
    }

    function getCashbackIds($cash_id){
        return explode(',',$cash_id);
    }

    public function cleanPhone($phone)
    {
        return preg_replace("/[\D]/", "", $phone);
    }

    private function proceccingActivate($user_activate)
    {
        $errors = [];
        $data = $this->getAllData();
        $start = Carbon::createFromTimestamp(strtotime($user_activate));
        if( $start->timestamp >= Carbon::now()->toDateTimeString() ) {
            $tarrif = Plans::find($data["tarrif_id"]);
            $polis = Polisies::where([
                'id' => $data['polis_id'],
                'active' => 0
            ])->get();

            if( ! isset($polis[0]) )
                $errors[] = ["er" => "Полис уже активирован"];
            elseif($tarrif) {
                $polis = $polis[0];
                $finish = Carbon::createFromTimestamp(strtotime($user_activate))->addMonth($tarrif->period === 'year' ? 12 : 1);
                $checking = Carbon::createFromTimestamp(strtotime($polis->created_at));
                $checking->addDays(2);
                if($checking->timestamp > strtotime($start)) {
                    $errors[] = [
                        "user_activate" => "Дата активации должна быть не менее 3 дней с момента оплаты"
                    ];
                } elseif($finish->format('d.m.Y') != $start->format('d.m.Y')) {
                    $polic_id = $this->updatePolic($data["polis_id"], $start, $finish);
                    if($polic_id) {
                        $data["done"] = true;
                    }
                } else {
                    if($finish->format('d.m.Y') == $start->format('d.m.Y'))
                        $errors[] = ["er" => "Дата начала полиса совпадает с датой его окончания"];
                    else
                        $errors[] = ["er" => "Некорректные данные"];
                }
            }
        } else 
            $errors[] = ["er" => "Дата начала активации полиса не должна быть меньше текущей"];

        return [
            "data"      => $data,
            "errors"    => $errors
        ];
    }

    private function putId($id) {
        request()->session()->put('register_user_id', $id);
    }

    private function updatePolic($polic_id, $start, $finish)
    {
        $polic_id = Polisies::where([
            'id'        => $polic_id,
            'active'    => 0
        ])->update([
            "active" => 1,
            "start" => $start,
            "finish" => $finish,
        ]);
        return $polic_id;
    }

    private function updateCashback($user_id, $cashback_id)
    {
        $user = User::where('id', $user_id)
        ->update([
           'cashback_id'        => $cashback_id,
           'cashback'           => 0
        ]);

        return $user;
    }

    private function userUpdate($user_id, $user_name, $user_surname, $user_birsday)
    {
        $user = User::where('id', $user_id)
        ->update([
           'user_name'     => $user_name,
           'user_surname'  => $user_surname,
           'user_birsday'  => date("Y-m-d H:i:s", strtotime($user_birsday))
        ]);

        return $user;
    }

    private function createUser($data)
    {
        $password = rand(1000000, 9999999);
        $user = User::create([
            "name" => $this->cleanPhone($data["phone"]),
            "phone" => $data["phone"],
            "password" => Hash::make($password),
            "email" => $this->cleanPhone($data["phone"]) . "@polis.ru",
            "cashback" => 0
        ]);

        return $user;
    }

    private function createPayment($user_id, $polic_id, $price)
    {
        $payment = Payments::create([
            "user_id" => $user_id,
            "polic_id" => $polic_id,
            "Amount" => $price
        ]);
        return $payment;
    }

    private function checkPayment($oder_id)
    {
        $data = $this->getAllData();
        if(!isset($data["user_id"]) && !isset($oder_id)) return false;
        return Payments::where([
            "user_id"   => $data["user_id"],
            "id"        => $oder_id,
            "Status"    => "Completed"
        ])->first();
    }

    private function createPolic($status_id = 1)
    {
        $id = request()->session()->get('register_polis_id');
        if(!$id) {
            $data = $this->getAllData();
            
            $polic_id = Polisies::create([
                "active" => 0,
                "user_id" => $data["user_id"],
                "address" => $data["address"],
                "appartment" => $data["appartment"],
                "tarrif_id" => $data["tarrif_id"],
                "status_id" => $status_id
            ]);

            if($polic_id)
                request()->session()->put('register_polis_id', $polic_id->id);
        } else
            $polic_id = Polisies::find($id);

        if($polic_id)
            request()->session()->put('order_id', '');

        $this->generateBos($polic_id->id);
        return $polic_id;
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

    private function getAllData()
    {
        $data = [];
        $data["phone"] = request()->session()->get('register_phone');
        $data["address"] = request()->session()->get('register_address');
        $data["appartment"] = request()->session()->get('register_appartment');
        $data["confirmed"] = request()->session()->get('register_confirm');
        $data["payment_status"] = request()->session()->get('register_payment_status');
        $data["tarrif_id"] = request()->session()->get('register_tarrif_id');
        $data["polis_id"] = request()->session()->get('register_polis_id');
        $data["cart"] = request()->session()->get('register_cart');
        $data["email"] = request()->session()->get('register_email');
        $data["user_id"] = request()->session()->get('register_user_id');
        $data["cash_id"] = request()->session()->get('register_cash_id');
        $data["another_polic"] = request()->session()->get('register_another_polic');
        return $data;
    }



    private function removeAllData()
    {
        request()->session()->put('register_phone', '');
        request()->session()->put('register_address', '');
        request()->session()->put('register_appartment', '');
        request()->session()->put('register_confirm', '');
        request()->session()->put('register_payment_status', '');
        request()->session()->put('register_tarrif_id', '');
        request()->session()->put('register_cart', '');
        request()->session()->put('register_email', '');
        request()->session()->put('register_user_id', '');
        request()->session()->put('register_password', '');
        request()->session()->put('register_polis_id', '');
        request()->session()->put('register_cash_id', '');
        request()->session()->put('register_another_polic', '');
    }

    private function checkAllData($data)
    {
        $error = false;
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'phone':
                    if( empty($value) && strlen($value) < 8 ) {
                        $error = true;
                        Log::info('Check payment phone failed');
                    }
                    break;

                //case 'email':
                //    $user = User::where('email', $value)->first();
                //    if($user)
                //        $error = true;
                //    break;
                
                case 'address':
                    if( empty($value) ) {
                        $error = true;
                        Log::info('Check payment address failed');
                    }
                    break;

                case 'appartment':
                    if( empty($value) ) {
                        $error = true;
                        Log::info('Check payment address failed');
                    }
                    break;
            }
        }

        return $error;
    }

    /***
     * Генерируем BOS для полиса
     */
    /*public function generateBos($policeId){

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
    }*/
}
