<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlateUser extends Model
{
	protected $table = 'plate_user';
    protected $fillable = ['user_id', 'price', 'plate_id'];
}
