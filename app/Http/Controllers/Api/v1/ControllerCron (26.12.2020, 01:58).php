<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\ControllerPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\System;
use App\Models\User;
use App\Models\Polisies;
use App\Models\Tarrifs;

use App\Myclasses\CloudPayment;
use Carbon\Carbon;

/*
	Системные статусы
	1 - добавление кешбека
	2 - деактивация полиса в случае отмены подписки полиса после 14 дней с момента активации
	3 - рекуррентный платеж - успешный
	4 - рекуррентный платеж - провальный по вине банка
	5 - рекуррентный платеж - провальный по вине системы
	6 - изменение статуса на статус "вступил в силу"
	7 - изменение тарифа
	8 - деактивация полиса в случае прекращения платы после 14 дней
*/

class ControllerCron extends ControllerPayment
{
    public function cronInit()
    {
        $pay = new CloudPayment;
        //$charge = $pay->charge(100, "user243@client.com", "477BBA133C182267FE5F086924ABDC5DB71F77BFC27F01F2843F2CDC69D89F05");
        $charge = $pay->Cancel("587745810", 100);
        dd($charge);
        //
        exit;
    	$today = Carbon::today();

    	$policies = DB::table('polisies')
    		->select("polisies.*", "users.cashback", "users.RebillId")
    		->join("users", "polisies.user_id", "users.id")
    		->where("polisies.active", 1)
    		->groupBy("polisies.id")
    		->get();

    	//echo "<pre>";
    	//print_r($policies);
    	//echo "</pre>";

    	foreach ($policies as $key => $police) {
    		//Проверка на статус "Оплачен" или "Вступил в силу"
    		$status = $this->isConfirmed($police);

    		//Дата начала подписки
    		$start = Carbon::createFromTimestamp(strtotime($police->start));
    		//Дата окончания подписки
    		$finish = Carbon::createFromTimestamp(strtotime($police->finish));

    		if($status) {
    			//Начисление кешбека
    			$cashsystem = System::where([
    				["polic_id", $police->id],
    				["user_id", $police->user_id],
    				["status_id", 1]
    			])->orderBy('created_at', 'desc')->first();

    			if( ! $cashsystem ) {
    				$cashStart = Carbon::createFromTimestamp(strtotime($police->start))->addMonths(1);
    				if($cashStart->day == $today->day) {
    					$this->cashBackUpdate($police);
    				}
    			} else {
    				$cashStart = Carbon::createFromTimestamp(strtotime($cashsystem->created_at))->addMonths(1);
    				if($cashStart->day == $today->day) {
    					$this->cashBackUpdate($police);
    				}
    			}
    		}

    		//Деактивация полиса в случае отмены подписки после 14 дней с момента активации
    		if($police->subscribed == 0) {
    			if($today->format('d.m.Y') == $finish->format('d.m.Y')) {
    				$this->deactivatePolice($police);
    				$this->updateSystemInfo($police, 2, "Деактивирован по причине отказа от подписки");
    			}
    		}
    		else {
    			//Изменение тарифа
    			if($status) {
    				if($today->format('d.m.Y') == $finish->format('d.m.Y') && $police->changed_tarrif != 0) {
    					$tarrif = Tarrifs::find($police->changed_tarrif);
    					$policeIs = Polisies::find($police->id);
    					if($tarrif && $policeIs) {
    						$policeIs->update([
    							"tarrif_id" => $tarrif->id
    						]);
    					}

    					if($policeIs) {
    						$policeIs->update([
    							'changed_tarrif' => 0
    						]);

    						$this->updateSystemInfo($police, 7, "Тариф пользователя был успешно изменён");
    					}
    				}
    			}


    			//Рекуррентный платеж
    			//Поиск по дате окончания подписки 
    			//Если сегодня дата совпадает с датой окончания подписки и есть id от банка для совершения рекурретного платежа и id тарифа
    			if($today->format('d.m.Y') == $finish->format('d.m.Y') && $police->RebillId > 0 && $police->tarrif_id > 0) {
    				//Поиск нужных данных для совершения реккурентного платежа
    				$policeIs = Polisies::find($police->id);
    				$tarrif = Tarrifs::find($police->tarrif_id);
    				$user = User::find($police->user_id);
    				if($policeIs && $user && $tarrif) {
    					
    					//Очистка сессии, которая заполняется при совершении платы для каждого платежа в цикле
    					request()->session()->put('order_id', '');
    					//Создания нового платежа в банке
    					$tinkoff = $this->configurateData($user, $tarrif, false, false);
    					//Проверка платежа на корректность
                 		if($tinkoff && $tinkoff->Success) {
                 			//Совершение рекурретного платежа
                 		    $response = $this->makeRecurent($tinkoff, $user);
                 		    if( isset( $response->Success ) && $response->Success ) {
                 		    	$finishTarrif = $finish;
                				$finishTarrif = $finishTarrif->addMonth($tarrif->per_month);
                				//Если платеж прошел успешно, то обновляю данные окончания подписки
                				$policeIs->update([
                					'finish' => $finishTarrif
                				]);
                 		        $this->updateSystemInfo($police, 3, "Оплата прошла успешно");
                 		    }

                 		    elseif(isset($tinkoff->Message)) {
                 		    	$this->updateSystemInfo($police, 4, $tinkoff->Message);
                 		    	$this->dipaidPolice($police);
                 		    }

                 		} elseif(isset($tinkoff->Message)) {
                 			$this->updateSystemInfo($police, 4, $tinkoff->Message);
                 			$this->dipaidPolice($police);
                 		}
    				} else {
    					//Запись в лог, если данные некорректные
    					$this->updateSystemInfo($police, 5, serialize([
    						"police_data" => $police,
    						"tarrif_data" => $tarrif,
    						"user_data"	  => $user
    					]));
    					$this->dipaidPolice($police);
    				}
    			} elseif($today->format('d.m.Y') == $finish->format('d.m.Y')) {
    				//Запись в лог, если массив police не соответсвтует требованию для совершения рекурретного платежа
    				$this->updateSystemInfo($police, 5, serialize([
    					"police_data" => $police
    				]));
    				$this->dipaidPolice($police);
    			}

    			$nullable = $finish;
    			$nullable = $nullable->addDays(14);

    			if($today->timestamp >= $nullable->timestamp && $police->status_id == 4) {
    				$this->deactivatePolice($police);
    				$this->updateSystemInfo($police, 8, "Полис был деактивирован и переведен в статус АННУЛИРОВАН");
    			}

    			//Обновление статсу на статус "вступил в силу"
    			
    			if($today->format('d.m.Y') == $start->format('d.m.Y') && $police->status_id == 1) {
    				$policeIs = Polisies::find($police->id);
    				if($policeIs) {
    					$policeIs->update([
    						"status_id" => 2
    					]);
    					$this->updateSystemInfo($police, 6, "Статус полиса был успешно изменен на ВСТУПИЛ В СИЛУ");
    				}
    			}
    		}
    		unset($start, $finish, $status);
    	}
    }

    /**
    * Обновление кешбека
	*	@param object Policies:class
    **/
    private function cashBackUpdate($police)
    {
    	if( ! $police && ! isset($police->cashback) ) return;

    	$cashbackUserUpdate = User::find($police->user_id);
    	if($cashbackUserUpdate) {
    		$cashbackUserUpdate->update([
    			"cashback" => $police->cashback += 399
    		]);
    		$this->updateSystemInfo($police, 1, "Начисление кешбека 399 балов");
    	}
    }

    /**
    *  Создание нового системного лога
	*	@param object Policies:class
	*	@param integer $status_id
	*	@param string $message
    **/
    private function updateSystemInfo($police, $status_id, $message)
    {
    	if( ! $police && ! $status_id ) return;

    	return System::create([
    		"status_id" => $status_id,
    		"user_id"	=> isset($police->user_id) ? $police->user_id : null,
    		"polic_id"	=> $police->id,
    		"name"		=> "CRON",
    		"value"		=> ($message) ? $message : ""
    	]);
    }

    /**
    * Первод полиса в статус неоплачен
	*	@param object Policies:class
    **/
    private function dipaidPolice($police)
    {
    	$policeIs = Policies::find($police->id);
    	if($policeIs) {
    		$police->update([
        		'status_id' => 4
        	]);
    	}
    }

    /**
    * Деактивация полиса
	*	@param object Policies:class
    **/
    private function deactivatePolice($police)
    {
    	$policeIs = Policies::find($police->id);
    	if($policeIs) {
    		$police->update([
        		'active' => 0,
        		'status_id' => 3
        	]);
    	}
    }


    /**
    * Проверка полиса на статус оплаты
	*	@param object Policies:class
    **/
    private function isConfirmed($police)
    {
    	if($police->status_id == 1 || $police->status_id == 2) return true;

    	return false;
    }
}
