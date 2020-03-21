<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
	protected $appends = ['editable'];
    protected $fillable = ['c', 'm', 'y', 'k', 'pantone', 'urgent', 'deliver', 'address', 'comment'];

    public function getEditableAttribute()
	{
		return !Auth::user()->hasRole('client') ||
				(Auth::user()->hasRole('client') && $this->attributes['status_id'] == '1' && $this->attributes['user_id'] == Auth::user()->id) ? 1 : 0;
	}

    public function plate()
    {
        return $this->belongsTo('App\Plate', 'plate_id');
    }

    public function storage()
    {
        return $this->belongsTo('App\Storage', 'storage_id');
    }

    public function payment()
    {
        return $this->belongsTo('App\Payment', 'payment_id');
    }

    public function status()
    {
        return $this->belongsTo('App\Status', 'status_id');
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
