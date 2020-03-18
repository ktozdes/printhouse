<?php

namespace App\Http\Controllers;

use App\Plate;
use App\Storage;
use Illuminate\Http\Request;

use App\Events\PlateQuantityChanged;

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
            : Plate::all('id', 'name', 'price');
        
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
            $storage = new Storage([
                'price' =>$request->storage['price'],
                'quantity' =>$request->storage['quantity'],
                'manager_id' =>$request->user->id,
                'plate_id' =>$plate->id,
            ]);
            $storage->save();
            event(new PlateQuantityChanged([
                'price' =>$request->storage['price'],
                'quantity' =>$request->storage['quantity'],
                'manager_id' =>$request->user->id,
                'plate_id' =>$plate->id,
            ]));
        }
        
        return response()->json([
            'status' => 'success',
            'plate' => $plate,
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
    public function edit(Request $request, Plate $plate)
    {
        $request->validate([
            'id'=>'required|integer',
        ]);
        $plate = Plate::where([
            ['id', '=', $request->id],
        ])->first();

        if (empty($plate)) {
            return response()->json(['status' => 'error', 'message' => 'Эта пластину нельзя изменить или ее нет'], 403);
        }

        return response()->json([
            'status' => 'success',
            'plate' => $plate,
            'message' => 'Пластина найдена',
        ]);
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
        $request->validate([
            'plate.id'=>'required|integer',
            'plate.name'=>'required|string'
        ]);

        $plate = Plate::find($request->plate['id']);

        $plate->update($request->plate);

        if (isset($request->storage['quantity']) && $request->storage['quantity'] > 0) {
            $storage = new Storage([
                'price' =>$request->storage['price'],
                'quantity' =>$request->storage['quantity'],
                'manager_id' =>$request->user->id,
                'plate_id' =>$plate->id,
            ]);
            $storage->save();
            event(new PlateQuantityChanged([
                'price' =>$request->storage['price'],
                'quantity' =>$request->storage['quantity'],
                'manager_id' =>$request->user->id,
                'plate_id' =>$plate->id,
            ]));
        }

        return response()->json([
            'status' => 'success',
            'plate' => $plate,
            'message' => 'Пластина Изменена',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Plate  $plate
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id'=>'required|integer'
        ]);
        $result = Plate::destroy($request->id);
        return response()->json([
            'status' => 'success',
            'message' => 'Пластина Удалена.'
        ]);
    }
}
