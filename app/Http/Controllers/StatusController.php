<?php

namespace App\Http\Controllers;

use App\Lib\UberRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Shifts;
use App\Lib\UberNotification;
use App\DriversStatus;
use App\Offline;
use App\Cars;
use Response;
use App\DriversAllStatuses;
use \App\Lib\Geo;
use App\Lib\ShifControllLibrary;
use Debugbar;
use SlackApi;
use App\Drivers;
//        'SlackChannel'
use SlackChat;
//        'SlackGroup'
//        'SlackFile'
//        'SlackSearch'
//        'SlackInstantMessage'
use SlackUser;
//        'SlackStar'
//        'SlackUserAdmin'
//        'SlackRealTimeMessage'
//        'SlackTeam'
//use Monolog\Logger;
//use Monolog\Handler\StreamHandler;
//use Monolog\Handler\FirePHPHandler;
use App\Service\UberShiftLog;
use App\Classes\Socket\ChatSocket;
use Htmldom;
use App\Lib\Wialon;
use Excel;
use App\Lib\WialonReport;



class StatusController extends Controller
{
    public function index(){
        $cars = Shifts::with('driver')
            ->withLastStatus()
            ->with('online')
            ->whereNull('end')
            ->orderBy('DriversLastStatus.date', 'DESC')
            ->get()
            ->keyBy('driver_id')
            ->toJson();

        $server = $_SERVER['SERVER_ADDR'];

        return Response::view('admin.statuses', ['shifts' => '$shifts', 'cars' => $cars, 'server' => $server])
                        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }

    public function closeShift(Request $request){
        $shift = Shifts::where('id', $request->id)
            ->with('lastChangeStatus')
            ->first();
        $shift->closeShift();
        return redirect()->route('admin.status');
    }

    public function getNewSatusJson(Request $request){
        $shifts = Shifts::with('driver')
            ->with('car')
            ->withLastStatus()
            ->with('lastChangeStatus')
            ->with('offlinesSurge')
            ->with('offlinesNotSurge')
            ->orderBy('DriversLastStatus.date', 'DESC')
            ->whereNull('end')
            ->get();
        $shifts->load('newShiftsData');


        foreach ($shifts as $shift){
            $shift->tripsCount = $shift->newShiftsData->count('trips');
        }
        
        $shifts = $shifts->groupBy(function ($item, $key) {
            if ($item->lastChangeStatus) {
                return ($item->lastChangeStatus->status == 'Offline' || $item->lastChangeStatus->status == 'shiftEnd') ? 'offline' : 'online';
            }
        });

        if(isset($shifts['online'])){
            $shifts['online'] = $shifts['online']->sortBy(function ($shift, $key) {
                return $shift->driver->firstname;
            });
        }

        return Response::json(['shifts'=>$shifts]);
    }
    
    public function getTest(Request $request){

//        $shift = Shifts::where('id', 3884)
//            ->first();
//        $shiftData = unserialize($shift->info);
////        $shiftDataArray = json_encode($shiftData);
//
//
//
//        $name = $shiftData['driver'];
//        $array = [];
//        foreach($shiftData['trips'] as $trip){
////            Debugbar::info($trip);
//            $array[] = [
//                'status' => $trip->status,
//                'distance' => $trip->distance,
//                'uber_fee' => $trip->uber_fee,
//                'total_earned' => $trip->total_earned,
//                'fare' => $trip->fare,
//                'begintrip_at' => $trip->begintrip_at,
//                'duration' => $trip->duration,
//                'total' => $trip->total
//            ];
//        }
//
//
//        Excel::create($name, function ($excel) use ($array) {
//            $excel->setTitle('trips');
//            $excel->sheet('Sheet 1', function ($sheet) use ($array) {
//                $sheet->setOrientation('landscape');
//                $sheet->fromArray($array);
//            });
//        })->export('xls');

//        $report = new WialonReport();
//        $resp = $report->getLastMessage();

        $shifts = Shifts::whereNull('info')
            ->whereNull('end')
            ->where('car_id', 20)
            ->with('lastChangeStatus')
            ->get();

        return $shifts;
    }
}
