<?php

namespace App\Http\Controllers;

use App\Cars;
use App\Offline;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\DriversStatus;
use App\DriversAllStatus;
use App\Shifts;
use Response;
use Debugbar;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use App\Lib\WialonReport;
use App\Lib\ShiftsControllerTrait;


class NewShiftsController extends Controller
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
            ->where('end','<',$timeTo)
            ->with('car')
            ->whereNotNull('end')
            ->orderBy('start', 'DESC')
            ->limit(500)
            ->get();

        $shifts->load('newShiftsData');
        $data = Shifts::getNewShiftData($shifts);
        return Response::view('admin.newshifts', 
            [
                'shifts' => $data,
                'timeFrom'  => $view_timeFrom,
                'timeTo'    => $view_timeTo
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
        $shift = Shifts::where('id',$request['id'])->first();
        $cars = Cars::getIdAndNumber();
        $report = new WialonReport();
        $offlineTrack = $report->getOfflineTrack($shift, $cars);

        $mergeTrack = WialonReport::mergeWialonAndUberData($shift, $offlineTrack);
        $track = '';
        if (count($mergeTrack) != 0) {
            $shift_offlines = Offline::getShiftOffline($shift->id);
            $track = json_encode($mergeTrack);
        } else {
            $track = $shift->singleStatuses->toJson();
        }
        return $track;
    }

    /**
     * Display the specified resource.
     *
     * @param  Shifts  $shift
     * @return \Illuminate\Http\Response
     */
    public function show(Shifts $newshift)
    {
        $data = ShiftsControllerTrait::getData($newshift);
        foreach ($data as $key => $value) {
            Debugbar::info($key);
        }
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
        $logger = new Logger('Logger1');
        // Now add some handlers
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../../storage/logs/newShift.log', Logger::DEBUG));
        $logger->pushHandler(new FirePHPHandler());
        // You can now use your logge
        if ($id){
            $shift = Shifts::where('id',$id)->first();
        }
        $logger->addInfo('start');
        switch($request->type){
            case 'counted':
                $logger->addInfo($shift->id);
                $shift->preliminarilyCountShift();
                break;
            case 'shiftData':
                $this->validate($request, [
                    'mileage_start' => 'required|max:8',
                    'mileage_end'   => 'required|max:8',
                    'fuel_start'    => 'required|max:8',
                    'refill'        => 'required|max:8',
                    'fuel_end'      => 'required|max:8',
                    'gas_refill'    => 'required|max:8',
                ]);
                $shift->updateFuelData($request);
                break;
            case 'interval':
                
                
                break;
        }
        return redirect()->route('admin.newshifts.show', $id);
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

    public function getTrack(Request $request)
    {

        return Response::view('admin.logs');
    }

}
