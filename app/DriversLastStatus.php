<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DriversLastStatus extends Model
{
	const UPDATED_AT = 'date';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'DriversLastStatus';
	
	//public $timestamps = false;
	
	protected $dates = [
        'date',
    ];
	
	protected $guarded = ['id'];

	protected $hidden = ['id'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];
	
	public function driver(){
		return $this->hasOne('App\Drivers', 'id', 'driver_id');
	}
	
	public function setCreatedAt($value)
	{
		// Do nothing.
	}
}