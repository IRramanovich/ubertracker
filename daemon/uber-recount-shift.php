<?php
require __DIR__.'/../bootstrap/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);


use App\Shifts;
use Carbon\Carbon;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use App\Service\UberDaemonOffline;

$loop = React\EventLoop\Factory::create();

$i = 0;
$loop->addPeriodicTimer(400, function(React\EventLoop\Timer\Timer $timer) use (&$i, $loop) {
    //recount shift if data from uber do not get
    $shiftsWithoutInfo = Shifts::whereNull('info')
        ->whereNull('end')
        ->with('lastShiftStatus')
        ->get();
    if($shiftsWithoutInfo){
        foreach($shiftsWithoutInfo as $one){
            $result = $one->preliminarilyCountShift();
            UberDaemonOffline::ensure($result, UberDaemonOffline::CODE_SHIFT_NOT_RECOUNT, $one);
        }
    }
});

$loop->run();

