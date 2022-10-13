<?php

namespace App;
use App\User;
use App\Transaction_sell_line_update_delete_history;
use Illuminate\Database\Eloquent\Model;

class Transaction_update_delete_history extends Model
{
    protected $table = 'transaction_update_delete_histories';
    
    public function updatedBy()
    {
        return $this->belongsTo(User::class,'action_by','id');
    }
    
    public function updateSellLines()
    {
        return $this->hasMany(Transaction_sell_line_update_delete_history::class,'transaction_id','transaction_id');
    }
}
