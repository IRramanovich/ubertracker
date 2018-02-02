<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cars extends Model
{
    protected $table = 'Cars';

    public $timestamps = false;

    protected $fillable = ['id', 'car_gov_number', 'car_model', 'production_year', 'buy_date', 'car_bloked', 'car_code'];

    static public function getAllCars(){
        return Cars::All();
    }
 
    static public function getAllBlockCars(){
        return Cars::where('car_bloked', 1)->get();
    }
    
    static public function getAllActiveCars(){
        return Cars::where('car_bloked', 0)->get();
    }

    static public function getCarByNumber($number){
        $number = str_replace(' ','',$number);
        $number = str_replace('-','',$number);
        $number = str_replace('BY','',$number);
        $car_code = strtolower($number);
        $car = Cars::where('car_code',$car_code)->first();
        if (!is_null($car)) {
            return $car;
        }
        $car_code = str_replace('СА','ca',$car_code);
        $car_code = str_replace('РН','ph',$car_code);
        $car = Cars::where('car_code',$car_code)->first();
        return $car;
    }

    static public function getIdAndNumber(){
        $response = [];
        $cars = Cars::getAllActiveCars();
        foreach ($cars as $car){
            $response[$car->id] = $car->car_gov_number;
        }
        return $response;
    }

    public function addCar($request){
        $this->car_gov_number = $request->gov_number;
        $this->car_model = $request->model;
        $this->production_year = $request->year_productions;
        $this->buy_date = $request->buy_date;

        $this->save();

        Cars::updateCarCode();
    }

    static public function blockCar($id){
        Cars::where('id',$id)
            ->update(['car_bloked' => 1]);
    }
    
    static public function unblockCar($id){
        Cars::where('id',$id)
            ->update(['car_bloked' => 0]);
    }

    static public function updateCarCode(){
        $cars = Cars::All();
        $result = [];
        foreach ($cars as $car){
            $carCode = $car->car_gov_number;
            $first = substr($carCode, 0, 4);
            $last = substr($carCode, -1, 1);
            $middle = strtolower(substr($carCode, -4, 2));
            $carCode = $first.$middle.$last;
            $car->car_code = $carCode;
            array_push($result, $car->save());
        }
        return $result;
    }
}
