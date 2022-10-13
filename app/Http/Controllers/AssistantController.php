<?php

namespace App\Http\Controllers;

use App\Assistant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssistantController extends Controller
{

    public function index()
    {
      $doctors =  DB::table("assistants")

      ->paginate(10);

        return view('hospital.assistant.index',compact("doctors"));

    }

    public function create()
    {



        return view('hospital.assistant.create')
            ;
    }

    public function store(Request $request) {

     
       
              DB::table("assistants")
              ->insert([
              "name" => $request->name,
              "email"=> $request->email,
              "phone" => $request->phone,
              "address" => $request->address,
           //    "commission" => $request->commission
              ]);
              return redirect()->route("assistants.index")
              ;
           }
           public function delete($id)
           {
               DB::table("assistants")->where([
                   "id" => $id
               ])
               ->delete();
               return redirect()->route("assistants.index")
                   ;
           }
           public function edit($id)
           {
            $doctor =   DB::table("assistants")->where([
                   "id" => $id
               ])
               ->first();
               return view("hospital.assistant.edit",compact("doctor"))
                   ;
           }
           public function history($id)
           {
            $doctor =   Assistant::where([
                   "id" => $id
               ])
               ->first();
               return view("hospital.assistant.history",compact("doctor"))
                   ;
           }
           public function update(Request $request) {
            DB::table("assistants")
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
            return redirect()->route("assistants.index")
                ;
         }
         public function getAllDoctor()
         {
             if (request()->ajax()) {
                 $term = request()->input('q', '');
     
                 $business_id = request()->session()->get('user.business_id');
                 $user_id = request()->session()->get('user.id');
     
                 $doctor = DB::table('assistants')->where('business_id', $business_id);
     
     
     
                 if (!empty($term)) {
                     $doctor->where(function ($query) use ($term) {
                             $query->where('name', 'like', '%' . $term .'%')
                                 ->orWhere('email', 'like', '%' . $term .'%')
                                 ->orWhere('phone', 'like', '%' . $term .'%');
     
                     });
                 }
     
                 $contacts = $doctor->select(
                     'assistants.id',
                     'assistants.name as text',
                     'assistants.phone as mobile',
                     'assistants.address'
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
