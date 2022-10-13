<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EcomCustommers extends Model
{
    protected $table = "customers";
    protected $connection = "mysql2";
    protected $fillable = [
        'user_id',
      ];
      public function user(){
          return $this->belongsTo(EcomUsers::class,"user_id","id");
      }
}
