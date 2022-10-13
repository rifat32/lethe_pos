<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $table = "uploads";
    protected $connection = "mysql2";


    public function user()
    {
    	return $this->belongsTo(User2::class,"user_id","id");
    }
}
