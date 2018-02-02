<?php

namespace App;

use GuzzleHttp\Psr7\Request;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use \App\Lib\Geo;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use App\Lib\ShifControllLibrary;
use App\Service\UberShiftLog;
use Debugbar;

class Shifts extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Shifts';

    protected $dates = [
        'start',
        'end'
    ];

    public $timestamps = false;

    protected $fillable = [
        'id',
        'driver_id',
        'total',
        'surge',
        'offline_surge',
        'offline_not_surge',
        'start',
        'end',
        'mileage_start',
        'mileage_end',
        'fuel_start',
        'refill',
        'fuel_end',
        'gas_refill',
        'car_id',
        'tracker_distance',
        'drop_order'
    ];

    public function driver()
    {
        return $this->hasOne('App\Drivers', 'id', 'driver_id');
    }
    
    public function car()
    {
        return $this->hasOne('App\Cars', 'id', 'car_id');
    }

    public function lastStatus()
    {
        return $this->hasOne('App\DriversLastStatus', 'driver_id', 'driver_id');
    }

    public function lastChangeStatus()
    {
        return $this->hasOne('App\DriversStatus', 'driver_id', 'driver_id')
            ->orderBy('id', 'DESC')
            ->limit(500);
    }

    public function lastShiftStatus()
    {
        return $this->hasOne('App\DriversAllStatuses', 'shift_id', 'id')
            ->orderBy('DriversAllStatuses.date', 'DESC')
            ->limit(500);
    }

    public function offlines()
    {
        return $this->hasMany('App\Offline', 'shift_id', 'id')
            ->select('Offline.*',  \DB::raw('TIMESTAMPDIFF(SECOND, Offline.start, Offline.end) as offlineDuration'))
            ->orderBy('Offline.start', 'ASC');
    }

    public function offlinesSurge()
    {
        return $this->hasMany('App\Offline', 'shift_id', 'id')
            ->select('Offline.*',  \DB::raw('TIMESTAMPDIFF(SECOND, Offline.start, Offline.end) as offlineDuration'))
            ->where('is_surge', 1)
            ->orderBy('Offline.start', 'ASC');
    }

    public function offlinesNotSurge()
    {
        return $this->hasMany('App\Offline', 'shift_id', 'id')
            ->select('Offline.*',  \DB::raw('TIMESTAMPDIFF(SECOND, Offline.start, Offline.end) as offlineDuration'))
            ->where('is_surge', 0)
            ->orderBy('Offline.start', 'ASC');
    }

    public function singleStatuses()
    {
        return $this->hasMany('App\DriversAllStatuses', 'shift_id', 'id')
            ->select('DriversAllStatuses.*', 'Trips.id as trip_id')
            ->orderBy('DriversAllStatuses.id', 'ASC')
            ->leftJoin('Trips', function($join)
            {
                $join->on('Trips.driver_id', '=', 'DriversAllStatuses.driver_id');
                $join->on('DriversAllStatuses.date','>', 'Trips.begin_trip_at');
                $join->on('DriversAllStatuses.date','<', \DB::raw('DATE_ADD( Trips.begin_trip_at, INTERVAL Trips.duration second)'));
            });
    }

    public function statuses()
    {
        $dateTime = new \DateTime();
        $dateTime->sub(new \DateInterval('P1D'));
        $date = $dateTime->format('Y-m-d 00:00:00');

        return $this->hasMany('App\DriversAllStatuses', 'shift_id', 'id')
            ->select('DriversAllStatuses.*', 'Trips.id as trip_id')
            ->where('DriversAllStatuses.date','>=', $date)
            ->orderBy('DriversAllStatuses.id', 'ASC')
            ->leftJoin('Trips', function($join)
            {
                $join->on('Trips.driver_id', '=', 'DriversAllStatuses.driver_id');
                $join->on('DriversAllStatuses.date','>', 'Trips.begin_trip_at');
                $join->on('DriversAllStatuses.date','<', \DB::raw('DATE_ADD( Trips.begin_trip_at, INTERVAL Trips.duration second)'));
            });
    }

    public function online()
    {
        $dateTime = new \DateTime();
        $dateTime->sub(new \DateInterval('P1D'));
        $date = $dateTime->format('Y-m-d 00:00:00');

        return $this->hasMany('App\DriversAllStatuses', 'shift_id', 'id')
            ->with('driver')
            ->join(\DB::raw("(SELECT driver_id, date, rank, @currcount as qwerty FROM(
                                                SELECT driver_id, status, date,
                                                   @currcount := IF(@currvalue = driver_id COLLATE utf8_unicode_ci, @currcount + 1, 1) AS rank,
                                                   @currvalue := driver_id AS whatever
                                                FROM
                                                  (SELECT @currcount:= -1) s,
                                                  (SELECT @currvalue:= -1) c,
                                                  (SELECT *
                                                   FROM DriversStatus
                                                   WHERE date >= '".$date."'
                                                   ORDER BY driver_id, date DESC
                                                  ) t
                                            ) ra
                                            WHERE ra.rank = IF(@currcount  > 4, 4, @currcount)
                                       ) rank"), function($join){
                $join->on('rank.driver_id', '=', 'DriversAllStatuses.driver_id');
            })
            ->where('DriversAllStatuses.date', '>=', \DB::raw('rank.date'))
            ->orderBy('DriversAllStatuses.id', 'ASC');

    }

    public function offline()
    {
        return $this->hasMany('App\DriversStatus', 'driver_id', 'driver_id')
            ->select('*', \DB::raw('TIMESTAMPDIFF(SECOND, DriversStatus.date, d2.date) as offlineDuration'))
            ->join('DriversStatus as d2', function($join){
                $join->on('DriversStatus.driver_id', '=', 'd2.driver_id');
                $join->on('d2.date', '=', \DB::raw('(select
                                                                    date
                                                             from
                                                                    DriversStatus d3
                                                             where
                                                                    d3.id > DriversStatus.id
                                                                 and
                                                                    d3.driver_id = DriversStatus.driver_id
                                                             LIMIT 1)'));
            })
            ->where('DriversStatus.status', '=', 'Offline')
            ->where('DriversStatus.date', '>', $this->start);
    }

    public function scopeWithLastStatus($query)
    {
        return $query->addSelect('Shifts.*', 'DriversLastStatus.date as last')
            ->join('DriversLastStatus', 'DriversLastStatus.driver_id', 'Shifts.driver_id');
    }

    public static function scopeOfflineOnly($query)
    {
        return $query->addSelect('Shifts.*')
                    ->join('DriversStatus as d2', function($join){
                        $join->on('Shifts.driver_id', '=', 'd2.driver_id');
                        $join->on('d2.id', '=', \DB::raw('(select
                                                                            id
                                                                     from
                                                                            DriversStatus d3
                                                                     where
                                                                            d3.driver_id = Shifts.driver_id
                                                                     ORDER BY
                                                                        d3.date DESC
                                                                     LIMIT 1)'));
                    })
                    ->whereNull('Shifts.end')
                    ->where('d2.status', 'Offline');
    }

    public function getDates(){
        return ['start', 'end'];
    }

    public function newShiftsData(){
        return $this->hasMany('App\Trips', 'shift_id', 'id');
    }

    public function updateFuelData($request){
        $this->mileage_start = $request->mileage_start;
        $this->mileage_end   = $request->mileage_end;
        $this->fuel_start    = $request->fuel_start;
        $this->refill        = $request->refill;
        $this->fuel_end      = $request->fuel_end;
        $this->gas_refill    = $request->gas_refill;
        $this->car_id        = $request->car_id;
        return $this->save();
    }

    public static function getNewShiftData($shifts){
        $response = [];
        foreach ($shifts as $shift){
            if($shift->info && $shift->info != ''){
                $fixed_data = preg_replace_callback ( '!s:(\d+):"(.*?)";!', function($match) {
                    return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
                },$shift->info );
                $shiftData = unserialize($fixed_data);
                if($shiftData['surges']){
                    foreach ($shiftData['surges'] as $data){
                        if(isset($data[0])){
                            foreach ($data as $key => $value){
                                $shift->info = $value;
                            }
                        }else{
                            $shift->info = $data;
                        }
                    }
                }else{
                    $Datatest = unserialize($shift->info);
                    $Datatest['tripCondition'] = false;
                    $shift->info = $Datatest;
                }
            }

            $km_per_hour = 0;
            if($shift->newShiftsData->sum('distance')){
                $km_per_hour = ($shift->newShiftsData->sum('distance')*1.60934)/($shift->newShiftsData->sum('duration')/60)*60;
            }

            $bonus = 0;
            $percent_bonus = 0;
            if(isset($shift->info['tripCondition'])) {
                if ($shift->info['tripCondition']) {
                    $bonus = $shift->info['bonus'];
                    if($bonus<>0 && $shift->total != 0)
                    $percent_bonus = $bonus/$shift->total*100;
                    else $percent_bonus = 0;
                }
            }

            $percent_surge = 0;
            if($shift->surge <> 0){
                $percent_surge = $shift->surge/$shift->total*100;
            }

            $order_by_day = 0;
            if($shift->start->diffInHours($shift->end)<>0){
                $order_by_day = $shift->newShiftsData->count('trips')/$shift->start->diffInHours($shift->end)*12;
            }

            $middle_distance = 0;
            if($shift->newShiftsData->count('trips') <> 0){
                $middle_distance = $shift->newShiftsData->sum('distance')*1.6/$shift->newShiftsData->count('trips');
            }

            $mileage = $shift->mileage_end - $shift->mileage_start;

            $middle_distance_order = 0;
            if($shift->newShiftsData->count('trips')<>0){
                $middle_distance_order = ($shift->mileage_end - $shift->mileage_start)/$shift->newShiftsData->count('trips');
            }

            $proportion_singles = 0;
            if($middle_distance_order <> 0){
                $proportion_singles = 1-$middle_distance/$middle_distance_order;
            }

            $expenditure_fuel = $shift->fuel_start+$shift->refill-$shift->fuel_end+$shift->gas_refill;

            $expenditure_fuel_h = 0;
            if($mileage <> 0){
                $expenditure_fuel_h = $expenditure_fuel/($mileage/100);
            }
            
            //Distance count by tracker
            $tracker_distance_part = 0;
            if($mileage>0){
                $tracker_distance_part = 1 - $shift->tracker_distance/$mileage;
            }

            $response[] = [
                'id'                    => $shift->id,
                'driver'                => $shift->driver->getName(),
                'start'                 => $shift->start,
                'end'                   => $shift->end,
                'offline_surge'         => $shift->offline_surge,
                'offline_not_surge'     => $shift->offline_not_surge,
                'drop_order'            => $shift->drop_order,
                'mileage_start'         => $shift->mileage_start,
                'mileage_end'           => $shift->mileage_end,
                'fuel_start'            => $shift->fuel_start,
                'refill'                => $shift->refill,
                'fuel_end'              => $shift->fuel_end,
                'gas_refill'            => $shift->gas_refill,
                'trips'                 => $shift->newShiftsData->count('trips'),
                'duration'              => round($shift->newShiftsData->sum('duration')/60,2),
                'distance'              => round($shift->newShiftsData->sum('distance')*1.60934,2),
                'km_per_hour'           => round($km_per_hour,2),
                'total'                 => round($shift->total,2),
                'surge'                 => round($shift->surge,2),
                'bonus'                 => round($bonus,2),
                'percent_surge'         => round($percent_surge,2),
                'percent_bonus'         => round($percent_bonus,2),
                'shift_length'          => $shift->start->diffInSeconds($shift->end),
                'order_by_day'          => round($order_by_day,2),
                'mileage'               => round($mileage,2),
                'middle_distance_order' => round($middle_distance_order,2),
                'middle_distance'       => round($middle_distance,2),
                'proportion_singles'    => round($proportion_singles,2),
                'expenditure_fuel'      => round($expenditure_fuel,2),
                'expenditure_fuel_h'    => round($expenditure_fuel_h,2),
                'car'                   => $shift->car['car_gov_number'],
                'tracker_distance'      => round($shift->tracker_distance,2),
                'tracker_distance_part' => round($tracker_distance_part,2),
            ];
        }
        return $response;
    }

    public function getAndComposeShiftData(){
        $surges = \Surge::getSurgesFromInterval($this->start, $this->lastChangeStatus->date);
        $data = \UberRequest::getShiftData($this);
        $end = $this->lastChangeStatus->date;
        if(!is_null($this->end)){
            $end = $this->end;
        }
        $trips = \UberRequest::getShipTripsByIntervalNew($data, $this->start, $end);
        $currentStatuses  = DriversAllStatuses::where('shift_id', $this->id)->get();
        $totalSumm = \UberRequest::getSummNew($data, $this);
        $surgeFee = \UberRequest::getSummByKey((array) $trips, 'surge');
        $totalDistance = \UberRequest::getSummByKey((array) $trips, 'distance') * env('MILES_TO_KM');
        $totalDuration = \UberRequest::getSummByKey((array) $trips, 'duration');
        $shiftDuration = $this->start->diffInSeconds($this->lastChangeStatus->start );
        $offlineTime = $this->offlines;
        $surge = \Surge::getSurgeInterval($this->offlines, $surges, $trips);
        $dropOrders = count(DriversStatus::getAllDropOrder($this->driver_id, $this->start, $this->end));
        

        $offlineTotalTime = $offlineTime->sum('offlineDuration');

        $surges = $surge;
        $offlineSurge = 0;
        foreach ($surges as $surge){
            $offlineSurge =+ \UberRequest::getSummByKey((array) $surge, 'offlineTimeSec');
        }

        $timePercentage = round( $totalDuration/$shiftDuration*100, 2);


        Trips::saveFromUberData($trips, $this);
        uasort($trips, function($a, $b){
            if ($a == $b) {
                return 0;
            }
            $aDate = Carbon::parse($a->date)->timezone('Europe/Minsk');
            $bDate = Carbon::parse($b->date)->timezone('Europe/Minsk');
            return ( $aDate->gt($bDate) ) ? 1 : -1;
        });

        $trakerDistance = 0;
        for ($i = 0; $i < count($currentStatuses) - 1; $i++) {
            $distance = Geo::distance($currentStatuses[$i], $currentStatuses[$i + 1], 6);
            if ($distance > 0) {
                $trakerDistance = $trakerDistance + $distance;
            }
        }

        $shiftData = [
            'driver'            => $this->driver->getName(),
            'shiftStart'        => $this->start->format('H:i:s d-m-Y'),
            'shiftEnd'          => $this->lastChangeStatus->date->format('H:i:s d-m-Y'),
            'totalSumm'         => $totalSumm,
            'trips'             => $trips,
            'tripsCount'        => count($trips),
            'totalDistance'     => $totalDistance,
            'totalDuration'     => gmdate("H:i:s" ,$totalDuration),
            'shiftDuration'     => gmdate("H:i:s" ,$shiftDuration),
            'timePercentage'    => $timePercentage,
            'offlineTime'       => $offlineTime,
            'offlineTotalTime'  => gmdate("H:i:s" , $offlineTotalTime),
            'surgeFee'          => (float)$surgeFee,
            'surges'            => $surges,
            'offline_surge'     => $offlineSurge,
            'offline_not_surge' => $offlineTotalTime,
            'end'               => $end,
            'tracker_distance'  => $trakerDistance,
            'drop_orders'        => $dropOrders,
        ];

        return $shiftData;
    }

    public static function createNewShift(DriversStatus $status)
    {
        $shift = self::create([
            'driver_id' => $status->driver_id,
            'start' => $status->date,
        ]);
        UberShiftLog::ensure($shift, UberShiftLog::CODE_SHIFT_NOT_CREATE_NORMAL);
        UberShiftLog::log($shift, UberShiftLog::CODE_SHIFT_CREATE_NORMAL, $shift);

        $uberDriver = \UberRequest::getDriver($shift->driver_id);
        if($uberDriver){
            $car = Cars::getCarByNumber($uberDriver->licensePlate);
            if(!is_null($car)){
                $shift->setCarId($car->id);
            }
        }

        return $shift;
    }

    public function closeShift(){
        $shiftData = $this->getAndComposeShiftData();

        $end = $shiftData['end'];

        $shiftParam = [
            'total'             => $shiftData['totalSumm'],
            'surge'             => $shiftData['surgeFee'],
            'offline_surge'     => $shiftData['offline_surge'],
            'offline_not_surge' => $shiftData['offline_not_surge'],
            'end'               => $end,
            'tracker_distance'  => $shiftData['tracker_distance'],
        ];

        $this->fill($shiftParam);
        $result = $this->save();
        UberShiftLog::ensure($result, UberShiftLog::CODE_SHIFT_NOT_CLOSE_MAIN_FIELD, $this);
        UberShiftLog::log($result, UberShiftLog::CODE_SHIFT_CLOSE_MAIN_FIELD, $this);

        $this->info = serialize($shiftData);
        $result = $this->save();
        UberShiftLog::ensure($result, UberShiftLog::CODE_SHIFT_NOT_SAVE_INFO_FILED, $this);
        UberShiftLog::log($result, UberShiftLog::CODE_SHIFT_SAVE_INFO_FILED, $this);

        $this->lastChangeStatus->status = 'shiftEnd';
        $this->lastChangeStatus->timestamps = false;
        $result = $this->lastChangeStatus->save();
        UberShiftLog::ensure($result, UberShiftLog::CODE_SHIFT_NOT_SAVE_STATUS_SHIFT_END, $this);
        UberShiftLog::log($result, UberShiftLog::CODE_SHIFT_SAVE_STATUS_SHIFT_END, $this);

        ShifControllLibrary::logEarlyEndShift($end, $this);

        return $result;
    }

    public function preliminarilyCountShift(){
        $shiftData = $this->getAndComposeShiftData();
        $shiftParam = [
            'total'             => $shiftData['totalSumm'],
            'surge'             => $shiftData['surgeFee'],
            'offline_surge'     => $shiftData['offline_surge'],
            'offline_not_surge' => $shiftData['offline_not_surge'],
            'tracker_distance'  => $shiftData['tracker_distance'],
            'drop_order'        => $shiftData['drop_orders'],
        ];
        $this->fill($shiftParam);
        $result = $this->save();

        return $result;
    }
    
    public function ReCountShift(){
        $data = \UberRequest::getShiftData($this);
        $shiftData = $this->getAndComposeShiftData();
        $shiftParam = [
            'total'             => $shiftData['totalSumm'],
            'surge'             => $shiftData['surgeFee'],
            'offline_surge'     => $shiftData['offline_surge'],
            'offline_not_surge' => $shiftData['offline_not_surge'],
            'tracker_distance'  => $shiftData['tracker_distance'],
        ];
        $this->fill($shiftParam);
        $result = $this->save();
        UberShiftLog::ensure($result, UberShiftLog::CODE_SHIFT_NOT_SAVE_FIELD_RECOUNT, $this);
        if(!is_null($data)){
            $this->info = serialize($shiftData);
            $result = $this->save();
            UberShiftLog::ensure($result, UberShiftLog::CODE_SHIFT_NOT_SAVE_RECOUNT, $this);
        }
        return true;
    }
    
    public function incrementDropOrder(){
        $this->drop_order += 1;
        $result = $this->save();
        UberShiftLog::ensure($result, UberShiftLog::CODE_SHIFT_NOT_SAVE_DROP_ORDER, $this);
        return $result;
    }

    public function scopeOfflineDrivers($query)
    {
        return $query->select('Shifts.*')
            ->whereNull('Shifts.end')
            ->Join(\DB::raw('(SELECT status, driver_id, MAX(id) as id FROM DriversStatus GROUP BY driver_id DESC) d2'), function($join){
                $join->on('d2.driver_id', '=', 'Shifts.driver_id');
            })
            ->where('d2.status', 'Offline');
    }

    public function scopeOnlineDrivers($query)
    {
        return $query->select('Shifts.*')
            ->whereNull('Shifts.end')
            ->Join(\DB::raw('(SELECT status, driver_id, MAX(id) as id FROM DriversStatus GROUP BY driver_id DESC) d2'), function($join){
                $join->on('d2.driver_id', '=', 'Shifts.driver_id');
            })
            ->whereNotIn('d2.status', ['Offline', 'shiftEnd']);
    }

    public function setCarId($carId){
        $this->car_id = $carId;
        $result = $this->save();
        UberShiftLog::ensure($result, UberShiftLog::CODE_SHIFT_NOT_SAVE_CAR_ID, $this);
        return $result;
    }

    public function getCarId(){
        return $this->car_id;
    }
}
