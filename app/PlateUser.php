<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlateUser extends Model
{
	protected $table = 'plate_user';
    protected $fillable = ['user_id', 'price', 'plate_id'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    public function plate()
    {
        return $this->belongsTo('App\Plate', 'plate_id');
    }
}
