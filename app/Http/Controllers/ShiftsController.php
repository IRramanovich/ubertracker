<?php

namespace App\Http\Controllers;

use App\Cars;
use App\DriversAllStatuses;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\DriversStatus;
use App\DriversAllStatus;
use App\Shifts;
use Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Debugbar;
use App\Lib\WialonReport;
use Excel;
use App\Lib\ShiftsControllerTrait;



class ShiftsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request['from'] != null && $request['to'] != null) {
            $timeFromArray = explode("-", $request['from']);
            $timeToArray = explode("-", $request['to']);

            $timeFrom =   $timeFromArray[2] .'-'. $timeFromArray[1] .'-'. $timeFromArray[0] . " 00:00:00";

            $timeTo =   $timeToArray[2] .'-'. $timeToArray[1] .'-'. $timeToArray[0] . " 00:00:00";
            $dateTime = new \DateTime($timeTo);
            $dateTime->add(new \DateInterval('P1D'));
            $timeTo = $dateTime->format('Y-m-d 00:00:00');

            $view_timeFrom = $timeFromArray[0] .'-'. $timeFromArray[1] .'-'. $timeFromArray[2];
            $view_timeTo = $timeToArray[0] .'-'. $timeToArray[1] .'-'. $timeToArray[2];
        }else{
            $dateTime = new \DateTime();
            $view_timeTo = $dateTime->format('d-m-Y');
            $dateTime->add(new \DateInterval('P1D'));
            $timeTo = $dateTime->format('Y-m-d 00:00:00');
            
            $dateTime->sub(new \DateInterval('P6D'));
            $timeFrom = $dateTime->format('Y-m-d 00:00:00');
            $view_timeFrom = $dateTime->format('d-m-Y');
        }
        
        $shifts = Shifts::with('driver')
            ->where('start','>=',$timeFrom)
            ->where('end','<=',$timeTo)
            ->whereNotNull('end')
            ->limit(100)
            ->orderBy('start', 'DESC')
            ->get();

        $cars = Shifts::with('driver')
            ->withLastStatus()
            ->with('online')
            ->whereNull('end')
            ->limit(100)
            ->orderBy('DriversLastStatus.date', 'DESC')
            ->get()
            ->keyBy('driver_id')
            ->toJson();

//        foreach ($shifts as $shift) {
//            Debugbar::info($shift);
//        }

        return Response::view('admin.shifts', 
            [
                'shifts'    => $shifts,
                'cars'      => $cars,
                'timeFrom'  => $view_timeFrom,
                'timeTo'    => $view_timeTo,
                'server'          => $_SERVER['SERVER_ADDR']
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  Shifts  $shift
     * @return \Illuminate\Http\Response
     */
    public function show(Shifts $shift)
    {
        $data =  ShiftsControllerTrait::getData($shift);
        Debugbar::info($data);
        return Response::view('admin.newshift-single', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function shiftreport(Request $request)
    {
        $timeFromArray = explode("-", $request['from']);
        $timeToArray = explode("-", $request['to']);
        $timeFrom =   $timeFromArray[2] .'-'. $timeFromArray[1] .'-'. $timeFromArray[0] . " 00:00:00";
        $timeTo =   $timeToArray[2] .'-'. $timeToArray[1] .'-'. $timeToArray[0] . " 00:00:00";
        $data = DriversStatus::with('driver')
            ->where('date','>',$timeFrom)
            ->where('date','<',$timeTo)
            ->limit(15500)
            ->get();
        $resultData = [];
        foreach ($data as $item){
            $resultData[] = [
                'DriverName' => $item->driver->getName(),
                'Latitude' => $item['latitude'],
                'Longitude' => $item['longitude'],
                'Status' => $item['status'],
                'Date' => $item['date'],
            ];
        }
        $name = substr($timeFrom, 0, 10) . ' ' . substr($timeTo, 0, 10);
        Excel::create($name, function ($excel) use ($resultData,$timeTo) {
            $excel->setTitle($timeTo);
            $excel->sheet('Sheet 1', function ($sheet) use ($resultData,$timeTo) {
                $sheet->setOrientation('landscape');
                $sheet->fromArray($resultData);
            });
        })->export('xls');
    }
}
