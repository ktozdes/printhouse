<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['amount', 'comment', 'user_id', 'name', 'balance_before', 'balance_after', 'manager_id'];

    public function payer()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function manager()
    {
        return $this->belongsTo('App\User', 'manager_id');
    }
}
