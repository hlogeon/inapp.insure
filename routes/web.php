<?php

//if($_SERVER['REQUEST_METHOD']=='POST')
//    file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/storage/logs/payment_rec_data.log', date('[H:i:s] ') . "\r\nPOST:::\r\n" . print_r($_POST, true) . "\r\n=====\r\n", FILE_APPEND);

//function pre($arr){echo'<pre>';print_r($arr);echo'</pre>';}

use App\Models\Polisies;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\MenuTop;
use App\Http\Controllers\Api\v1\ControllerMenuBottom;
use App\Http\Controllers\Api\v1\ControllerPersonal;
use App\Http\Controllers\Api\v1\AuthValidation;
use App\Http\Controllers\Api\v1\ControllerTariffs;
use App\Http\Controllers\ControllerTesting;
use App\Http\Controllers\Api\v1\ControllerCashback;
use App\Http\Controllers\Api\v1\ControllerInsurances;
use App\Http\Controllers\Api\v1\ControllerCron;
use App\Myclasses\Authorization;
use App\Http\Controllers\Api\v1\ControllerPayment;
use App\Http\Controllers\ControllerPolisies;
use App\Http\Controllers\Crm\ControllerCrm;

//Route::get('pedit',[ControllerPolisies::class, 'index']);

Route::get('/{any}', function(){
	return view('index');
})->where('any', '(?!admin|api|payment|paymentcheck|cron|crm|get_pdf|certgen|test)+.*');

Route::any('/paymentcheck', [ControllerPayment::class, 'checking']);
Route::get('/test', [ControllerTesting::class, 'index']);

Route::get('/get_pdf/{id}', [ControllerPersonal::class, 'getPdf'])->where('id', ".*");
Route::get('/certgen', function() {
    $request = request()->all();
    return view('certgen.pdf', $request);
});

Route::group(['prefix' => '/cron/'], function () {
    Route::get('init', [ControllerCron::class, 'cronInit']);
});


Route::group(['prefix' => '/api/v1/'], function () {
    //Проверка на авторизацию
    Route::get('get_user', [Authorization::class, 'current']);

    //Проверка на авторизацию
    Route::get('get_user_data', [Authorization::class, 'currentUser']);

    //Заверешение сессии авторизации
    Route::get('logout', [Authorization::class, 'logout']);

	//Получение пунктов верхнего меню
    Route::get('top_menu', [MenuTop::class, 'index']);

    //Получение пунктов нижнего меню
    Route::get('bottom_menu', [ControllerMenuBottom::class, 'index']);

    //Авторизация
    Route::get('send_phone', [AuthValidation::class, 'index']);

    //Тарифы для страицы /authpayment
    Route::get('tarrifs', [ControllerTariffs::class, 'index']);

    //Страница аккаунта получение данных о пользователе
    Route::get('get_personal', [ControllerPersonal::class, 'personalInfo']);

    //Получение всех рисков в лк
    Route::get('get_risks', [ControllerPersonal::class, 'getRisks']);

    //Получение всех страховок в лк
    Route::get('get_insurances', [ControllerPersonal::class, 'getIinsurances']);

    Route::get('bonus/landing', [ControllerPersonal::class, 'landingBonuses']);

    Route::get('bonus/landing/all', [ControllerPersonal::class, 'landingAllBonuses']);

    Route::get('bonus/offers', [ControllerPersonal::class, 'getBonusList']);

    Route::get('bonus/accept', [ControllerPersonal::class, 'activateBonus']);

    Route::post('bonus/favorite', [ControllerPersonal::class, 'favoriteBonus']);

    Route::get('plans', [ControllerPersonal::class, 'getPlans']);

    //Получение всех страховок
    Route::get('get_insurances_public', [ControllerInsurances::class, 'index']);

    //Получение всех страховок
    Route::get('add_aPolic', [ControllerPersonal::class, 'addAPolic']);

    //Получение всех полисов
    Route::get('get_polices', [ControllerPersonal::class, 'getPolices']);

    //Установить тариф на странице информации о полисе в личном кабинете
    Route::get('set_tarrifs', [ControllerPersonal::class, 'setTarrifs']);

    //Получение тарифов на странице информации о полисе в личном кабинете
    Route::get('get_tarrifs', [ControllerPersonal::class, 'getTarrifs']);

    //Отмена подписки в личном кабинете
    Route::get('cansel_subscribe', [ControllerPersonal::class, 'canselSubscribe']);

    //Отзыв после отмены подписки
    Route::get('cansel_vote', [ControllerPersonal::class, 'canselVote']);
    
    //Изменить телефон в личном кабинете
    Route::get('change_phone', [ControllerPersonal::class, 'changePhone']);

    //Подтверждение смс в личном кабинете
    Route::get('change_phonesms', [ControllerPersonal::class, 'changePhoneSms']);

    //Получение телефона из сессии при изменение телефона
    Route::get('checking_phone', [ControllerPersonal::class, 'checkingPhoneAfterSentSms']);
    
    //Включить автообновление
    Route::get('enable_subscribe', [ControllerPersonal::class, 'enableSubscribe']);

    //Проверка сообщений в личном кабинете
    Route::get('check_message', [ControllerPersonal::class, 'checkMessage']);

    //Получение всех кешбеков
    Route::get('get_cashback', [ControllerCashback::class, 'index']);

    //Получение всех кешбеков
    Route::get('get_cashback_public', [ControllerCashback::class, 'publicable']);

    //Оплата на сайте при оформлении полиса
    Route::get('pay_for_police', [ControllerPayment::class, 'index']);

    //Оплата на сайте при смене карты
    Route::get('pay_for_changing', [ControllerPayment::class, 'changingCard']);

    //Проверка оплаты через заданный интервал времени
    Route::get('what_is_going_on', [ControllerPayment::class, 'checkingInterval']);

    //Удаление карты из банка
    Route::get('change_card', [ControllerPayment::class, 'changeCard']);
});


//Auth::routes();

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});


// AMOCRM Integration
Route::group(['prefix' => 'crm'], function() {
    Route::get('integration', [ControllerCrm::class, 'index']);
    Route::post('hook', [ControllerCrm::class, 'hook']);
    Route::get('auth', [ControllerCrm::class, 'auth']);
    Route::get('redirect', [ControllerCrm::class, 'redirect']);
});
