<?php

namespace App\Services\Internal;

use App\Services\Service;
use Illuminate\Database\Eloquent\Model;
use App\Myclasses\Client;
use App\Jobs\PolisiesCreateJob;

/**
 * Работа с уведомлениями в системе
 */
class EventService extends Service {

    /**
     * Создать уведомление при сохранении
     * @return bool Была ли создана запись в БД
     */
    public static function onSave(Model $model) : bool {
        if (!$model->id) {
            return false;
        }
        // PolisiesCreateJob::dispatch($model);
        return true;
    }
}