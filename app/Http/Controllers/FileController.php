<?php

namespace App\Http\Controllers;

use App\File;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Carbon\Carbon;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $uploadsPath;
 
    public function __construct()
    {
        $this->uploadsPath = '/uploads/pdf';
    }

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

    public function upload(Request $request)
    {
        $file = $request->file('file');
 
        if (!is_dir( public_path($this->uploadsPath)) ) {
            mkdir(public_path($this->uploadsPath), 0777);
        }
        $dt = Carbon::now();
        $name  =$dt->format('Y-m-d-h-i') . '_'  . $request->user->name . '_'  . Str::random(5) . '.' . $file->getClientOriginalExtension();
        $res = Storage::disk('public_path')->put( $this->uploadsPath . '/' . $name , file_get_contents($file) );
        if ( $res ) {
            $filable = new File();
            $filable->name = $name;
            $filable->src = $name;
            $filable->size = $this->filesize_formatted($request->file('file')->getClientSize() );
            $filable->filable_id = $request->user->id;
            $filable->filable_type = 'App\User';

            if (isset($request->orderID) && is_numeric($request->orderID)){
                $order = Order::where([
                    ['id', '=', $request->orderID],
                    ['user_id', '=', $request->user->id],
                ])->first();
                if (!empty($order)) {
                    $filable->filable_id = $order->id;
                    $filable->filable_type = 'App\Order';
                }
                
            }
            $filable->save();

            return response()->json([
                'status' => 'success',
                'message' => 'PDF файл загружен.',
                'file_id' => $filable->id,
                'file' => $filable,
            ]);
        }
        else {
            return response()->json(['status' => 'error', 'message' => 'Файл не загружен. Попробуйте снова.'], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, File $file)
    {
        
        $request->validate([
            'id'=>'required|integer'
        ]);
        $file = new File();
        if (isset($request->orderID) && is_numeric($request->orderID)){
            $order = Order::where([
                ['id', '=', $request->orderID],
                ['user_id', '=', $request->user->id],
            ])->first();
            
            $file = File::where([
                ['filable_id', '=', $order->id],
                ['filable_type', '=', 'App\Order'],
            ])->first();
        }
        else{
            $file = File::where([
                ['id', '=', $request->id],
                ['filable_id', '=', $request->user->id],
                ['filable_type', '=', 'App\User'],
            ])->first();
        }
 
        if (empty($file)) {
            return response()->json(['status' => 'error', 'message' => 'Файл не найден'], 400);
        }
 
        $file_path = public_path($this->uploadsPath) . '/' . $file->name;
 
        if (file_exists($file_path)) {
            unlink($file_path);
        }
 
        if (!empty($file)) {
            $file->delete();
        }

        return response()->json([
            'status' => 'success',
            'name'  =>$file->name,
            'message' => 'PDF файл удален.'
        ]);
    }

    private function filesize_formatted($size)
    {
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }
}
