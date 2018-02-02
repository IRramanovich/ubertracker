<?php
/**
 * Created by PhpStorm.
 * User: a123
 * Date: 11/17/17
 * Time: 17:31
 */

namespace App\Lib;

use App\Lib\Wialon;
use App\Offline;
use Debugbar;

class WialonReport
{
    private $key = "02f3c7f7899b662e8abf96d506fd662c79E31F1ABB64B813CA4F63AC84514EA1936AD58C";
    //02f3c7f7899b662e8abf96d506fd662c0C3E7EF6426BEC0C25B2C42550E89B8682A11AAB
    private $wialon_api;

    function __construct()
    {
        $this->wialon_api = new Wialon();
    }

    private function checkLogin($login_result)
    {
        $login_result = json_decode($login_result, true);
        if (array_key_exists('error', $login_result)) {
            return FALSE;
        }
        return TRUE;
    }

    public function getLastMessage()
    {
        $login_result = $this->wialon_api->login($this->key);
        if ($this->checkLogin($login_result)) {
            $wialon_units = $this->wialon_api->core_search_items('{"spec":{"itemsType":"avl_unit","propName":"sys_name","propValueMask":"*","sortType":"sys_name"},"force":1,"flags":1025,"from":0,"to":0}');
            $wialon_units = json_decode($wialon_units, true);
        }
        $this->wialon_api->logout();

        return $wialon_units;
    }

    public function getOfflineDistance($shift, $cars)
    {
        $login_result = $this->wialon_api->login($this->key);
        if ($this->checkLogin($login_result)) {
            $wialon_units = $this->wialon_api->core_search_items('{"spec":{"itemsType":"avl_unit","propName":"sys_name","propValueMask":"*","sortType":"sys_name"},"force":1,"flags":1,"from":0,"to":0}');
            $wialon_units = json_decode($wialon_units, true);
            $offline_distance = 0;
            if ($shift->car_id != 0) {
                $car_numder = explode(' ', $cars[$shift->car_id])[0];

                $shift_offlines = Offline::getShiftOffline($shift->id);
                $curre = 1;
                foreach ($wialon_units["items"] as $wialon_item) {
                    $wialon_item_name = explode(' ', $wialon_item["nm"])[1];
                    if ($wialon_item_name == $car_numder) {
                        foreach ($shift_offlines as $shift_offline){
                            $timestamp_from = strtotime($shift_offline->start);
                            $timestamp_to = strtotime($shift_offline->end);
                            $res = $this->wialon_api->report_exec_report('{"reportResourceId":16515118,"reportTemplateId":1,"reportObjectId":' . $wialon_item["id"] . ',"reportObjectSecId":0,"interval":{"from":' . $timestamp_from . ',"to":' . $timestamp_to . ',"flags":0}}');
                            $res = json_decode($res, true);
                            $curre = floatval(explode(' ', $res["reportResult"]["stats"][4][1])[0]);
                            $offline_distance = $offline_distance + $curre;
                        }
                    }
                }
            }

        }
        $this->wialon_api->logout();

        return $offline_distance;
    }

    public function getOfflineTrack($shift, $cars)
    {
        $login_result = $this->wialon_api->login($this->key);
        if ($this->checkLogin($login_result)) {
            $wialon_units = $this->wialon_api->core_search_items('{"spec":{"itemsType":"avl_unit","propName":"sys_name","propValueMask":"*","sortType":"sys_name"},"force":1,"flags":1,"from":0,"to":0}');
            $wialon_units = json_decode($wialon_units, true);

            $result = [];

            if ($shift->car_id != 0){
                $car_numder = explode(' ', $cars[$shift->car_id])[0];

                $shift_offlines = Offline::getShiftOffline($shift->id);

                foreach ($wialon_units["items"] as $wialon_item) {
                    $wialon_item_name = explode(' ', $wialon_item["nm"])[0];
                    if ($wialon_item_name == $car_numder) {
                        foreach ($shift_offlines as $shift_offline){
                            $timestamp_from = strtotime($shift_offline->start);
                            $timestamp_to = strtotime($shift_offline->end);
                            $response = $this->wialon_api->messages_load_interval('{"itemId":' . $wialon_item["id"] . ',"timeFrom":' . $timestamp_from . ',"timeTo":' . $timestamp_to . ',"flags":1, "flagsMask":65281, "loadCount":"0xffffffff"}}');
                            $res = WialonReport::processingWialonDataToUber($shift, $response, $shift_offline->start);
                            $result[$shift_offline->start->format('Y-m-d H:i:s')] = $res;
                        }
                    }
                }
            }

        }
        $this->wialon_api->logout();

        return $result;
    }

    public static function processingWialonDataToUber($shift, $wialonData) {
        $uberData = [];
        $date = new \DateTime();
        $wialonData = json_decode($wialonData);
        foreach ($wialonData->messages as $messsge) {
            $uberItem = [
                "id" => null,
                "driver_id" => $shift->driver_id,
                "latitude" => $messsge->pos->y,
                "longitude" => $messsge->pos->x,
                "course" => $messsge->pos->c,
                "status" => "Offline",
                "date" => $date->setTimestamp($messsge->t)->format('Y-m-d H:i:s'),
                "shift_id" => $shift->id,
                "trip_id" => 'offline'
            ];
            array_push($uberData, $uberItem);
        }
        return $uberData;
    }

    public static function mergeWialonAndUberData($shift, $wialonData) {
        $result = [];
        $singleStatuses = [];

        for ($i = 0; $i < count($shift->singleStatuses); $i++) {
            if ($shift->singleStatuses[$i]->status != 'offline') {
                $uberItem = [
                    "id" => null,
                    "driver_id" => $shift->singleStatuses[$i]->driver_id,
                    "latitude" => $shift->singleStatuses[$i]->latitude,
                    "longitude" => $shift->singleStatuses[$i]->longitude,
                    "course" => $shift->singleStatuses[$i]->course,
                    "status" => $shift->singleStatuses[$i]->status,
                    "date" => $shift->singleStatuses[$i]->date->format('Y-m-d H:i:s'),
                    "shift_id" => $shift->id,
                    "trip_id" => $shift->singleStatuses[$i]->trip_id
                ];
                array_push($singleStatuses, $uberItem);
            }
        }

        $i = 0;
        $last_i = 0;
        foreach($wialonData as $key => $value) {
            while ((strtotime($key)) >= (strtotime($singleStatuses[$i]['date']))) {
                $i++;
            }
            $result = array_merge($result, array_slice($singleStatuses, $last_i, $i-$last_i), $value);
            $last_i = $i;
            while ((strtotime($value[count($value)-1]['date'])) >= (strtotime($singleStatuses[$i]['date']))) {
                $i++;
            }
        }
        $result = array_merge($result, array_slice($singleStatuses, $last_i, count($singleStatuses)-1));

        return $result;
    }
}