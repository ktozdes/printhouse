<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'balance', 'rank', 'trust', 'comment', 'comment', 'api_token'

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'api_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function rollApiKey(){
        do{
            $this->api_token = Str::random(60);
        }
        while($this->where('api_token', $this->api_token)->exists());
        $this->save();
    }
    public function plates()
    {
        return $this->belongsToMany('App\Plate');
    }
    public function pricing()
    {
        return $this->hasMany('App\PlateUser');
    }

}
