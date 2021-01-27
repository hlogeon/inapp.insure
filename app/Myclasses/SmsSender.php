<?php

namespace App\Myclasses;
use Illuminate\Http\Request;

class SmsSender
{
	private $api_key = "4CBE42D3-439F-D62D-6613-92C51B1CCE19";

	public function __construct($phone, $text)
	{
		$this->phone = $phone;
		$this->text = $text;
	}

    public function getResponse ()
    {
    	include_once $_SERVER["DOCUMENT_ROOT"]."/includes/sms.ru.php";
        $smsru = new \SMSRU($this->api_key); // Ваш уникальный программный ключ, который можно получить на главной странице

        $data = new \stdClass();
        $data->to = $this->phone;
        $data->text = $this->text; // Текст сообщения
        // $data->from = ''; // Если у вас уже одобрен буквенный отправитель, его можно указать здесь, в противном случае будет использоваться ваш отправитель по умолчанию
        // $data->time = time() + 7*60*60; // Отложить отправку на 7 часов
        //$data->translit = 1; // Перевести все русские символы в латиницу (позволяет сэкономить на длине СМС)
        //$data->test = 1; // Позволяет выполнить запрос в тестовом режиме без реальной отправки сообщения
        // $data->partner_id = '1'; // Можно указать ваш ID партнера, если вы интегрируете код в чужую систему
        $sms = $smsru->send_one($data); // Отправка сообщения и возврат данных в переменную
        
        return $sms;      
    }

    private function generateUrl()
    {
    	return $this->url . "?" . http_build_query([
    		"login" 	=> $this->api_login,
    		"password" 	=> $this->api_password,
    		"phone"		=> $this->phone,
    		"text"		=> $this->text
    	]);
    }
}
