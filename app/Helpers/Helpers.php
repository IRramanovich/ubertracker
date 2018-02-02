<?php

namespace App\Helpers;

class Helpers
{
    public static function secondsToTime($seconds) {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format('%a %H:%I:%S');
    }
}