<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

// this is to avoid ddos attack
Route::middleware('throttle:20,1')->group(function () {


  Route::apiResource('events', EventController::class);

  Route::post('events/{event}/reserve', [ReservationController::class, 'reserve']);
  Route::put('events/{event}/reserve', [ReservationController::class, 'update']);
  Route::delete('events/{event}/reserve', [ReservationController::class, 'cancel']);

});

/**
 * 
 * Accept application/json
 * Content-Type application/json
 * 
 * 
 * 
 * 
 * 
 */