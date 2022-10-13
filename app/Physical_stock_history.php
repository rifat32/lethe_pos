<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Physical_stock_history extends Model
{
    public function products()
    {
        return $this->belongsTo('App\Product','product_id','id');
    }

    public function users()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }
}
