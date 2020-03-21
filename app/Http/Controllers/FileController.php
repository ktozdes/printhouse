<?php

namespace App\Http\Controllers;

use App\File;
use App\Order;
use App\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Carbon\Carbon;
use App\Events\PaymentWasCreated;

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

    public function upload(Request $request)
    {
        $file = $request->file('file');
 
        if (!is_dir( public_path($this->uploadsPath)) ) {
            mkdir(public_path($this->uploadsPath), 0777);
        }
        $dt = Carbon::now();
        $name  =$dt->format('Y-m-d-h-i') . '_'  . $request->user->name . '.' . $file->getClientOriginalExtension();
        $res = Storage::disk('public_path')->put( $this->uploadsPath . '/' . $name , file_get_contents($file) );
        $fileMetaData = [];
        if ( $res ) {
            //Storage::disk('public_path')->url($this->uploadsPath . '/' . $name);
            $fullPath = Storage::disk('public_path')->getDriver()->getAdapter()->getPathPrefix() . $this->uploadsPath . '/' . $name ;
            $fileMetaData = $this->getPDFInfo( $fullPath );
            //print_r($fileMetaData);

            $filable = new File();
            $filable->name = $name;
            $filable->old_name = $request->file('file')->getClientOriginalName();
            $filable->url = Storage::disk('public_path')->url($this->uploadsPath . '/' . $name);
            $filable->filable_id = $request->user->id;
            $filable->filable_type = 'App\User';
            $filable->pages = $fileMetaData['pages'];
            $filable->width = $fileMetaData['width'];
            $filable->height = $fileMetaData['height'];
            $filable->size = $fileMetaData['size'];

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
        // return response()->json([
        //     'status' => 'success',
        //     'message' => 'PDF файл удален.'
        // ]);
        $request->validate([
            'id'=>'required|integer'
        ]);
        $file = new File();
        if (isset($request->orderID) && is_numeric($request->orderID)){
            $order = Order::where([
                ['id', '=', $request->orderID],
                ['user_id', '=', $request->user->id],
            ])->first();

            if (empty($order)) {
                return response()->json(['status' => 'error', 'message' => 'Заказ не найден или вы не создавали этот заказ'], 400);
            }
            
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

    private function getPDFInfo($file)
    {
        $pdfinfoPath = config('app.pdfinfo');
        $output = [];
        $data = [];
        exec("$pdfinfoPath $file", $output);
        if (file_exists($file)){
            foreach($output as $op) {
                if(preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1)
                {
                    $data['pages'] = intval($matches[1]);
                }
                else if(preg_match("/File size:\s*(\d+)/i", $op, $matches) === 1)
                {
                    $data['size'] = intval($matches[1]) * 0.000001;
                }
                else if(strpos($op, "Page size:") === 0) {
                    $dimensions = explode('x', preg_replace("/[^0-9x\.]/", "", explode('(', $op)[0]));
                    $data['width'] = trim($dimensions[0]);
                    $data['height'] = trim($dimensions[1]);
                } 
            }
        }
        return $data;
    }
}
