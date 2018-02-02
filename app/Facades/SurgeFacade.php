<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class SurgeFacade extends Facade{
    protected static function getFacadeAccessor() { return 'Surge'; }
}