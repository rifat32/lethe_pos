<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\CustomerWarranty;
use APP\warrant;
use DB;
class CustomerWarrantyController extends Controller
{
    public function index(){
        if (!auth()->user()->can('customer_warranty.view') || !auth()->user()->can('customer_warranty.create')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $customer_warranty_list = CustomerWarranty::where('business_id', $business_id)
                        ->select(['customer_name', 'product_name','reason','status', 'id']);
            return Datatables::of($customer_warranty_list)
                ->addColumn(
                    'action',
                    '@can("customer_warranty.update")
                    <button data-href="{{action(\'CustomerWarrantyController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".customer_warranty_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                    &nbsp;
                    @endcan
                    @can("customer_warranty.delete")
                    <button data-href="{{action(\'CustomerWarrantyController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_customer_warranty"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
                ->removeColumn('id')
                ->rawColumns([4])
                ->make(false);
        }
        else{
            return view('warranty.customer.index');
         
        }
        //return view('warranty.customer.index');
    }

    public function create(){
        if (!auth()->user()->can('customer_warranty.create')) {
            abort(403, 'Unauthorized action.');
        }


        $customer=DB::Table('contacts')
        ->where('type','customer')
        ->get();
        $product=DB::Table('products')
        ->get();
       
        return view('warranty.customer.create',compact('customer','product'));
       
    }

    public function store(Request $request){
        if (!auth()->user()->can('customer_warranty.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $input = $request->only(['customer_name', 'product_name','reason','status','start_date', 'end_date']);
            $input['business_id'] = $request->session()->get('user.business_id');

            CustomerWarranty::create($input);
            $output = ['success' => true,
                            'msg' => __("Warranty Record Added Successfully")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }
        return $output;
    }

    public function edit($id){
        if (!auth()->user()->can('customer_warranty.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $customer_warranty = CustomerWarranty::where('business_id', $business_id)->find($id);

            $customer=DB::Table('contacts')
            ->where('type','customer')
            ->get();
            $product=DB::Table('products')
            ->get();
            


            return view('warranty.customer.edit',compact('customer_warranty','customer','product'));
        }
    }

    public function update(Request $request, $id){
        if (!auth()->user()->can('customer_warranty.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
               // $input = $request->only(['name', 'code']);
                $business_id = $request->session()->get('user.business_id');

                $customer_warranty = CustomerWarranty::where('business_id', $business_id)->findOrFail($id);
                $customer_warranty->customer_name = $request->customer_name;;
                $customer_warranty->product_name = $request->product_name;
                $customer_warranty->reason = $request->reason;
                $customer_warranty->status = $request->status;

                $customer_warranty->save();

                $output = ['success' => true,
                            'msg' => __("Customer Warranty Record updated successfully")
                            ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }

    public function destroy($id){
        if (!auth()->user()->can('customer_warranty.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $customer_warranty = CustomerWarranty::where('business_id', $business_id)->findOrFail($id);
                $customer_warranty->delete();

                $output = ['success' => true,
                            'msg' => __("Customers Warranty Record Deleted!")
                            ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }
            return $output;
        }
    }
}
