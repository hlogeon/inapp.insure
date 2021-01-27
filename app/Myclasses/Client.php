<?php

namespace App\Myclasses;

use Ufee\Amo\Amoapi;
use Ufee\Amo\Oauthapi;

class Client
{
//    use \Core\Traits\Singleton;
    private static $_crm;

    /**
     * Get amoCRM API client
     * @return CRM
     */
    public function crm()
    {
        if (is_null(static::$_crm)) {
            static::$_crm = Oauthapi::setInstance(\Config::get('crm'));
//            static::$_crm->queries->cachePath(storage_path('app/cache'));
            static::$_crm->setOauthPath(storage_path('app/cache'));
            static::$_crm->queries->logs(storage_path('logs'));
        }
        return static::$_crm;
    }
}