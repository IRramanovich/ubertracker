<?php

namespace App\Lib;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Helpers\Helpers;


class Surge
{
    public $carCount = 0;

    private $surges = [];

    public function __construct()
    {
        $this->loadSurgeArray();
    }

    public function loadSurgeArray()
    {
        setlocale(LC_ALL, 'ru_RU.UTF-8');
        $surge = Storage::disk('local')->get('surge.txt');

        $ar = explode("\n", $surge);
        $this->carCount = array_shift($ar);
        foreach ($ar as  $one){
            if($one != ""){
                $this->addSurgeLineToArray($one);
            }
        }
    }

    public function addSurgeLineToArray( $string )
    {
        $arr = explode(",", $string);
        array_walk($arr, 'trim');
        $day = $arr[0];
        $this->surges[$day][] = [ (int)$arr[1], (int)$arr[2], (int)$arr[3], (int)$arr[4]];
    }

    public function isSurgeTime(Carbon $date)
    {
        Carbon::setLocale('ru');
        $day = str_replace('.', '', $date->formatLocalized('%a'));
        foreach($this->surges[$day] as $surge){
            if($this->checkDate($date, $surge)){
                return true;
            }
        }
        return false;
    }

    public function isSurgeTimeOfflineInterval(Carbon $dateStart, Carbon $dateEnd)
    {
        $overlaps = [];
        $surges = $this->getSurgesFromInterval($dateStart, $dateEnd);

        foreach($surges as $daySurges){
            foreach($daySurges as $surge){
                $overlap = $this->getOverLap($surge['start'], $surge['end'], $dateStart, $dateEnd);
                $overlap['isSurge'] = true;
                $overlaps[] = $overlap;
            }
        }

        return $this->createIntervalsFromOverlaps($overlaps, $dateStart, $dateEnd);
    }

    public function getOverLap($dateStart1, $dateEnd1, $dateStart2, $dateEnd2)
    {
        $r1s = $dateStart1;
        $r1e = $dateEnd1;

        $r2s = $dateStart2;
        $r2e = $dateEnd2;

        if ($r1s >= $r2s && $r1s <= $r2e || $r1e >= $r2s && $r1e <= $r2e || $r2s >= $r1s && $r2s <= $r1e || $r2e >= $r1s && $r2e <= $r1e) {

            $res = array(
                'start' => $r1s > $r2s ? $r1s : $r2s,
                'end' => $r1e < $r2e ? $r1e : $r2e
            );

        } else return [];

        return $res;
    }

    public function createIntervalsFromOverlaps($overlaps, Carbon $dateStart, Carbon $dateEnd)
    {
        $intervals[] = ['start' => $dateStart];
        foreach($overlaps as $overlap){
            end($intervals);
            $lastInterval = &$intervals[key($intervals)];

            if($lastInterval['start'] != $overlap['start'] && $overlap['start'] != $dateEnd){
                $lastInterval['end'] = $overlap['start']->copy()->subSecond();
                $intervals[] = $overlap;
            } else {
                $lastInterval['end'] = $overlap['end']->copy()->subSecond();
                $lastInterval['isSurge'] = $overlap['isSurge'];
            }

            if($overlap['end'] != $dateEnd){
                $intervals[] = ['start' => $overlap['end']];
            }
        }
        end($intervals);
        $lastInterval = &$intervals[key($intervals)];
        $lastInterval['end'] = $dateEnd;
        return $intervals;
    }

    public function checkDate(Carbon $date, $surge)
    {
        if( $date->hour>=$surge[0] && $date->hour<$surge[1] ){
            return $date;
        }
        return false;
    }

    public function getSurgesFromInterval(Carbon $dateStart, Carbon $dateEnd)
    {
        $days = $this->generateDateRange($dateStart, $dateEnd);

        return $this->getSurgeIntervals($dateStart, $dateEnd, $days);
    }

    private function generateDateRange(Carbon $dateStart, Carbon $dateEnd)
    {
        $dates = [];

        for($date = $dateStart->copy(); $date->lte($dateEnd); $date->addDay()->startOfDay()) {
            Carbon::setLocale('ru');
            $day = str_replace('.', '', $date->formatLocalized('%a'));
            $dates[$day]['start'] = $date->copy();
            if( $date->copy()->endOfDay()->gte($dateEnd) ){
                $dates[$day]['end'] = $dateEnd->copy();
            } else {
                $dates[$day]['end'] = $date->copy()->endOfDay()->addSecond();
            }
        }
        return $dates;
    }

    private function getFirstDaySurges(Carbon $date, $day)
    {
        $surgeArray = [];
        $daySurges = $this->getDaySurges($day);
        foreach($daySurges as $surge){
            if( $start = $this->checkDate($date, $surge) ){
                $surgeArray[$day] = $start;
                $surgeArray[$day] = $surge[1];
            }
        }
        return $surgeArray;
    }

    private function getLastDaySurges(Carbon $date, $day)
    {
        $surgeArray = [];
        $daySurges = $this->getDaySurges($day);
        foreach($daySurges as $surge){
            if( $end = $this->checkDate($date, $surge) ){
                $surgeArray[$day] = $surge[0];
                $surgeArray[$day] = $end;
            }
        }
        return $surgeArray;
    }

    private function getSurgeIntervals(Carbon $dateStart, Carbon $dateEnd, $days)
    {
        $surges = [];

        foreach($days as $day => $shiftTime){
            $daySurges = $this->getDaySurges($day);
            foreach( $daySurges as $daySurge ){

                $surgeStart = $shiftTime['start']->copy()->hour($daySurge[0])->minute(0)->second(0);
                $surgeEnd = $shiftTime['start']->copy()->hour($daySurge[1])->minute(0)->second(0);

                $surge = $this->getOverLap($surgeStart, $surgeEnd, $shiftTime['start'], $shiftTime['end']);

                if($surge){
                    $surge['offline'] = [];
                    $surge['offlineTime'] = 0;
                    $surge['trips'] = [];
                    $surge['tripsFee'] = 0.0;
                    $surge['expectedTripsInHour'] = $daySurge[3];
                    $surge['surgeRate'] = $daySurge[2];
                    $surges[$day][] = $surge;
                }
            }
        }
        return $surges;
    }

    private function getDaySurges($day)
    {
        return $this->surges[$day];
    }

    public function getCarCount()
    {
        return $this->carCount;
    }

    public function getSurgeInterval($offlines, $surges, $trips){
        foreach($offlines as $key => $offline) {
            foreach ($surges as &$daySurges) {
                foreach ($daySurges as &$surge) {
                    if ($offline->start->between($surge['start'], $surge['end']) || $offline->end->between($surge['start'], $surge['end'])) {
                        $surge['offline'][] = $offline;
                        $surge['offlineTime'] += $offline->offlineDuration;
                        unset($offlines[$key]);
                        continue(1);
                    }
                }
            }
        }

        foreach ($surges as &$daySurges) {
            foreach ($daySurges as &$surge) {
                foreach($trips as $trip){

                    $tripDate = Carbon::parse($trip->date)->timezone('Europe/Minsk');
                    if( $tripDate->between($surge['start'], $surge['end']) ){
                        $surge['trips'][] = $trip;
                        $surge['tripsFee'] += $trip->total;
                    }
                }
                $surge['onlineTime'] = Helpers::secondsToTime($surge['start']->diffInSeconds($surge['end']) - $surge['offlineTime']);
                $surge['onlineTimeSec'] = $surge['start']->diffInSeconds($surge['end']) - $surge['offlineTime'];
                $surge['offlineTimeSec'] = $surge['offlineTime'];
                $surge['offlineTime'] = gmdate("H:i:s", $surge['offlineTime']);
                $surge['expectedFee'] = round( $surge['onlineTimeSec']/3600 * $surge['surgeRate'] * 0.75, 2);
                $surge['expectedTrips'] = round( $surge['onlineTimeSec'] / 3600 *$surge['expectedTripsInHour'], 2);

                if( count($surge['trips'])  > $surge['expectedTrips']){
                    $surge['tripCondition'] = true;

                    if($surge['tripsFee'] < $surge['expectedFee']){
                        $surge['bonus'] = $surge['expectedFee'] - $surge['tripsFee'];
                    } else {
                        $surge['bonus'] = 0;
                    }
                } else {
                    $surge['tripCondition'] = false;
                }
            }

        }
        return $surges;
    }
}