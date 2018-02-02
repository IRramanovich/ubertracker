<?php

namespace App\Http\Controllers;

use App\Cars;
use Illuminate\Http\Request;
use App\Http\Requests;
use Response;

class CarsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cars = Cars::getAllActiveCars();

        return Response::view('admin.cars', ['cars' => $cars, 'page' => 'index'])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $car = new Cars();
        $this->validate($request, [
            'gov_number' => 'required|max:10',
            'model' => 'required|max:50',
        ]);

        $car->addCar($request);

        return redirect()->route('admin.cars.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cars = Cars::getAllBlockCars();

        return Response::view('admin.cars', ['cars' => $cars, 'page' => 'store'])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }
 
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

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
     * Update field block_car in cars table
     *
     * @param int $id
     */
    public function block_car($id)
    {
        return Cars::blockCar($id);
    }
    /**
     * Update field block_car in cars table
     *
     * @param int $id
     */
    public function unblock_car($id)
    {
        return Cars::unblockCar($id);
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
        if(!isset($request->type)){
            return redirect()->route('admin.cars.index');
        }

        switch($request->type){
            case 'block_car':
                $result = $this->block_car($id);
                break;
            case 'unblock_car':
                $result = $this->unblock_car($id);
                break;
        }

        return redirect()->route('admin.cars.index');
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
}
