<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    
    public function show(Request $request) 
    {
        $user = $request->user();
        $user->load('driver');
        return $user;
    }

    public function update(Request $request)
    {
        $request->validate([
            'year' => 'required|numeric|between:2010,2024',
            'make' => 'required',
            'model' => 'required',
            'color' => 'required|alpha',
            'licence_plate' => 'required',
            'name' => 'required'
        ]);

        $user = $request->user();
        $user->update($request->only('name'));

        // create or update a driver associated with this user
        $user->driver()->updateOrCreate($request->only([
            'year',
            'make',
            'model',
            'color',
            'licence_plate'

        ]));
        $user->load('driver');
        return $user;
    }
}
