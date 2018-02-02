<?php
require __DIR__.'/../bootstrap/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
use App\DriversStatus;
use App\Drivers;
use App\Shifts;
use App\DriversLastStatus;
use App\DriversAllStatuses;
use Carbon\Carbon;
use App\Events\DriversStatusChange;
use App\Lib\ShifControllLibrary;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use App\Lib\UberRequest;
use App\Cars;
use App\Service\UberDaemonOffline;

$loop = React\EventLoop\Factory::create();

$i = 0;
$loop->addPeriodicTimer(400, function(React\EventLoop\Timer\Timer $timer) use (&$i, $loop) {
    //get information about driver and add information adout car to shift
    $shiftsOnline = Shifts::OnlineDrivers()
        ->where('car_id', 0)
        ->with('lastShiftStatus')
        ->get();
    foreach($shiftsOnline as $one){
        if ($one->getCarId() == 0) {
            $uberDriver = \UberRequest::getDriver($one->driver_id);
            if($uberDriver){
                $car = Cars::getCarByNumber($uberDriver->licensePlate);
                if(!is_null($car)){
                    $one->setCarId($car->id);
                }
            }
        }

    }

});

$loop->run();





