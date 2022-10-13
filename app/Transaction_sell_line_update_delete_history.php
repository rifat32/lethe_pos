<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Product;
class Transaction_sell_line_update_delete_history extends Model
{
    protected $table = 'transaction_sell_line_update_delete_histories';
    
    public function products()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
