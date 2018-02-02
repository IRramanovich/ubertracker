<?php
/**
 * Created by PhpStorm.
 * User: a123
 * Date: 1/16/18
 * Time: 14:26
 */
require __DIR__.'/../bootstrap/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\DriversStatus;
use App\Shifts;
use App\DriversLastStatus;
use App\DriversAllStatuses;
use App\Drivers;
use App\Offline;
use Carbon\Carbon;
use App\Events\DriversStatusChange;
use App\Lib\ShifControllLibrary;
use App\Service\UberDaemonLog;
use App\Lib\WialonReport;
use App\Cars;

$loop = React\EventLoop\Factory::create();

$report = new WialonReport();

$i = 0;
$loop->addPeriodicTimer(1, function(React\EventLoop\Timer\Timer $timer) use (&$i, $loop, $report) {
    $messages = $report->getLastMessage();
    foreach ($messages['items'] as $item){
        $car = Cars::getCarByNumber($item['nm']);
        if(!is_null($car)){

            $shifts = Shifts::whereNull('info')
                ->whereNull('end')
                ->where('car_id', $car->id)
                ->with('lastChangeStatus')
                ->with('lastStatus')
                ->get();

            if (count($shifts) > 0) {
                $lastAllStatus = DriversAllStatuses::where(['driver_id' => $shifts[count($shifts)-1]['driver_id']])->first();
                if (!is_null($lastAllStatus)) {
                    if (($lastAllStatus->latitude != $item['lmsg']['pos']['y']) && ($lastAllStatus->longitude != $item['lmsg']['pos']['x'])) {
                        $allStatuses = new DriversAllStatuses();
                        $allStatuses->driver_id = $shifts[count($shifts)-1]['driver_id'];
                        $allStatuses->latitude = $item['lmsg']['pos']['y'];
                        $allStatuses->longitude = $item['lmsg']['pos']['x'];
                        $allStatuses->course = $item['lmsg']['pos']['c'];
                        $allStatuses->date = date('Y-m-d H:i:s', $item['pos']['t']);
                        $allStatuses->status = $shifts[count($shifts)-1]->lastChangeStatus->status;
                        $allStatuses->shift_id = $shifts[count($shifts)-1]->id;
                        $result = $allStatuses->save();//log
                        $allStatuses->load('driver');

                        $data = [
                            'driver_id' => $shifts[count($shifts)-1]['driver_id'],
                            'latitude' => $item['lmsg']['pos']['y'],
                            'longitude' => $item['lmsg']['pos']['x'],
                            'course' => $item['lmsg']['pos']['c'],
                            'date' => date('Y-m-d H:i:s', $item['pos']['t']),
                            'speed' => $item['pos']['s'],
                            'status' => $shifts[count($shifts)-1]->lastChangeStatus->status,
                            'shift_id' => $shifts[count($shifts)-1]->id,
                            'driver' => $allStatuses['driver']
                        ];

                        if (($shifts[count($shifts)-1]->lastStatus->date->diffInMinutes(Carbon::now()) > 0) && ($shifts[count($shifts)-1]->lastChangeStatus->status != 'Offline')) {
                            $data['status'] = 'Shadow';
                        }

                        $result_data = [
                            'topic_id'  => 'onNewData',
                            'data'      => $data
                        ];
                        \App\Classes\Socket\Pusher::sentDataToServer($result_data);
                    }
                }
            }
        }
    }
});

$loop->run();