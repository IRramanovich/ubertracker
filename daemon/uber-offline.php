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
$loop->addPeriodicTimer(240, function(React\EventLoop\Timer\Timer $timer) use (&$i, $loop) {
    $lastStatuses = DriversLastStatus::with('driver')->get();

    foreach ($lastStatuses as $one) {
        $driver = Drivers::firstOrNew([
            'id' => $one->driver_id,
        ]);

        $shift = Shifts::where('driver_id', $one->driver_id)
            ->whereNull('end')
            ->with('lastChangeStatus')
            ->orderBy('start', 'desc')
            ->first();

        if($shift) {
            switch ($shift->lastChangeStatus->status) {
                case 'Offline':
                    if ($one->date->diffInHours(Carbon::now()) > 3) {
                        $shift->lastChangeStatus->status = 'shiftEnd';
                        $shift->lastChangeStatus->timestamps = false;
                        $shift->lastChangeStatus->save();
                        $shift->closeShift($shift);
                    }

                    if ($shift->lastChangeStatus->date->diffInMinutes(Carbon::now()) > 59 &&
                        !$shift->lastChangeStatus->offline_60_send &&
                        !Surge::isSurgeTime($shift->lastChangeStatus->date)) {

                        UberNotification::offline60($one->driver->getName(), $shift->lastChangeStatus->date);
                        $shift->lastChangeStatus->offline_60_send = 1;
                        $shift->lastChangeStatus->timestamps = false;
                        $shift->lastChangeStatus->save();
                    }
                    break;
                case 'shiftEnd':
                    continue;
                    break;
                default:
                    if ($one->date->diffInMinutes(Carbon::now()) > 3) {

                        $offline = new DriversStatus();
                        $offline->driver_id = $one->driver_id;
                        $offline->status = 'Offline';
                        $offline->latitude = $one->latitude;
                        $offline->longitude = $one->longitude;
                        $offline->date = $one->date;
                        $offline->save();

                        if (Surge::isSurgeTime($one->date)) {
                            if ($one->driver) {
                                UberNotification::offlineSurge($one->driver->getName(), $one->date);
                            } else {
                                UberNotification::offlineSurge($one->driver_id, $one->date);
                            }
                        } else {
                            if ($one->driver) {
                                UberNotification::offline($one->driver->getName(), $one->date);
                            } else {
                                UberNotification::offline($one->driver_id, $one->date);
                            }
                        }
                    }
                    break;
            }
        }
    }// end main foreach
});

$loop->run();
