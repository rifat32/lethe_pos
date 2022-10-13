<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DoctorCommission extends Model
{
    protected $table = "doctor_commissions";
    protected $fillable = [
        'doctor_id',
        'service_id',
        "doctor_commission"
      ];
    public function doctor(){
        return $this->hasOne(Doctor::class,"id","doctor_id");
    }
    public function service(){
        return $this->hasOne(Product::class,"id","service_id");
    }

}
