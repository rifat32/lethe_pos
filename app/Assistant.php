<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Assistant extends Model
{
    protected $table = "assistants";
  

    public function sells(){
        return $this->hasMany(Transaction::class,"assistant_id","id");
    }

}
