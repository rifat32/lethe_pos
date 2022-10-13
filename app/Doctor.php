<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table = "doctors";
    protected $fillable = [
        'user_id',
    ];

    public function sells(){
        return $this->hasMany(Transaction::class,"doctor_id","id");
    }
    public function payments(){
        return $this->hasMany(DoctorPayment::class,"doctor_id","id");
    }
    public function commissions(){
        return $this->hasMany(DoctorCommission::class,"doctor_id","id");
    }


}
