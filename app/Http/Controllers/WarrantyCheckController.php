<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Yajra\DataTables\Facades\DataTables;
use App\WarrantyCheck;
use Redirect;

class WarrantyCheckController extends Controller
{
    public function index(){
      
        if (!auth()->user()->can('warranty_check.view') || !auth()->user()->can('warranty_check.create')) {
          
            abort(403, 'Unauthorized action.');
        }
        $warrants=DB::Table('warranty_check')->where('deleted_at',null)
        ->paginate(7);
          
        return view('warranty_check.index',compact('warrants'));
    }

    public function create(){
        if (!auth()->user()->can('warranty_check.create')) {
            abort(403, 'Unauthorized action.');
        }


        $invoice=DB::Table('transaction_sell_lines')
        ->get();
        
       
        return view('warranty_check.create',compact('invoice'));
       
    }

    public function store(Request $request){
        if (!auth()->user()->can('customer_warranty.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $input = $request->only(['invoice_id', 'warranty_issued_date','duration']);
            $duration=$input['duration'];
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['expired_date']= $input['warranty_issued_date'];
            
            $input['expired_date']=date('Y-m-d', strtotime('+'.$duration.'years'));
           

           WarrantyCheck::create($input);

            $output = ['success' => true,
                            'msg' => __("Warranty Record Added Successfully")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }
         return Redirect::to('warranty-check')->with('success','Warranty Record Added Successfully');
  
 
         
    }

    public function edit($id){

        $invoice=DB::Table('transaction_sell_lines')
        ->get();
           $details= WarrantyCheck:: findOrFail($id);
		   return view('warranty_check.edit', compact('details','invoice'));
        
    }

    public function update(Request $request, $id){
      
            try {
               $input = $request->only(['name', 'code']);
                $business_id = $request->session()->get('user.business_id');

                $warranty = WarrantyCheck::findOrFail($id);
               
                $warranty->invoice_id = $request->invoice_id;
                $warranty->warranty_issued_date = $request->warranty_issued_date;
                $warranty->duration=$request->duration;
                $expired_date=$request->warranty_issued_date;
                $expired_date=date('Y-m-d', strtotime('+'.$warranty->duration.'years'));
                $warranty->expired_date=$expired_date;

                $warranty->save();

                $output = ['success' => true,
                            'msg' => __("Customer Warranty Record updated successfully")
                            ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return redirect::to('warranty-check')->with('success','Record Successfully Updated');

       
    }


    public function search(Request $request){
      
        if (!auth()->user()->can('warranty_check.view') || !auth()->user()->can('warranty_check.create')) {
          
            abort(403, 'Unauthorized action.');
        }

        $search=$request->search;
        $warrants=DB::Table('warranty_check')->where('deleted_at',null)
        ->where('invoice_id',$search)
        ->paginate(10);
          
        return view('warranty_check.index',compact('warrants'));
    }


    public function delete($id){
        if (!auth()->user()->can('customer_warranty.create')) {
            abort(403, 'Unauthorized action.');
        }
          $warranty= WarrantyCheck:: findOrFail($id);
          $warranty->delete();
           return redirect::to('warranty-check')->with('success','Record successfully Deleted');
     
     }
}
