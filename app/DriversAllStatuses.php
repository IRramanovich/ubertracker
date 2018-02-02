<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DriversAllStatuses extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'DriversAllStatuses';
	
	protected $guarded = ['date'];
	
	public $timestamps = false;
	
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

    public static function createOfflinePoint($driver_id, $shift_id){
        return self::create(
            [
                'driver_id' => $driver_id,
                'latitude' => '53.923673',
                'longitude' => '27.567913',
                'course' => null,
                'status' => 'Offline',
                'shift_id' => $shift_id,
            ]
        );
    }
}