<?php

namespace App\Http\Controllers;

use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Doctor;
use App\DoctorCommission;
use App\DoctorPayment;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\Unit;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    public function index()
    {
      $doctors = Doctor::

      paginate(10);

        return view('hospital.doctor.index',compact("doctors"));

    }
    public function getDoctorReport()
    {
      $doctors = Doctor::

      paginate(10);

        return view('hospital.doctor.index',compact("doctors"));

    }
    public function getDoctorSellReport(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
      $transactions = Transaction::
      
      where('transactions.business_id', $business_id)
    ->where('transactions.type', 'sell')
    ->where('transactions.status', 'final')
    ->where(
        "doctor_id","!=",null
    );
$from_date = $request->from_date;
$to_date = $request->to_date;
  if(!empty($from_date) && !empty($to_date)) {
    $transactions =  $transactions->whereBetween("transaction_date",[$from_date,$to_date]);
  }




  $transactions = $transactions->orderByDesc("id")
      ->paginate(10);
   return view('hospital.doctor.sell-report',compact("transactions","from_date","to_date"));
    }
    
    
    public function create()
    {
        return view('hospital.doctor.create')
            ;
    }

    public function delete($id)
    {
        DB::table("doctors")->where([
            "id" => $id
        ])
        ->delete();
        return redirect()->route("doctors.index")
            ;
    }
    public function deleteCommissions($id)
    {
        DB::table("doctor_commissions")->where([
            "id" => $id
        ])
        ->delete();
        return redirect()->route("commissions.index")
            ;
    }

    public function edit($id)
    {
     $doctor =   DB::table("doctors")->where([
            "id" => $id
        ])
        ->first();
        return view("hospital.doctor.edit",compact("doctor"))
            ;
    }
    public function editCommission($id)
    {
     $commission =   DoctorCommission::where([
            "id" => $id
        ])
        ->first();
        return view("hospital.comission.edit",compact("commission"))
            ;
    }

    public function payment($id)
    {
     $doctor  =  DB::table("doctors")->where([
            "id" => $id
        ])
        ->first();
        $payments = DoctorPayment::where([
            "doctor_id" => $id
        ])
        ->paginate(10);
        return view("hospital.doctor.payment",compact("doctor","payments"))
            ;
    }

public function addPayment(Request $request) {
    DoctorPayment::create([
"doctor_id" => $request->id,
"payment_amount" => $request->payment_amount
    ]);


    return redirect()->route("doctors.index");
 }
 public function createCommission()
 {


     return view('hospital.comission.create');
 }
 public function getCommissions()
 {
   $commissions = DoctorCommission::

   paginate(10);

     return view('hospital.comission.index',compact("commissions"));

 }

 public function CommissionStore(Request $request) {

  $commission = DoctorCommission::where([
    "doctor_id" => $request->doctor_id,
"service_id" => $request->service_id,
  ])->first();
  if($commission){
    $commission->doctor_commission = $request->doctor_commissionl;
    $commission->save();
  } else {
    DoctorCommission::create([
        "doctor_id" => $request->doctor_id,
        "service_id" => $request->service_id,
        "doctor_commission" => $request->doctor_commission,
            ]);
  }






    return redirect()->route("commissions.index");
 }
 public function CommissionUpdate(Request $request) {

    DB::table("doctor_commissions")
    ->where([
        "id" => $request->id
    ])
    ->update([
        "doctor_commission" => $request->doctor_commission,
    // "commission" => $request->commission
    ]);




    return redirect()->route("commissions.index");
 }

    public function update(Request $request) {
        DB::table("doctors")
        ->where([
            "id" => $request->id
        ])
        ->update([
        "name" => $request->name,
        "email"=> $request->email,
        "phone" => $request->phone,
        "address" => $request->address,
        // "commission" => $request->commission
        ]);
        return redirect()->route("doctors.index")
            ;
     }
    public function store(Request $request) {
 $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users,surname',
            'email' => 'required|string|email|unique:users',

        ]);
        if ($validator->fails()) {
            return back()->with(["error" => $validator->errors()->all()]);
        }
   $user = new User();
   $user->first_name = "dr";
   $user->surname = $request->name;
   $user->username = $request->name;
   $user->email = $request->email;
   $user->type = "doctor";
   $user->password = Hash::make("123456");
   $user->contact_no = $request->phone;
   $user->address = $request->address;
   $user->save();

       DB::table("doctors")
       ->insert([
       "name" => $request->name,
       "email"=> $request->email,
       "phone" => $request->phone,
       "address" => $request->address,
       "user_id" => $user->id,
    //    "commission" => $request->commission
       ]);
       return redirect()->route("doctors.index")
       ;
    }
    public function getAllDoctor()
    {
        if (request()->ajax()) {
            $term = request()->input('q', '');

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $doctor = Doctor::where('business_id', $business_id);



            if (!empty($term)) {
                $doctor->where(function ($query) use ($term) {
                        $query->where('name', 'like', '%' . $term .'%')
                            ->orWhere('email', 'like', '%' . $term .'%')
                            ->orWhere('phone', 'like', '%' . $term .'%');

                });
            }

            $contacts = $doctor->select(
                'doctors.id',
                'doctors.name as text',
                'doctors.phone as mobile',
                'doctors.address'
            )

                    ->get();

                    // $contacts2 = Contact::where('contacts.business_id', $business_id)

                    // ->leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
                    // ->leftjoin('customer_groups AS cg', 'contacts.customer_group_id', '=', 'cg.id')

                    // ->onlyCustomers()
                    // ->addSelect(['contacts.contact_id', 'contacts.name as text', 'cg.name as customer_group', 'city', 'state', 'country', 'landmark', 'mobile', 'contacts.id', 'is_default',
                    //     DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    //     DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                    //     DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    //     DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                    //     DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                    //     DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid")
                    //     ])

                    // ->get();

            return json_encode($contacts);
        }
    }
}
