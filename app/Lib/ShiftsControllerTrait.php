<?php
/**
 * Created by PhpStorm.
 * User: a123
 * Date: 1/15/18
 * Time: 17:22
 */

namespace App\Lib;

use App\Shifts;
use App\Cars;
use App\DriversStatus;
use App\Offline;
use Debugbar;

class ShiftsControllerTrait
{
    public static function getData(Shifts $shift)
    {
        $shift->load('lastChangeStatus');

        if($shift->end && $shift->info){
            $shiftData = unserialize($shift->info);
        }else{
            $shiftData = $shift->getAndComposeShiftData();
        }

        $cars = Cars::getIdAndNumber();
        $dropOrders = DriversStatus::getAllDropOrder($shift->driver_id, $shift->start, $shift->end);
        $statusDistance = DriversStatus::getAllStatusDistance($shift->driver_id, $shift->start, $shift->end);

        $report = new WialonReport();
//        $statusDistance['offline'] = $report->getOfflineDistance($shift, $cars);

        $data = [
            'shift'             => $shift,
            'cars'              => $cars,
            'num'               => 1,
            'statusDistance'    => $statusDistance,
            'dropOrders'        => $dropOrders
        ];

        return array_merge($data, $shiftData);
    }
}