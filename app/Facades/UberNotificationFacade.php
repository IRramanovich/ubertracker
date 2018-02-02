<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UberNotificationFacade extends Facade{
    protected static function getFacadeAccessor() { return 'UberNotification'; }
}