<?php

namespace App\Myclasses;


use App\Models\User;
use Carbon\Carbon;

class CloudPayment
{
	protected $public_id = "pk_19ba322d94cdaa881e611754cf8e3";
	protected $api_password = "e7a11a440c59702f971c26d59c967569";

    const API_URL    = 'https://api.cloudpayments.ru/payments/';
    const CHARGE_URL = self::API_URL . 'tokens/charge';
    const SUBSCRIBE_URL = 'https://api.cloudpayments.ru/subscriptions/create';
    const CANCEL_URL = self::API_URL . 'refund';

    /**
     * @var array
     */
    protected $_order = array();

	public function __construct()
	{
		//$this->public_id = $this->$public_id;
		//$this->api_password = self::$api_password;
	}

	function send($url, $params)
	{
    	$params = json_encode($params);

    	if ($curl = curl_init()) {
        	curl_setopt($curl, CURLOPT_URL, $url);
        	curl_setopt($curl, CURLOPT_HEADER, false);
        	curl_setopt($curl, CURLOPT_USERPWD, $this->public_id . ":" . $this->api_password);
        	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        	curl_setopt($curl, CURLOPT_POST, true);
        	curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        	curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            	'Content-Type: application/json',
            	'Content-Length: ' . strlen($params),
        	));
        	$response = curl_exec($curl);
        	curl_close($curl);

            $response = json_decode($response, true);

        	return $response;
    	}
    	return false;
	}

    public function charge($price, $accoun_id, $pay_token,$invoiceId, $period)
    {

        $user = (new User)->currentUser();

        $reciept = [
            "cloudPayments" => [
                "CustomerReceipt"=> [
                    "Items"=> [//товарные позиции
                    [
                        "label"=> "Подписка на страховку", //наименование товара
                        "price"=> $price, //цена
                        "quantity"=> 1.00, //количество
                        "amount"=> $price, //сумма
                        "vat"=> 0, //ставка НДС
                    ]
                ],
                    "calculationPlace"=> "my.inapp.insure", //место осуществления расчёта, по умолчанию берется значение из кассы
                    "taxationSystem"=> 2, //система налогообложения; необязательный, если у вас одна система налогообложения
                    //"email"=> "temuch-13@mail.ru", //e-mail покупателя, если нужно отправить письмо с чеком
                    "phone"=> $user->phone, //телефон покупателя в любом формате, если нужно отправить сообщение со ссылкой на чек
                    "isBso"=> false, //чек является бланком строгой отчётности
                    "AgentSign"=> null, //признак агента, тег ОФД 1057
                    "amounts"=> [ "electronic"=> $price], // Сумма оплаты электронными деньгами
                    ], //онлайн-чек
            ]
        ];

        $params = [
            "token" => $pay_token,
            'accountId' => $accoun_id,
            'description' => '',
            'email' => $user->email,
            'amount' => $price,
            'currency' => 'RUB',
            'requireConfirmation' => false,
            'startDate' => Carbon::now()->toISOString(),
            'interval' => 'Month',
            'period' => $period,
        ];
        return $this->send(self::SUBSCRIBE_URL, $params);
        // $_params = [
        //     'Amount' => $price,
        //     'AccountId' => $accoun_id,
        //     'Token' => $pay_token,
        //     'InvoiceId'=>$invoiceId,
        //     'JsonData'=> json_encode($reciept)
        // ];

        // return $this->send(self::CHARGE_URL, $params);
    }
    
    public function Cancel($paymentId, $amount = 0)
    {
        $this->ClearOrder();
        if( ! $paymentId ) {
            //$this->show_error("Не введен ID платежа", 500);
            return false;
        }
        $params = [
            'Amount' => $amount,
            'TransactionId' => $paymentId,
        ];
        //$url = 'https://api.cloudpayments.ru/payments/refund';

        return $this->send(self::CANCEL_URL, $params);
    }

    /**
     * @param array $params
     */
    public function AddMainInfo($params = array())
    {
        $this->order = $params;
    }

    public function AddMainInfoParams($params = array())
    {
        foreach ($params as $key => $value) {
            $this->order[$key] = $value;
        }
    }

    /**
     * @param array $params
     */
    public function AddDATA($params = array())
    {
        $this->order['DATA'] = $params;
    }

    public function ClearOrder()
    {
        $this->order = array();
    }

    /**
     * @param $text
     * @param $code
     */
    public function show_error($text = '', $code = 404)
    {
        $this->errors[] = [
            "code"  => $code,
            "text"  => $text
        ];
    }

}
