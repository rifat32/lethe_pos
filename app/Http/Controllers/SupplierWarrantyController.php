<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\SupplierWarranty;
use App\CustomerWarranty;
use DB;
class SupplierWarrantyController extends Controller
{
    public function index(){
        if (!auth()->user()->can('supplier_warranty.view') || !auth()->user()->can('supplier_warranty.create')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $supplier_warranty_list = SupplierWarranty::where('business_id', $business_id)
                        ->select(['warrent_id','customer_name','product_name','reason','status', 'id']);
            return Datatables::of($supplier_warranty_list)
                ->addColumn(
                    'action',
                    '@can("supplier_warranty.update")
                    <button data-href="{{action(\'SupplierWarrantyController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".supplier_warranty_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                    &nbsp;
                    @endcan
                    @can("supplier_warranty.delete")
                    <button data-href="{{action(\'SupplierWarrantyController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_supplier_warranty"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
                ->removeColumn('id')
                ->rawColumns([5])
                ->make(false);
        }
        return view('warranty.supplier.index');
    }

    public function create(){
        if (!auth()->user()->can('supplier_warranty.create')) {
            abort(403, 'Unauthorized action.');
        }

        $warrent=DB::Table('warrant')
        ->whereDate("end_date", ">=", now())
        ->get();
        return view('warranty.supplier.create',compact('warrent'));
    }

    public function store(Request $request){
        if (!auth()->user()->can('supplier_warranty.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
                    
            $input = $request->only(['warrent_id','customer_name', 'product_name','reason','status']);
            $input['business_id'] = $request->session()->get('user.business_id');
            $warrent_id=$request->warrent_id;
           
           
            $customer_warranty = CustomerWarranty::where('id', $warrent_id)->update(['status'=>$input['status']]);
            SupplierWarranty::create($input);
            
        

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
        if (!auth()->user()->can('supplier_warranty.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $supplier_warranty = SupplierWarranty::where('business_id', $business_id)->find($id);

            $warrent=DB::Table('warrant')
            ->get();
            return view('warranty.supplier.edit',compact('supplier_warranty','warrent'));
        }
    }

    public function update(Request $request, $id){
        if (!auth()->user()->can('supplier_warranty.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                //$input = $request->only(['name', 'code']);
                $business_id = $request->session()->get('user.business_id');

                $supplier_warranty = SupplierWarranty::where('business_id', $business_id)->findOrFail($id);
                $supplier_warranty->warrent_id = $request->warrent_id;
                $supplier_warranty->customer_name = $request->customer_name;
                $supplier_warranty->product_name = $request->product_name;
                $supplier_warranty->reason = $request->reason;
                $supplier_warranty->status = $request->status;
               

                $warrent_id=$request->warrent_id;
                $customer_warranty = CustomerWarranty::where('id', $warrent_id)->update(['status'=> $supplier_warranty->status]);
               
                

                $supplier_warranty->save();

                $output = ['success' => true,
                            'msg' => __("Warranty Record updated successfully")
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
        if (!auth()->user()->can('supplier_warranty.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $supplier_warranty = SupplierWarranty::where('business_id', $business_id)->findOrFail($id);
                $supplier_warranty->delete();

                $output = ['success' => true,
                            'msg' => __("Warranty Record Deleted!")
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