<?php
/**
 * Created by PhpStorm.
 * User: iramanovich
 * Date: 3.4.17
 * Time: 17.25
 */

namespace App\Service;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;


class UberDaemonOffline
{
    const CODE_FAIL                             = 0;
    const CODE_NEW_DRIVER_NOT_SAVE_OR_CREATE    = 1;
    const CODE_NEW_SHIFT_NO_CREATE              = 2;
    const CODE_NEW_SHIFT_NO_CREATE_IN_RESERVE   = 3;
    const CODE_SHIFT_NOT_RECOUNT                = 4;
    const CODE_CHECK_DRIVER                     = 5;


    public static function getUberMessage($code)
    {
        $msgArr = [
            0  => 'Fail : Required parameter are missing',
            1  => 'Не была создана или не обновилась запись для бодителя',
            2  => 'Смена не была создана в первом случае',
            3  => 'Смена не была создана в резервном случае',
            4  => 'Пересчет смены с пустим info',
            5  => 'Get driver',
        ];

        if (isset($msgArr[$code])) {
            return $msgArr[$code];
        } else {
            return "No error message is set yet";
        }
    }

    public static function getData($data)
    {
        if($data != Null && is_object($data)){
            $data_class = get_class($data);
            switch ($data_class){
                case 'Shifts':
                    $result = json_encode([
                        'Shift_ID'  => $data->id,
                        'Start'     => $data->start,
                        'End'       => $data->end,
                    ]);
                    return $result;
                case 'DriversStatus':
                    $result = json_encode([
                        'Status'     => $data->status,
                        'Date'       => $data->date,
                    ]);
                    return $result;
                default:
                    return json_encode($data);
            }
        }
        return $data;
    }

    public static function ensure($expr, $errorCode, $data=Null)
    {
        if (!$expr) {
            $logger = new Logger('LoggerException');
            // Now add some handlers
            $logger->pushHandler(new StreamHandler(__DIR__.'/../../storage/logs/DaemonOfflineLog_Error.log', Logger::DEBUG));
            $logger->pushHandler(new FirePHPHandler());
            // You can now use your logge
            $response = json_encode([
                'error_code' => $errorCode,
                'message'    => self::getUberMessage($errorCode),
                'responce'   => self::getData($data),
            ]);
            $logger->addInfo($response);
        }
    }

    public static function log($expr, $errorCode, $data=Null)
    {
        if ($expr) {
            $logger = new Logger('LoggerException');
            // Now add some handlers
            $logger->pushHandler(new StreamHandler(__DIR__.'/../../storage/logs/DaemonOfflineLog_Logs.log', Logger::DEBUG));
            $logger->pushHandler(new FirePHPHandler());
            // You can now use your logge
            $response = json_encode([
                'error_code' => $errorCode,
                'message'    => self::getUberMessage($errorCode),
                'responce'   => self::getData($data),
            ]);
            $logger->addInfo($response);
        }
    }
}