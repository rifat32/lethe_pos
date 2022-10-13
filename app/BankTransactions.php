<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class BankTransactions extends Model{

    use SoftDeletes;
    public $timestamps = true;
    protected $table = 'bank_transactions';
    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
}
