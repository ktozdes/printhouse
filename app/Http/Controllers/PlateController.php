<?php

namespace App\Http\Controllers;

use App\Plate;
use App\Storage;
use Illuminate\Http\Request;

class PlateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $plates =  ($request->user->hasRole('super-admin')) 
            ? Plate::all()
            : Plate::all('id', 'name', 'specification', 'producer', 'width', 'height', 'thickness', 'measurement_unit');
        
        return response()->json([
            'plates' => $plates
        ]);
        //return response()->json('plates' =>$plates]);
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
        $request->validate([
            'plate.name'=>'required'
        ]);

        $plate = new Plate($request->plate);
        $plate->save();

        if (isset($request->storage['quantity']) && $request->storage['quantity'] > 0) {
            $storage = new Storage($request->storage);
            $storage->manager_id = $request->user->id;
            $storage->plate_id = $plate->id;
            $storage->save();
        }
        
        return response()->json([
            'status' => 'success',
            'plate' => $plate,
            'storage' => $storage,
            'message' => 'Заказ Создан',
        ]);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Plate  $plate
     * @return \Illuminate\Http\Response
     */
    public function show(Plate $plate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Plate  $plate
     * @return \Illuminate\Http\Response
     */
    public function edit(Plate $plate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Plate  $plate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Plate $plate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Plate  $plate
     * @return \Illuminate\Http\Response
     */
    public function destroy(Plate $plate)
    {
        //
    }
}
