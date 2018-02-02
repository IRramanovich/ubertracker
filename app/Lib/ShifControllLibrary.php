<?php
/**
 * Created by PhpStorm.
 * User: iramanovich
 * Date: 24.2.17
 * Time: 17.28
 */

namespace App\Lib;

use Carbon\Carbon;
use App\Offline;
use App\DriversAllStatuses;
use App\DriversLastStatus;
use App\DriversStatus;
use App\Shifts;
use App\Cars;
use League\Flysystem\Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class ShifControllLibrary
{
    
    public static function logLateStartShift($start_date, $newStatus, $shift){
        $status = DriversStatus::createStatusForLateStartOrCloseShift($newStatus->driver_id, $start_date);

        DriversAllStatuses::createOfflinePoint($newStatus->driver_id, $shift->id);
        
        if($newStatus->date->diffInSeconds($status->date) > 0)
            Offline::createOffline($shift, $status, $newStatus);

    }

    public static function logEarlyEndShift($end_date, $shift){
        $status = DriversStatus::createStatusForLateStartOrCloseShift($shift->driver_id, $end_date);
        
        DriversAllStatuses::createOfflinePoint($shift->driver_id, $shift->id);
        
        $newStatus = DriversStatus::where('driver_id', $shift->driver_id)
            ->orderBy('date', 'desc')
            ->firstOrFail();
        
        if($newStatus){
            if($newStatus->date->diffInSeconds($status->date) > 0)
                Offline::createOffline($shift, $newStatus, $status);
        }

        return true;
    }
    
    public static function checkDropOrder($driver, $statusName){
        $logger = new Logger('LoggerException');
        // Now add some handlers
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../storage/logs/control-libraru.log', Logger::DEBUG));
        $logger->pushHandler(new FirePHPHandler());

        $lastStatus     = DriversStatus::where('driver_id', $driver->id)->orderBy('date', 'DESC')->first();
        $currentShift   = Shifts::where('driver_id', $driver->id)->whereNull('end')->first();
        if($currentShift){
            $car = Cars::where('id', $currentShift->car_id)->first();
            if($car){
//                $logger->addInfo('$statusName:' . $statusName);
//                $logger->addInfo('$lastStatus->status:' . $lastStatus->status);
//                $logger->addInfo('==========================================');
                if($statusName == 'Open' && $lastStatus->status == 'Dispatched'){
                    $logger->addInfo('extention complite');
                    $currentShift->incrementDropOrder();
                    $mulct = 0;
                    if ($currentShift->drop_order >= 5){
                        $mulct = $currentShift->drop_order-5;
                    }

                    $message = "Пропущен заказ ($driver->lastname ".substr($driver->firstname, 0, 1)."  )!!!\n"
                        . "За смену пропущено заказов: $currentShift->drop_order \n";

//                UberNotification::sendSlackMessageToDriver('administrator',$message);
                    try{
                        UberNotification::sendSlackMessageToDriver('car'.$car->car_code, $message);
                    } catch(\Exception $e){
                        $logger->addInfo('false');
                        return false;
                    }

                    $logger->addInfo('end');
                    return true;
                }
            }
        }

        return false;
    }


    public static function checkOfflineDriver($driver){
        $response = NULL;
        $message = "Вы офлайн!!! (+$driver->phone, $driver->firstname $driver->lastname)";
        $currentShift   = Shifts::where('driver_id', $driver->id)->whereNull('end')->first();
        if($currentShift){
            $car = Cars::where('id', $currentShift->car_id)->first();
            if($car){
//                UberNotification::sendSlackMessageToDriver('administrator',$message);
                UberNotification::sendSlackMessageToDriver('car'.$car->car_code,$message);
                $response = $car->car_code;
                
                return $response;
            }
        }
        return false;
    }
}