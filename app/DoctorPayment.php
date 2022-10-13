<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DoctorPayment extends Model
{
    protected $table = "doctor_payments";
    protected $fillable = [
        'doctor_id',
        'payment_amount',
      ];


}
