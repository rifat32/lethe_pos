<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    //
    protected $table = "order_details";
    protected $connection = "mysql2";
    public function order()
    {
        return $this->belongsTo(Order2::class);
    }

    public function product()
    {
        return $this->belongsTo(Variation::class,"variation_id","id");
    }

}
