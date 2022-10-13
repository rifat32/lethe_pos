<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB ;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    public function purchase_lines()
    {
        return $this->hasMany(\App\PurchaseLine::class);
    }

    public function sell_lines()
    {
        return $this->hasMany(\App\TransactionSellLine::class);
    }

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }
    public function doctor()
    {
        return $this->belongsTo(\App\Doctor::class, 'doctor_id');
    }
    public function assistant()
    {
        return $this->belongsTo(\App\Assistant::class, 'assistant_id');
    }


    public function payment_lines()
    {
        return $this->hasMany(\App\TransactionPayment::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\BusinessLocation::class, 'location_id');
    }

    public function business()
    {
        return $this->belongsTo(\App\Business::class, 'business_id');
    }

    public function tax()
    {
        return $this->belongsTo(\App\TaxRate::class, 'tax_id');
    }

    public function stock_adjustment_lines()
    {
        return $this->hasMany(\App\StockAdjustmentLine::class);
    }

    public function sales_person()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function return_parent()
    {
        return $this->hasOne(\App\Transaction::class, 'return_parent_id');
    }

    public function table()
    {
        return $this->belongsTo(\App\Restaurant\ResTable::class, 'res_table_id');
    }

    public function service_staff()
    {
        return $this->belongsTo(\App\User::class, 'res_waiter_id');
    }

    public function createUser()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }


    public function newpurchaselines()
    {
        return $this->hasMany(\App\PurchaseLine::class,'transaction_id')
                ->join('transaction_sell_lines_purchase_lines as tslpl','tslpl.purchase_line_id','=','purchase_lines.id')
                ->join('variations as v','v.id','=','purchase_lines.variation_id')
                ->select('purchase_lines.id','purchase_lines.transaction_id','purchase_lines.line_discount_amount','purchase_lines.item_tax',
                    DB::raw("SUM(tslpl.quantity) as sell_quantity"),
                    DB::raw("SUM(tslpl.qty_returned) as return_quantity"),
                    DB::raw("SUM(tslpl.qty_returned * v.sell_price_inc_tax) as return_price"),
                    DB::raw("SUM(tslpl.quantity * v.default_purchase_price) as purchase_price"),
                    DB::raw("SUM(tslpl.quantity * v.sell_price_inc_tax) as sell_price"),
                    DB::raw("SUM((tslpl.quantity - tslpl.qty_returned)* purchase_lines.purchase_price_inc_tax) as net_sell_price")
                )->groupBy('purchase_lines.id');
    }

}
