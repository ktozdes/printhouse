<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    protected $fillable = ['quantity', 'price', 'plate_id', 'manager_id'];
}
