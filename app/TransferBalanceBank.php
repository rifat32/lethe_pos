<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class TransferBalanceBank extends Model
{
    use SoftDeletes;
    protected $table = 'transfer_balance_bank';
    protected $dates = ['deleted_at'];
        /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
