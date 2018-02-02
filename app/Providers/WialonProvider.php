<?php
/**
 * Created by PhpStorm.
 * User: kormilo
 * Date: 26.10.17
 * Time: 6:11
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use App\Lib\Wialon;

class WialonProvider extends ServiceProvider
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
        App::bind('Wialon', function()
        {
            return new Wialon();
        });
    }
}