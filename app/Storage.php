<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    protected $fillable = ['quantity', 'price', 'plate_id', 'manager_id'];


    public function plate()
    {
        return $this->belongsTo('App\Plate', 'plate_id');
    }

    public function manager()
    {
        return $this->belongsTo('App\User', 'manager_id');
    }
}
