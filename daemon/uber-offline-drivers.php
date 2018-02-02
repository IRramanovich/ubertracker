<?php
require __DIR__.'/../bootstrap/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Shifts;
use Carbon\Carbon;
use App\Lib\ShifControllLibrary;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

$loop = React\EventLoop\Factory::create();

$i = 0;
$loop->addPeriodicTimer(300, function(React\EventLoop\Timer\Timer $timer) use (&$i, $loop) {
    // check offline drivers and send them notification
    $shiftsOffline = Shifts::OfflineDrivers()->with('driver')->get();
    foreach($shiftsOffline as $one){
        $resp = ShifControllLibrary::checkOfflineDriver($one->driver);
    }
});

$loop->run();
