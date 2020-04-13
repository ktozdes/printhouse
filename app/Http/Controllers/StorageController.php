<?php

namespace App\Http\Controllers;

use App\Storage;
use Illuminate\Http\Request;
use App\Events\PlateQuantityChanged;

class StorageController extends Controller
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
    public function addDefect(Request $request)
    {
        $request->validate([
            'storage.plate_id'=>'required|integer',
            'storage.quantity'=>'required|integer|gt:0',
        ]);
        
        event(new PlateQuantityChanged([
            'order_id' => null,
            'name' => 'defect',
            'comment' => $request->comment,
            'quantity' => $request->storage['quantity'],
            'plate_id' => $request->storage['plate_id'],
            'manager_id' => $request->user->id,
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Брак записан',
        ]);
        
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Storage  $storage
     * @return \Illuminate\Http\Response
     */
    public function show(Storage $storage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Storage  $storage
     * @return \Illuminate\Http\Response
     */
    public function edit(Storage $storage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Storage  $storage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Storage $storage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Storage  $storage
     * @return \Illuminate\Http\Response
     */
    public function destroy(Storage $storage)
    {
        //
    }
}
