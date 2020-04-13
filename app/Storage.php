<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    protected $fillable = ['name', 'quantity', 'global_quantity_before', 'global_quantity_after', 'local_quantity_before', 'local_quantity_after', 'price', 'comment', 'used_storage_id', 'order_id', 'plate_id', 'user_id', 'manager_id'];

    public function order()
    {
        return $this->hasOne('App\Order', 'storage_id');
    }

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