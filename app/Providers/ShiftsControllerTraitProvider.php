<?php
/**
 * Created by PhpStorm.
 * User: a123
 * Date: 1/15/18
 * Time: 17:54
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use App\Lib\ShiftsControllerTrait;

class ShiftsControllerTraitProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('ShiftControllerTrait', function()
        {
            return new ShiftsControllerTrait;
        });
    }
}
