<?php

namespace App\Http\Controllers;

use App\Events\TripAccepted;
use App\Events\TripEnded;
use App\Events\TripLocationUpdated;
use App\Events\TripStarted;
use App\Models\Trip;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TripController extends Controller
{
    public function store(Request $request) 
    {
        // dd($request->all());
        $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'destination_name' => 'required'
        ]);

        return $request->user()->trips()->create($request->only([
            'origin',
            'destination',
            'destination_name'
        ]));
    }

    public function show(Request $request, Trip $trip)
    {
        // is the trip associated with the authenticated user.
        if($trip->user->id == $request->user()->id) {
            return $trip;
        }

        if($trip->driver && $request->user()->driver) {
            if($trip->driver->id == $request->user()->driver->id) {
                return $trip;
            }
        }

        return response()->json(['message' => 'Cannot find this trip.'], 404);
    }

    public function accept(Request $request, Trip $trip) 
    {
        // a driver has accepted a trip
        $request->validate([
            'driver_location' => 'required'
        ]);

        $trip->update([
            'driver_id' => $request->user()->id,
            'driver_location' => $request->driver_location
        ]);

        TripAccepted::dispatch($trip, $request->user());
        $trip->load('driver.user');

        return $trip;
    }
    
    public function start(Request $request, Trip $trip) 
    {
        //a driver has started driving the user to final destination
        $trip->update([
            'is_started' => true
        ]);

        TripStarted::dispatch($trip, $request->user());
        $trip->load('driver.user');
    }

    public function end(Request $request, Trip $trip) 
    {
        //the trip has ended
        $trip->update([
            'is_complete' => true
        ]);

        TripEnded::dispatch($trip, $request->user());
        $trip->load('driver.user');
    }

    public function location(Request $request, Trip $trip) 
    {
        //the current location of the driver
        $request->validate([
            'driver_location' => $request->driver_location
        ]);

        $trip->load('driver.user');
        TripLocationUpdated::dispatch($trip, $request->user());
        return $trip;
    }
}
