<?php

namespace App\Listeners;

use App\Events\PlateQuantityChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Storage;
use App\Plate;

class UpdatePlateQuantity
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PlateQuantityChanged  $event
     * @return void
     */
    public function handle(PlateQuantityChanged $event)
    {
        Plate::find( $event->storage['plate_id'] )->increment('quantity', $event->storage['quantity']);
    }
}
