<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
	protected $appends = ['editable'];
    protected $fillable = ['c', 'm', 'y', 'k', 'urgent', 'deliver', 'address', 'comment', 'status_id', 'user_id', 'plate_id'];


    public function getEditableAttribute()
	{
		return !Auth::user()->hasRole('client') ||
				(Auth::user()->hasRole('client') && $this->attributes['status_id'] == '1' && $this->attributes['user_id'] == Auth::user()->id) ? 1 : 0;
	}
}
