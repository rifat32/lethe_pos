<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order2 extends Model
{
    use SoftDeletes;
    //
    protected $table = "orders";
    protected $connection = "mysql2";

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class,'order_id','id');
    }
    public function user()
    {
        return $this->belongsTo(User2::class,"user_id","id");
    }
}
