<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Trips extends Model
{
    protected $table = 'Trips';

    protected $dates = [
        'date',
        'begin_trip_at'
    ];

    public $timestamps = false;

    protected $fillable = ['id', 'driver_id', 'duration', 'distance', 'total', 'begin_trip_at', 'date', 'shift_id'];

    public static function saveFromUberData($trips, $shift)
    {
        foreach($trips as $trip) {
            $tripModel = self::firstOrNew(
                [
                    'id' => $trip->trip_id
                ]
            );

            $tripModel->fill(['driver_id' => $shift->driver_id,
                'shift_id' => $shift->id,
                'duration' => $trip->duration,
                'distance' => $trip->distance,
                'total' => $trip->total,
                'begin_trip_at' => Carbon::parse($trip->begintrip_at)->timezone('Europe/Minsk'),
                'date' => Carbon::parse($trip->date)->timezone('Europe/Minsk'),
                'surge' => (property_exists($trip, 'surge'))? $trip->surge: 0,
            ])->save();
        }
    }
}
