<?php
/**
 * Created by PhpStorm.
 * User: iramanovich
 * Date: 22.3.17
 * Time: 12.11
 */

namespace App\Service;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class UberShiftLog
{
    const CODE_FAIL                             = 0;
    const CODE_SHIFT_NOT_CREATE_FIRST_INTERVAL  = 1;
    const CODE_SHIFT_NOT_CREATE_SECOND_INTERVAL = 2;
    const CODE_SHIFT_NOT_CREATE_NORMAL          = 3;
    const CODE_SHIFT_NOT_CLOSE_MAIN_FIELD       = 4;
    const CODE_SHIFT_NOT_SAVE_INFO_FILED        = 5;
    const CODE_SHIFT_NOT_SAVE_STATUS_SHIFT_END  = 6;
    const CODE_SHIFT_NOT_SAVE_FIELD_AFTER_COUNT = 7;
    const CODE_SHIFT_NOT_SAVE_DROP_ORDER        = 8;
    const CODE_SHIFT_NOT_SAVE_CAR_ID            = 9;
    const CODE_SHIFT_NOT_SAVE_RECOUNT           = 10;
    const CODE_SHIFT_NOT_SAVE_FIELD_RECOUNT     = 11;
    const CODE_SHIFT_CLOSE_MAIN_FIELD           = 12;
    const CODE_SHIFT_SAVE_INFO_FILED            = 13;
    const CODE_SHIFT_SAVE_STATUS_SHIFT_END      = 14;
    const CODE_SHIFT_CREATE_FIRST_INTERVAL      = 15;
    const CODE_SHIFT_CREATE_SECOND_INTERVAL     = 16;
    const CODE_SHIFT_CREATE_NORMAL              = 17;

    public static function getUberMessage($code)
    {
        $msgArr = [
            0  => 'Fail : Required parameter are missing',
            1  => 'Смена не создана для интервала 7:30 - 12:30',
            2  => 'Смена не создана для интервала 18:30 - 22:30',
            3  => 'Cмена не создана вне интервалов',
            4  => 'Смена не закрылась при сохранении главных полей',
            5  => 'Не сохранилось поле info при закрытии смены',
            6  => 'Не создалась запись ShiftEnd в таблице последних статусов',
            7  => 'Не сохранились данные при пересчете смены',
            8  => 'Не сохранилось количество пропущеных заказов',
            9  => 'Не сохранился номер автомобиля',
            10 => 'Не сохранилось поле info при пересчете смены',
            11 => 'Не сохранилось при пересчете смены',
            12 => 'При закрытии смены главные данные сохранились',
            13 => 'При закрытии смены сохранилось поле инфо',
            14 => 'При закрытии смены сохранилсяя новый статус',
            15 => 'Смена создана для интервала 7:30 - 12:30',
            16 => 'Смена создана для интервала 18:30 - 22:30',
            17 => 'Cмена создана вне интервалов',
        ];

        if (isset($msgArr[$code])) {
            return $msgArr[$code];
        } else {
            return "No error message is set yet";
        }
    }

    public static function getData($data)
    {
        if($data != Null){
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
            }
        }
        return $data;
    }

    public static function ensure($expr, $errorCode, $data=Null)
    {
        if (!$expr) {
            $logger = new Logger('LoggerException');
            // Now add some handlers
            $logger->pushHandler(new StreamHandler(__DIR__.'/../../storage/logs/ShiftLog_Errors.log', Logger::DEBUG));
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
            $logger->pushHandler(new StreamHandler(__DIR__.'/../../storage/logs/ShiftLog_Logs.log', Logger::DEBUG));
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