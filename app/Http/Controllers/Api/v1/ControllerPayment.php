<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Bso;
use App\Models\BsoIndexes;
use App\Myclasses\Client;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payments;
use App\Models\Polisies;
use App\Models\Tarrifs;
use Illuminate\Support\Facades\Log; 

use App\Myclasses\CloudPayment;

class ControllerPayment extends Controller
{
    protected function init()
    {
        return new CloudPayment;
    }

    public function index(Request $request)
    {
        $user = (new User)->currentUser();
        $response = false;
        if ($user) {
            $tarrif_id = isset($request["tarrif_id"]) ? $request["tarrif_id"] : false;
            $tarrif = Tarrifs::find($tarrif_id);
            if ($tarrif) {
                if ($user->RebillId && $user->RebillId > 0) {
                    $this->charge_flag = true;
                    $tinkoff = $this->configurateData($user, $tarrif);

                    if ($tinkoff && $tinkoff->Success) {
                        $result = $tinkoff;
                        $response = $this->makeRecurent($tinkoff, $user);
                        if (isset($response->Success) && !$response->Success)
                            $response = $result;
                    }
                } else
                    $response = $this->createOrder($user, $tarrif);
            }
        }

        return response()->json([
            'data' => $response
        ]);


    }

    public function changingCard(Request $request)
    {
        $user = (new User)->currentUser();
        $response = false;
        if ($user) {
            $init = $this->init();
            $payment = Payments::where([
                "Amount" => 1,
                "user_id" => $user->id,
                "Status" => "Completed"
            ])
                ->orderBy("id", "desc")
                ->first();
            if ($payment) {


                $charge = $init->Cancel($payment->TransactionId, 1);
                $response = $charge;
            }
        }

        return response()->json([
            'data' => $response
        ]);
    }

    public function changeCard()
    {
        $user = (new User)->currentUser();
        $response = false;
        if ($user) {
            $payment = Payments::select("CardId", "PaymentId")
                ->where([
                    "Amount" => 1,
                    "user_id" => $user->id,
                    "Status" => "Completed"
                ])
                ->orderBy("id", "desc")
                ->first();

            if ($payment && $payment->CardId) {
                $tinkoff = $this->init();
                $params = [
                    "CustomerKey" => "user{$user->id}"
                ];

                $tinkoff->AddMainInfoParams($params);

                $cards = $tinkoff->GetCardList();
                foreach ($cards as $key => $card) {
                    if ($card->CardId != $payment->CardId) {
                        $tinkoff = $this->init();
                        $params = [
                            "CustomerKey" => "user{$user->id}",
                            "CardId" => $card->CardId
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
        //if(strpos($_SERVER['HTTP_USER_AGENT'], 'Apache-HttpClient/') === false) return;
        //$pay = $this->init();
        //return $charge();
        //dd($result);
        //exit;


        $req = $request->all();
        if ($req) {
            ob_start();
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/storage/logs/payment_checking' . date('dmY') . '.log', date('[H:i:s] ') . "\r\nRESULT:::\r\n" . print_r($req, true) . "\r\n=====\r\n", FILE_APPEND);
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
            'PaymentId' => $result->PaymentId,
            "RebillId" => $user->RebillId
        ];

        $tinkoff->AddMainInfoParams($params);

        return $tinkoff->Charge();
    }

    protected function createOrder($user, $tarrif)
    {
        $resp = $this->configurateData($user, $tarrif);

        if (!$resp || !is_object($resp)) return null;

        $response = [
            'Success' => $resp->Success
        ];
        if (isset($resp->Message))
            $response["Message"] = $resp->Message;

        if (isset($resp->PaymentURL))
            $response["PaymentURL"] = $resp->PaymentURL;

        $response['user_id'] = $user->id;

        return $response;
    }

    protected function configurateData($user, $tarrif, $price = false, $confirm_bank = true)
    {
        if (!$user) return false;
        $tinkoff = $this->init();

        $payments = false;
        $data = [
            'user_id' => $user->id
        ];
        if ($tarrif)
            $data['tarrif_id'] = $tarrif->id;

        if (!$price)
            $price = $tarrif->price;
        // * $tarrif->per_month

        if ($price > 0) {
            $data["Amount"] = $price;
            $oder_id = request()->session()->get('order_id');
            $payments = Payments::where([
                'id' => $oder_id,
                'Status' => null
            ])->first();

            if ($payments) {
                $payments->update($data);
                $payments = Payments::find($oder_id);
            } else
                $payments = Payments::create($data);
        }

        if ($payments) {
            request()->session()->put('order_id', $payments->id);
            $orderId = $payments->id . "_" . $user->id;
            $tinkoff->AddMainInfo(
                array(
                    'OrderId' => $orderId,
                    //'Description' => 'Описание заказа',
                )
            );
            $tinkoff->AddItem(
                array(
                    'Name' => $tarrif->name,
                    'Price' => $price * 100,
                    "Quantity" => (float)1.00,
                    "Tax" => "none",
                )
            );
            $tinkoff->SetTaxation('usn_income');

            if ($user->phone)
                $tinkoff->SetOrderMobile(preg_replace("/[\D]/", "", $user->phone));

            if ($user->user_email)
                $tinkoff->SetOrderEmail($user->user_email);

            if ($confirm_bank) {
                $result = $tinkoff->AddCustomer("user{$user->id}");
                if ($result->Success) {
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

            if (isset($this->charge_flag))
                $tinkoff->charge_flag = true;

            return $tinkoff->Init();
        }
        return false;
    }

    public function checkingInterval(Request $request)
    {
        $response = null;
        $payment = false;
        $user_id = (isset($request['user_id'])) ? $request['user_id'] : false;
        $user = (new User)->currentUser();


        if ($user) {
            $response = [
                'user' => [
                    'id' => $user->id,
                    'user_phone' => $user->phone,
                ]
            ];
            $tarrif_id = (isset($request['tarrif_id'])) ? $request['tarrif_id'] : null;
            $refund = (isset($request['refund'])) ? $request['refund'] : null;

            $order_id = request()->session()->get('order_id');
            if ($order_id > 0)
                $payment = Payments::where([
                    "id" => $order_id,
                    //"Status" => null
                ])->first();
            $token = $user->Token;


            if (($tarrif_id && $tarrif_id != 'undefined') || $refund) {


                if (!$payment)
                    $payment = Payments::create([
                        'user_id' => $user->id,
                        'tarrif_id' => $tarrif_id,
                        'address' => request()->session()->get('address')
                    ]);

                if ($payment) {
                    $order_id = request()->session()->put('order_id', $payment->id);
                    $data = [
                        "phone" => request()->session()->get('register_phone'),
                        "address" => request()->session()->get('register_address'),
                        "appartment" => request()->session()->get('register_appartment')
                    ];
                    $payment->update(["data" => serialize($data)]);
                    if (!empty($token)) {

                        $tariff = Tarrifs::find($tarrif_id);

                        if ($tariff) {

                            $cloud = $this->init();
                            $res = $cloud->charge($tariff->price, $user->AccountId, $token, $payment->id);
                            if ($res['Success'] && $res['Model']['Status'] == 'Active')
                                $response['payed'] = 1;
                            else {
                                $response['payed'] = 0;
                                $response["errors"] = $res['Message'];  //$res['Model']['CardHolderMessage'];
                            }
                        }
                    }
                    if (!isset($response['payed']) || (isset($response['payed']) && $response['payed'] !== 0)) {
                        $response["payment"] = Payments::find($payment->id);
                    }
                }
            } elseif (!($tarrif_id && $tarrif_id != 'undefined'))
                $response['errors'] = "Не указан тариф";
        }

        return response()->json([
            'data' => $response
        ]);
    }

    //Получение id пользователя из массива банка
    protected function getUser($data)
    {
        if ($data["InvoiceId"] && $data["InvoiceId"] > 0) {
            $payment = Payments::find($data["InvoiceId"]);
            if ($payment && $payment->user_id > 0) {
                return User::find($payment->user_id);
            }
        }
        return false;
    }

    //Получение id полиса из массива банка
    protected function getPaymentId($data)
    {
        if (!is_array($data) && !$data["InvoiceId"]) return false;
        return $data["InvoiceId"];
    }

    //Сбор необходимых для записи в БД данных
    protected function getData($data)
    {
        if (is_array($data)) {
            $arrayData = [];
            if (isset($data['InvoiceId']) && $data['InvoiceId'] > 0)
                $arrayData["InvoiceId"] = $data['InvoiceId'];

            if (isset($data['Status']) && $data['Status'])
                $arrayData["Status"] = $data['Status'];

            if (isset($data['Amount']) && $data['Amount'] > 0)
                $arrayData["Amount"] = $data['Amount'];

            if (isset($data['AccountId']) && $data['AccountId'])
                $arrayData["AccountId"] = $data['AccountId'];

            if (isset($data['CardFirstSix']) && $data['CardFirstSix'])
                $arrayData["CardFirstSix"] = $data['CardFirstSix'];

            if (isset($data['CardLastFour']) && $data['CardLastFour'])
                $arrayData["CardLastFour"] = $data['CardLastFour'];

            if (isset($data['CardType']) && $data['CardType'])
                $arrayData["CardType"] = $data['CardType'];

            if (isset($data['TransactionId']) && $data['CardType'])
                $arrayData["TransactionId"] = $data['TransactionId'];

            return $arrayData;
        }

        return false;
    }

    //Обработка платежей от банка
    protected function getPayment($data)
    {

        $payment_id = $this->getPaymentId($data);
        $user = $this->getUser($data);
        $payment = false;

        if ($payment_id > 0 && $user) {
            $payment = Payments::where([
                "id" => $payment_id,
                "user_id" => $user->id
            ])->first();

        }


        if ($payment) {
            //Обработка платежей в статусе Refund
            if (isset($data['OperationType']) && $data['OperationType'] == "Refund") {
                $payment->update(['Status' => "Refund"]);
                return false;
            }

            if (isset($data['Type']) && $data['Type'] == "Income") {
                $payment->update(['link' => $data['Url']]);
                return false;
            }

            if (isset($data['Status']) && $data['Status'] == "Declined") {
                //$payment->update(['link' => $data['Url']]);

                if(!empty($payment->polic_id)) {
                    $polic = Polisies::where([
                        'id' => $payment->polic_id,
                    ])->first();


                    // Тут неудавшийся платеж!
                    $this->sendToAmo($polic->bso, $user->phone);
                }
                return false;
            }

        }


        if ($user && $payment) {
            $this->UpdateToken($data, $user);
            return $this->insertPayment($data, $payment, $user);
        } else {
            $data = $this->getData($data);
            if ($user && is_object($user) && isset($user->id))
                $data['user_id'] = $user->id;
            elseif (request()->session()->get('register_user_id') > 0)
                $data['user_id'] = request()->session()->get('register_user_id');

            Payments::create($data);
        }


        //return response("OK", 200);
    }

    public function sendToAmo($number, $phone)
    {
        $client = new Client();
        $crm = $client->crm();

        $contact = null;
        $contacts = $crm->contacts()->searchByPhone($phone);

        if ($contact = $contacts->first()) {
            $leads = $contact->leads->filter(function ($lead) use (&$number) {
                return $lead->cf()->byId(335005)->getValue() == $number;
            });

            if ($leads->count()) {
                if ($lead = $leads->first()) {
                    $lead->status_id = 35724097;    // отписка до 14 дней
                    $lead->save();
                }
            }
        }


    }

    //Созать или обновить платежку в базе
    protected function insertPayment($data, $payment, $user)
    {
        $data = $this->getData($data);

        if (isset($data['InvoiceId']) && $data['InvoiceId']) {
            $payment = Payments::where([
                "user_id" => $user->id,
                "id" => $payment->id,
            ])->first();

            if ($payment) {
                if (isset($data['AccountId'])) unset($data['AccountId']);

                $data["user_id"] = $user->id;
                if ($payment->update($data)) {
                    //$order_id = request()->session()->put('order_id', "");
                    return true;
                }

            }
        }
    }

    //Обновить идентификационный номер плательщика банка. Это нужно для рекуррентных платежей
    protected function UpdateToken($data, $user)
    {
        if (isset($data["Token"]) && !empty($data["Token"])) {
            $user->update([
                'Token' => $data["Token"]
            ]);
        }

        if (isset($data["AccountId"]) && !empty($data["AccountId"])) {
            $user->update([
                'AccountId' => $data["AccountId"]
            ]);
        }
    }

    /***
     * Получить ID полиса
     */
    public function getPoliseId($paymentId, $userId)
    {

        $obPayment = Payments::where('id', $paymentId)
            ->where("user_id", $userId)
            ->first();
        if ($obPayment) {
            $arPayment = $obPayment->toArray();
            return $arPayment["polic_id"];
        }
        return false;
    }
}
