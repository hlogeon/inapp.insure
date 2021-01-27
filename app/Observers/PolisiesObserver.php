<?php

namespace App\Observers;

use App\Models\Polisies;
use App\Services\Internal\EventService;

class PolisiesObserver
{
    /**
     * Handle the police "created" event.
     *
     * @param  \App\Models\Polisies  $Polisies
     * @return void
     */
    public function created(Polisies $Polisies)
    {
        EventService::onSave($Polisies);
    }
}
