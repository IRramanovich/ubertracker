<?php

namespace App\Lib;


class Geo
{
    public static function distance($status, $newStatus, $accuracy) {

        $theta = $status->longitude - $newStatus->longitude;
        $dist = sin(deg2rad($status->latitude)) *
                sin(deg2rad($newStatus->latitude)) +
                cos(deg2rad($status->latitude)) *
                cos(deg2rad($newStatus->latitude)) *
                cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return round($miles * 1.609344, $accuracy);
    }
    
}