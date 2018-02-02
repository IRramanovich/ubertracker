<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Response;
use App\Drivers;
use Debugbar;


class LogController extends Controller
{
    public function index(){

        $filelist = [];
        $files = [];
        if($handle_dir = opendir(__DIR__.'/../../../storage/logs/')){
            while ($entry = readdir($handle_dir)){
                if(strpos($entry, 'log')){
                    $filelist[] = $entry;
                    $handle_file = fopen(__DIR__.'/../../../storage/logs/'.$entry, "r");
                    $arr = [];
                    while(($buffer = fgets($handle_file, 2024)) != false){

                        $temp = explode(' ', $buffer);
                        $data = '';
                        if(count($temp) > 4){
                            for($i = 3; $i<count($temp)-2; $i++) {
                                $data = $data.' '.$temp[$i];
                            }
                            $data_json = json_decode($data);

//                            Debugbar::info($data_json);
//
                            if(isset($data_json->responce)){
                                $responce = $data_json->responce;
                                if(isset($responce->driver_id)){
                                    $driver = Drivers::where('id', $responce->driver_id)->first();
                                    $data_json->responce->driver_id = $driver->lastname;
                                    $arr[] = [
                                        'date' => $temp[0].' '.$temp[1],
                                        'data' => $data_json,
                                        'responce' => $responce,
                                    ];
                                }

//                                var_dump($responce);

                            }

                        }

                    }
                    $files[$entry] = $arr;
                    fclose($handle_file);

                }
            }
            closedir($handle_dir);
        }
        Debugbar::info($files);


        return Response::view('admin.logs', ['files_list'=>$filelist, 'files'=>$files])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
//        return Response::json(['response'=>'']);
    }
}