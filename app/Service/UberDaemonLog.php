<?php
/**
 * Created by PhpStorm.
 * User: iramanovich
 * Date: 30.3.17
 * Time: 14.29
 */

namespace App\Service;

use App\Drivers;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class UberDaemonLog
{
    const CODE_FAIL                             = 0;
    const CODE_NEW_DRIVER_NOT_SAVE_OR_CREATE    = 1;
    const CODE_NEW_SHIFT_NO_CREATE              = 2;
    const CODE_NEW_SHIFT_NO_CREATE_IN_RESERVE   = 3;
    const CODE_LAST_STATUS_NOT_SAVE             = 4;
    const CODE_ALL_STATUS_NOT_SAVE              = 5;
    const CODE_GET_DRIVER_FIRST_CASE            = 6;
    const CODE_GET_DRIVER_SECOND_CASE           = 7;
    const CODE_GET_DATA                         = 8;
    const CODE_SHIFT_WAS_CLOSE                  = 9;
    const CODE_NEW_SHIFT_CREATE                 = 10;
    const CODE_NEW_SHIFT_CREATE_IN_RESERVE      = 11;
    const CODE_CHECK_DROP_ORDER                 = 12;


    public static function getUberMessage($code)
    {
        $msgArr = [
            0  => 'Fail : Required parameter are missing',
            1  => 'Не была создана или не обновилась запись для бодителя',
            2  => 'Смена не была создана в первом случае',
            3  => 'Смена не была создана в резервном случае',
            4  => 'Запись в таблицу LastStatus не сохранена',
            5  => 'Запись в таблицу AllStatus не сохранена',
            6  => 'Запрос данных водителя, первый вариант',
            7  => 'Запрос данных водителя, второй вариант',
            8  => 'Get data',
            9  => 'Закрытие существующей смены перед открытием новой',
            10 => 'Смена создана в первом случае',
            11 => 'Смена создана в резервном случае',
            12 => 'Водитель отклонил заказ'
        ];

        if (isset($msgArr[$code])) {
            return $msgArr[$code];
        } else {
            return "No error message is set yet";
        }
    }

    public static function getData($data, $driverStatus)
    {
        if($data != Null){
            if(is_object($data)){
                $data_class = get_class($data);
                switch ($data_class){
                    case 'Shifts':
                        $driver = Drivers::where('id', $data->driver_id)->first();
                        if(is_null($driverStatus)){
                            $result = json_encode([
                                'Shift_ID'  => $data->id,
                                'Driver'    => $driver->lastname,
                                'Start'     => $data->start,
                                'End'       => $data->end,
                            ]);
                        }else{
                            $result = json_encode([
                                'Shift_ID'          => $data->id,
                                'Driver'            => $driver->lastname,
                                'Start'             => $data->start,
                                'DriverStatus'      => $driverStatus->status,
                                'DriverStatusDate'  => $driverStatus->date,
                                'End'               => $data->end,
                            ]);
                        }
                        return $result;
                    case 'DriversStatus':
                        $driver = Drivers::where('id', $data->driver_id)->first();
                        $result = json_encode([
                            'Driver'     => $driver->lastname,
                            'Status'     => $data->status,
                            'Date'       => $data->date,
                        ]);
                        return $result;
                    default:
                        return $data;
                }
            }
        }
        return $data;
    }

    public static function ensure($expr, $errorCode, $data=Null, $driverStatus = Null)
    {
        if (!$expr) {
            $logger = new Logger('LoggerException');
            // Now add some handlers
            $logger->pushHandler(new StreamHandler(__DIR__.'/../../storage/logs/DaemonLog_Errors.log', Logger::DEBUG));
            $logger->pushHandler(new FirePHPHandler());
            // You can now use your logge
            $response = json_encode([
                'error_code' => $errorCode,
                'message'    => self::getUberMessage($errorCode),
                'responce'   => self::getData($data, $driverStatus),
            ]);
            $logger->addInfo($response);
        }
    }

    public static function log($expr, $errorCode, $data=Null, $driverStatus = Null)
    {
        if ($expr) {
            $logger = new Logger('LoggerException');
            // Now add some handlers
            $logger->pushHandler(new StreamHandler(__DIR__.'/../../storage/logs/DaemonLog_Logs.log', Logger::DEBUG));
            $logger->pushHandler(new FirePHPHandler());
            // You can now use your logge
            $response = json_encode([
                'error_code' => $errorCode,
                'message'    => self::getUberMessage($errorCode),
                'responce'   => self::getData($data, $driverStatus),
            ]);
            $logger->addInfo($response);
        }
    }

}