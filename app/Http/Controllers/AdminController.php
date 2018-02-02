<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\DriversStatus;
use App\Shifts;
use App\Facades\SurgeFacade;
use App\Facades\UberRequestFacade;
use Surge;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Debugbar;
use Excel;

class AdminController extends Controller
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

        $statuses = DriversStatus::with('driver')
            ->where('date', '>=', $timeFrom)
            ->where('date', '<=', $timeTo)
            ->limit(15500)
            ->get();

		return view('admin.index', [
            'statuses'  => $statuses,
            'timeFrom'  => $view_timeFrom,
            'timeTo'    => $view_timeTo
        ]);
    }

    public function update(Request $request)
    {
        $timeFromArray = explode("-", $request['from']);
        $timeToArray = explode("-", $request['to']);
        $timeFrom =   $timeFromArray[2] .'-'. $timeFromArray[1] .'-'. $timeFromArray[0] . " 00:00:00";
        $timeTo =   $timeToArray[2] .'-'. $timeToArray[1] .'-'. $timeToArray[0] . " 00:00:00";

        $statuses = DriversStatus::with('driver')
            ->where('date','>',$timeFrom)
            ->where('date','<',$timeTo)
            ->limit(15500)
            ->get();
        return view('admin.index', ['statuses' => $statuses]);
    }

    public function shifts()
    {
        //$shifts = Shifts::with('driver')->get();
        $shifts = Shifts::orderBy('start', 'DESC')->get();
        /*foreach($shifts as $shift) {
            Shifts::firstOrCreate(
                [
                    'driver_id' => $shift->driver_id,
                    'total' => $shift->total,
                    'start' => $shift->start,
                    'end' => $shift->date,
                ]
            );
        }
            die();*/
        $cars = DriversStatus::with('driver')->lastOnlineStatuses()->get()->keyBy('driver_id')->toJson();
        return view('admin.shifts', ['shifts' => $shifts, 'cars' => $cars]);
    }
	
	public function surge()
    {
        $surge = Storage::disk('local')->get('surge.txt');
		return view('admin.surge', ['surge' => $surge]);
    }

    public function saveSurge(Request $request)
    {
        Storage::disk('local')->put('surge.txt', $request->input('surge'));
        return redirect()->route('admin.surge');
    }

    public function report(Request $request)
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

    public function parse(Request $request)
    {
        $offline = DriversStatus::getLastNotOnlineStatuses();
        foreach($offline as $one){
            var_dump($one->driver_id);
            var_dump(DriversStatus::getOfflineTimeReport($one));
            die();
            var_dump('***************************************************');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
