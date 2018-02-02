<?php

namespace App\Lib;

use App\DriversStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use App\Trips;
use App\Shifts;
use App\DriversAllStatuses;
use \App\Lib\Geo;
use SlackApi;
use SlackUser;
use SlackChat;

class UberNotification
{
    private $formatDate = 'H:i:s d-m-Y';

    public function __construct()
    {
        $this->to = env('NOTIFICATIONS_EMAIL');
    }

    public function offlineOnline($driver, Carbon $date)
    {
        $this->send('Водитель вернулся в онлайн', $driver.' вернулся в онлайн ' .$date->format( $this->formatDate ));
    }

    public function offlineSurge($driver, Carbon $date)
    {
        $this->send('СРОЧНО: Водитель ушел в офлайн пиковое время', $driver.' ушел в офлайн в пиковое время ' . $date->format( $this->formatDate ));
    }

    public function offline($driver, Carbon $date)
    {
        $this->send('Водитель ушел в офлайн', $driver.' ушел в офлайн ' . $date->format( $this->formatDate ));
    }

    public function offline60($driver, Carbon $date)
    {
        $this->send('ВАЖНО:Водитель ушел в офлайн более 60 минут', $driver.' ушел в офлайн в не пиковое время ' . $date->format( $this->formatDate ));
    }

    public function newShift($driverStatus, Carbon $date)
    {
        $this->send('Водитель начал смену', $driverStatus->driver->getName().' начал смену в ' . $date->format( $this->formatDate ));
    }

//    public function shiftReport(Shifts $shift, $notPreliminarily = true)
//    {
//        $surges = \Surge::getSurgesFromInterval($shift->start, $shift->lastChangeStatus->date);
//        $data = \UberRequest::getShiftData($shift);
//        if(!$data && $notPreliminarily){
//            $shift->lastChangeStatus->status = 'Offline';
//            $shift->lastChangeStatus->timestamps = false;
//            $shift->lastChangeStatus->save();
//            return false;
//        }
//        $end = $shift->lastChangeStatus->date;
//        if(!is_null($shift->end)){
//            $end = $shift->end;
//        }
//        $trips = \UberRequest::getShipTripsByIntervalNew($data, $shift->start, $end);
//        $currentStatuses  = DriversAllStatuses::where('shift_id', $shift->id)->get();
//        $totalSumm = \UberRequest::getSummNew($data, $shift);
//        $surgeFee = \UberRequest::getSummByKey((array) $trips, 'surge');
//        $totalDistance = \UberRequest::getSummByKey((array) $trips, 'distance') * env('MILES_TO_KM');
//        $totalDuration = \UberRequest::getSummByKey((array) $trips, 'duration');
//        $shiftDuration = $shift->start->diffInSeconds( $shift->lastChangeStatus->start );
//        $offlineTime = $shift->offlines;
//        $surge = \Surge::getSurgeInterval($shift->offlines, $surges, $trips);
//
//        $offlineTotalTime = $offlineTime->sum('offlineDuration');
//
//        $surges = $surge;
//        $offlineSurge = 0;
//        foreach ($surges as $surge){
//            $offlineSurge =+ \UberRequest::getSummByKey((array) $surge, 'offlineTimeSec');
//        }
//
//
//        $shiftParam = [
//            'total' => $totalSumm,
//            'surge' => (float)$surgeFee,
//            'offline_surge' => $offlineSurge,
//            'offline_not_surge' => $offlineTotalTime,
//        ];
//
//        //подсчет дистанции по всем поездкам
//        if($notPreliminarily) {
//            $trakerDistance = 0;
//            for ($i = 0; $i < count($currentStatuses) - 1; $i++) {
//                $distance = Geo::distance($currentStatuses[$i], $currentStatuses[$i + 1], 6);
//                if ($distance > 0) {
//                    $trakerDistance = $trakerDistance + $distance;
//                }
//            }
//            $shiftParam = [
//                'total' => $totalSumm,
//                'surge' => (float)$surgeFee,
//                'offline_surge' => $offlineSurge,
//                'offline_not_surge' => $offlineTotalTime,
//                'end' => $end,
//                'tracker_distance' => $trakerDistance,
//            ];
//        }
//
//        $shift->fill($shiftParam);
//        $shift->save();
//
//        Trips::saveFromUberData($trips, $shift);
//
//        $timePercentage = round( $totalDuration/$shiftDuration*100, 2);
//        uasort($trips, function($a, $b){
//            if ($a == $b) {
//                return 0;
//            }
//            $aDate = Carbon::parse($a->date)->timezone('Europe/Minsk');
//            $bDate = Carbon::parse($b->date)->timezone('Europe/Minsk');
//            return ( $aDate->gt($bDate) ) ? 1 : -1;
//        });
//
//        $shiftData = [
//            'driver'            => $shift->driver->getName(),
//            'shiftStart'        => $shift->start->format('H:i:s d-m-Y'),
//            'shiftEnd'          => $shift->lastChangeStatus->date->format('H:i:s d-m-Y'),
//            'totalSumm'         => $totalSumm,
//            'trips'             => $trips,
//            'tripsCount'        => count($trips),
//            'totalDistance'     => $totalDistance,
//            'totalDuration'     => gmdate("H:i:s" ,$totalDuration),
//            'shiftDuration'     => gmdate("H:i:s" ,$shiftDuration),
//            'timePercentage'    => $timePercentage,
//            'offlineTime'       => $offlineTime,
//            'offlineTotalTime'  => gmdate("H:i:s" , $offlineTotalTime),
//            'surgeFee'          => $surgeFee,
//            'surges'            => $surges,
//        ];
//
//        if($notPreliminarily){
//            $shift->info = serialize($shiftData);
//            $shift->save();
//
//            $message = View::make('admin.shiftReport', $shiftData)->render();
//            $this->send('Завершение смены ' . $shift->driver->getName(), $message );
//
//            $shift->lastChangeStatus->status = 'shiftEnd';
//            $shift->lastChangeStatus->timestamps = false;
//            $shift->lastChangeStatus->save();
//        }
//        return $shiftData;
//
//    }

    public static function sendSlackMessageToDriver($driver_login, $message){
        $id = NULL;
        $userList = SlackUser::lists();
        if($userList->ok){
            foreach ($userList->members as $user){
                if($user->name == $driver_login){
                    $id = $user->id;
                    continue;
                }
            }
            try{
                $response = SlackChat::message($id, $message);
                return $response;
            }catch (Exception $e){
                return false;
            }
        }
        return false;
    }

    public function send($subject, $message)
    {
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        return mail($this->to, $subject, $message, $headers);
    }
}