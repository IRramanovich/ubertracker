<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Surge;
use \App\Lib\Geo;
use Carbon\Carbon;
use Debugbar;

class DriversStatus extends Model
{
	const CREATED_AT = 'date';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'DriversStatus';
	
	protected $fillable = ['driver_id', 'latitude', 'longitude', 'course', 'status', 'date'];
	
	public $timestamps = [ "created_at" ];
	
	protected $dates = [
        'date',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];
	
	public function driver(){
		return $this->hasOne('App\Drivers', 'id', 'driver_id');
	}

    public function shift(){
        return $this->hasOne('App\Shifts', 'driver_id', 'driver_id')
                    ->whereNull('Shifts.end')
                    ->orderBy('Shifts.start', 'DESC');
    }
	
	public function setUpdatedAt($value){
		
	}
	
	public function getDates(){
		return ['date', 'start', 'endOffline', 'offlineEnd', 'offlineStart'];
	}

    

    public function scopeOffline($query)
    {
        return $query->addSelect('DriversStatus.*')
                    ->rightJoin(\DB::raw('(SELECT driver_id, MAX(id) as id FROM DriversStatus GROUP BY driver_id) d2'), function($join){
                        $join->on('d2.driver_id', '=', 'DriversStatus.driver_id');
                        $join->on('d2.id','=', 'DriversStatus.id');
                    })
                    ->where('DriversStatus.status', '=', 'Offline');
    }

    public function scopeShifts($query)
    {
        return $query->select('DriversStatus.*')
                    ->withShiftStart()
                    ->withTotal()
                    ->where('DriversStatus.status', '=', 'shiftEnd');
    }

    public function scopeWithTotal($query)
    {
        return $query->addSelect(\DB::raw('SUM(total) as total'))
                            ->join('Trips', function($join)
                            {
                                $join->on('Trips.driver_id', '=', 'DriversStatus.driver_id');
                                $join->on('Trips.date','>', 'd3.date');
                                $join->on('Trips.date','<', 'DriversStatus.date');
                            })->groupBy('Trips.driver_id', 'DriversStatus.date', 'd3.date');
                    }

	public function scopeWithShiftStart($query)
	{
		return $query->addSelect('d3.date as start')
					->join('DriversStatus as d3', function($join){
						$join->on('DriversStatus.driver_id', '=', 'd3.driver_id');
						$join->on('DriversStatus.date','>', 'd3.date');
						$join->on('d3.status','=', \DB::raw("'shiftStart'"));
					})
					->leftJoin('DriversStatus as d4', function($join)
					{
						$join->on('d4.driver_id', '=', 'd3.driver_id')
                            ->where('d4.status', 'shiftStart');
						$join->on('d4.date','>', 'd3.date');
						$join->on('d4.date','<', 'DriversStatus.date');
					})
                    ->whereNull('d4.driver_id')
					->whereNotNull('d3.driver_id');
	}

    public function scopeWithNextStatusDate($query)
    {
        return $query->addSelect('Next.date as offlineEnd', 'Next.latitude as latitudeEnd', 'Next.longitude as longitudeEnd')
            ->join('DriversStatus as Next', function($join){
                $join->on('DriversStatus.driver_id', '=', 'Next.driver_id');
                $join->on('DriversStatus.date','<', 'Next.date');
            })
            ->leftJoin('DriversStatus as NextSub', function($join)
            {
                $join->on('DriversStatus.driver_id', '=', 'NextSub.driver_id');
                $join->on('NextSub.date','>', 'DriversStatus.date');
                $join->on('NextSub.date','<', 'Next.date');
            })
            ->whereNull('NextSub.driver_id');
    }

    public function scopeWithPreviousStatusDate($query)
    {
        return $query->addSelect('Previous.date as offlineStart', 'Previous.latitude as latitudeStart', 'Previous.longitude as longitudeStart')
            ->join('DriversStatus as Previous', function($join){
                $join->on('DriversStatus.driver_id', '=', 'Previous.driver_id');
                $join->on('DriversStatus.date','>', 'Previous.date');
            })
            ->leftJoin('DriversStatus as PreviousSub', function($join)
            {
                $join->on('DriversStatus.driver_id', '=', 'PreviousSub.driver_id');
                $join->on('PreviousSub.date','<', 'DriversStatus.date');
                $join->on('PreviousSub.date','>', 'Previous.date');
            })
            ->whereNull('PreviousSub.driver_id');
    }

    public function getOfflineShiftStart(){
        $offline  = DriversStatus::with('driver')
            ->select(
                        'DriversStatus.id',
                        'DriversStatus.driver_id',
                        'DriversStatus.date',
                        'DriversStatus.status',
                        'd3.date as start',
                        'DriversStatus.'
            )
            ->leftJoin('DriversStatus as d2', function($join)
            {
                $join->on('DriversStatus.driver_id', '=', 'd2.driver_id');
                $join->on('DriversStatus.date','<', 'd2.date');
            })
            ->leftJoin('DriversStatus as d3', function($join)
            {
                $join->on('DriversStatus.driver_id', '=', 'd3.driver_id');
                $join->on('DriversStatus.date','>', 'd3.date');
                $join->on('d3.status','=', DB::raw("'shiftStart'"));
            })
            ->leftJoin('DriversStatus as d4', function($join)
            {
                $join->on('d4.driver_id', '=', 'd3.driver_id');
                $join->on('d4.date','>', 'd3.date');
                $join->on('d4.date','<', 'DriversStatus.date');
                $join->on('d4.status','=', DB::raw("'shiftStart'"));
            })
            ->where('DriversStatus.status', 'Offline')
            ->whereNull('d2.driver_id')
            ->whereNull('d4.driver_id')
            ->whereNotNull('d3.driver_id')
            ->get();
    }

    public static function getOfflineTimeReport(DriversStatus $status)
    {
        return self::select(
                        'DriversStatus.date',
                        \DB::raw('TIMESTAMPDIFF(SECOND, Previous.date, Next.date) as offlineDuration')
                    )
                    ->withShiftStart()
                    ->withNextStatusDate()
                    ->withPreviousStatusDate()
                    ->where('DriversStatus.status', 'Offline')
                    ->where('DriversStatus.driver_id', $status->driver_id)
                    ->where('DriversStatus.date', '<', $status->date)
                    ->where('DriversStatus.date', '>', $status->start)
                    ->get();
    }
    
    public static function createStatusForLateStartOrCloseShift($driver_id, $date){
        return self::create([
            'driver_id' => $driver_id,
            'latitude' => '53.923673',
            'longitude' => '27.567913',
            'course' => null,
            'status' => 'shiftStart',
            'date' => $date,
        ]);
    }

    public static function getAllDropOrder($driver_id, $start, $end){
        $checkNextStatus = False;
        $dropOrderStatusses = [];
        $dispatchedStatus = null;

        if($end == null){
            $end = Carbon::now();
        }

        $statuses = DriversStatus::where('driver_id',$driver_id)
            ->where('date','>',$start)
            ->where('date','<',$end)
            ->orderBy('id', 'ASC')
            ->get();
        
        foreach ($statuses as $status){
            if ($checkNextStatus
                && $status->status != 'Accepted'
                && $status->status != 'Arrived'
                && $status->status != 'DrivingClient')
            {
                $dropOrderStatusses[] = [
                    'id'            => $status->id,
                    'startStatus'   => $dispatchedStatus->date,
                    'endStatus'     => $status->date,
                    'status'        => $status->status,
                    'latitude'      => $status->latitude,
                    'longitude'     => $status->longitude,
                ];
            }
            $checkNextStatus = False;
            if($status->status == 'Dispatched'){
                $checkNextStatus = True;
                $dispatchedStatus = $status;
            }
        }
        return $dropOrderStatusses;
    }

    public static function getAllStatusDistance($driver_id, $start, $end){

        if($end == null){
            $end = Carbon::now();
        }

        $statuses = DriversStatus::where('driver_id',$driver_id)
            ->where('date','>',$start)
            ->where('date','<',$end)
            ->orderBy('id', 'ASC')
            ->get();
        $lastStatus = NULL;
        $response = [
            'Open'          => 0,
            'Dispatched'    => 0,
            'Accepted'      => 0,
            'Arrived'       => 0,
            'DrivingClient' => 0,
            'Offline'       => 0
        ];
        foreach ($statuses as $status){
            if($lastStatus !== NUll){
                $distance = Geo::distance($lastStatus, $status, 2);

                if (!is_nan($distance)) {
                    switch ($lastStatus->status) {
                        case 'Open':
                            $response[$lastStatus->status] += $distance;
                            break;
                        case 'Dispatched':
                            $response[$lastStatus->status] += $distance;
                            break;
                        case 'Accepted':
                            $response[$lastStatus->status] += $distance;
                            break;
                        case 'Arrived':
                            $response[$lastStatus->status] += $distance;
                            break;
                        case 'DrivingClient':
                            $response[$lastStatus->status] += $distance;
                            break;
                        case 'Offline':
                            $response[$lastStatus->status] += $distance;
                            break;
                        default:
                            break;
                    }
                }
            }
            $lastStatus = $status;
        }
        return $response;
    }
}