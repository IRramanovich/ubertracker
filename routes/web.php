<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/admin', 'AdminController@index')->name('admin')->middleware('auth');
//Route::get('/admin/shifts/{shifts}', 'AdminController@shifts')->name('admin.shifts')->middleware('auth');

Route::model('shifts', 'Shifts');
Route::model('newshifts', 'Shifts');
Route::group(array('prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth'), function(){
    Route::resource('','AdminController');
    Route::resource('shifts','ShiftsController');
    Route::get('status','StatusController@index')->name('status');
    Route::get('logs','LogController@index')->name('logs');
    Route::post('closeshift', 'StatusController@closeShift')->name('closeshift');


    Route::resource('cars','CarsController');
    Route::resource('newshifts','NewShiftsController');
});


Route::get('updatestatus','StatusController@getNewSatusJson')->name('updatestatus');
Route::get('test','StatusController@getTest')->name('test');

Route::get('/admin/surge', 'AdminController@surge')->name('admin.surge');
Route::get('/admin/logout', 'AdminController@logout')->name('admin.logout');

Route::post('admin/surge/save', array('before' => 'csrf', 'uses' => 'AdminController@saveSurge'))->name('admin.surge.save');


Route::get('/report', 'AdminController@report')->name('report');
Route::get('/shiftreport', 'ShiftsController@report')->name('shiftreport');

//update table by date
Route::get('/update', 'AdminController@update')->name('update');
Route::get('/admin/driver', 'AdminController@driver')->name('driver');

Route::get('/parse', 'AdminController@parse')->name('parse');
Auth::routes();

Route::get('/home', 'HomeController@index');

Route::get('/', function () {
    return redirect()->route('admin.status');
});
