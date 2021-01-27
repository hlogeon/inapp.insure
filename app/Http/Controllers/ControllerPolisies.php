<?php

namespace App\Http\Controllers;

use App\Models\Bso;
use App\Models\BsoIndexes;
use App\Models\Payments;
use App\Models\Polisies;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ControllerPolisies extends Controller
{
    private static $LOGIN_1C = "sc1105838";
    private static $PASSWORD_1C = "Vagan2020";

    public function index(){
        
        $arPolisies = Polisies::where('send_1C', 0)->get();
        if($arPolisies->count()){

            $arPolisies = $arPolisies->toArray();

            $client = new Client(['auth' => [
                self::$LOGIN_1C,
                self::$PASSWORD_1C
            ]]);
            $arDataToSend = [];
            foreach ($arPolisies as $arPolisy) {
                if($arPolisy['status_id'] === 1 &&
                    $arPolisy["user_id"] > 0) { // Статус оплачен и есть пользователь
                        $arUser = \App\Models\User::where("id", $arPolisy["user_id"])->first();
                        if (is_object($arUser))
                            $arUser = $arUser->toArray();

                        if ($arUser &&
                            $arPolisy["address"] &&
                            $arPolisy["start"] &&
                            $arPolisy["finish"] &&
                            $arPolisy["bso"]
                        ) {
                            $arData = [
                                "id" => $arPolisy["id"],
                                "company" => $arPolisy["company"],
                                "insurant" => [
                                    "id" => $arUser["id"],
                                    "name" => $arUser["user_name"],
                                    "surname" => $arUser["user_surname"],
                                    "birth" => $arUser["user_birsday"],
                                    "phone" => $arUser["phone"],
                                    "email" => $arUser["user_email"]
                                ],
                                "address" => $arPolisy["address"], //фактический адрес
                                "created" => date('Y-m-d', strtotime($arPolisy["created_at"])), //дата создания документа
                                "begin" => $arPolisy["start"], //дата начала действия (активации)
                                "end" => $arPolisy["finish"], //дата окончания действия
                                "bso" => $arPolisy["bso"], //номер полиса, 16 цифр
                                "payment" => [
                                    "date" => "2021-11-01", //дата документа оплаты
                                    "number" => "123", //номер документа оплаты (чека)
                                    "link" => "https://taxcom.ru/123/123" //ссылка на сайт офд
                                ],

                            ];
                            $arDataToSend[] = $arData;
                        }
                    }
            }

            $res = $client->request('POST', 'https://terminal.scloud.ru/05/sc1105838_base001/hs/bitrix', [
                'body' => json_encode($arDataToSend)
            ]);
            $body = $res->getBody();
            $arResults = json_decode($body, true);

            foreach ($arResults as $police_id => $arResult) {
                $police_id = preg_replace("/\D/","",$police_id);
                if ($arResult["result"] == "success") {
                    Polisies::where('id', $police_id)->update(['send_1C' => 1]);
                    echo "Полис с ID:" . $police_id . " успешно отправлен и обработан в 1С<br>";
                } elseif ($arResult["result"] == "error") {
                    Polisies::where('id', $police_id)->update(['error_mess_1C' => $arResult["errortext"]]);
                    echo "Полис с ID:" . $police_id . " не удалось  обработать в 1С<br>";
                    echo "Причина " . $arResult["errortext"]."<br>";
                }
            }
        }
        else{
            echo "Не найдено ни одного полиса для отправки";
        }
    }
}
