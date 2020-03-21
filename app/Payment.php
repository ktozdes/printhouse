<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['name', 'amount', 'balance_before', 'balance_after', 'comment', 'user_id', 'manager_id'];

    public function payer()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function manager()
    {
        return $this->belongsTo('App\User', 'manager_id');
    }
}
