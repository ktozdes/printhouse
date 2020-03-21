<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    protected $fillable = ['name', 'quantity','quantity_before', 'quantity_after', 'price', 'plate_id', 'user_id', 'manager_id'];


    public function plate()
    {
        return $this->belongsTo('App\Plate', 'plate_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function manager()
    {
        return $this->belongsTo('App\User', 'manager_id');
    }
}
