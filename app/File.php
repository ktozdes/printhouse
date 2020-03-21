<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['name', 'old_name', 'pages', 'url', 'size', 'width', 'height'];
    protected $hidden = ['filable_id', 'filable_type'];
}
