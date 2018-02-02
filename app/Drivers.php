<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Drivers extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Drivers';
	
	public $timestamps = false;
	
	public $incrementing = false;

    protected $fillable = ['id', 'firstname', 'lastname'];

	protected $appends = array('name');

    public function getNameAttribute(){
        return $this->getName();
    }
	
	public function getName(){
		return ($this->firstname && $this->lastname)? $this->firstname . ' ' . $this->lastname: $this->id;
	}
	
}