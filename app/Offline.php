<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Surge;
use \App\Lib\Geo;

class Offline extends Model
{
    protected $table = 'Offline';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $dates = [
        'start',
        'end',
    ];

    public static function createOffline($shift, DriversStatus $status, DriversStatus $newStatus)
    {
        $intervals = Surge::isSurgeTimeOfflineInterval($status->date, Carbon::now());
        $distance = Geo::distance($status, $newStatus, 2);
        $offlineDuration = $newStatus->date->diffInSeconds($status->date);
        foreach($intervals as $interval){
            $intervalDuration = $interval['end']->diffInSeconds($interval['start']);
            self::create([
                'shift_id' => $shift->id,
                'start' => $interval['start'],
                'is_surge' => ( isset($interval['isSurge']) )? 1: 0,
                'end' => $interval['end'],
                'distance' => $distance * ($intervalDuration/$offlineDuration),
            ]);
        }
    }

    public static function getShiftOffline($shift_id) {
        return Offline::where('shift_id', $shift_id)->get();
    }
}
