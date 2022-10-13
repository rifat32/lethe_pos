<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\HrmTransaction;
use App\Hrm;
use Yajra\DataTables\Facades\DataTables;
class HrmTransactionController extends Controller{

    public function index(){
        if (!auth()->user()->can('hrm_transaction.view') || !auth()->user()->can('hrm_transaction.create')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $hrmt = HrmTransaction::where('business_id', $business_id)
                        ->select(['e_id', 'type', 'amount','created_at','id']);
            return Datatables::of($hrmt)
                ->addColumn(
                    'action',
                    '@can("hrm_transaction.update")
                    <button data-href="{{action(\'HrmTransactionController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".hrm_transactions_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                    &nbsp;
                    @endcan
                    @can("hrm_transaction.delete")
                    <button data-href="{{action(\'HrmTransactionController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_hrm_transactions"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
                ->editColumn('e_id', function ($row) { 
                    $business_id = request()->session()->get('user.business_id');
                    $bb = Hrm::where('business_id', $business_id)->where('id',$row->e_id)->first();
                    return $bb->employee_id;
                })
                ->removeColumn('id')
                ->rawColumns([4])
                ->make(false);
        }
        return view('hrm.transactions.index');
    }

    public function create(){
        if (!auth()->user()->can('hrm_transaction.create')) {
            abort(403, 'Unauthorized action.');
        }
        $hrmt = Hrm::pluck('employee_id', 'id');
        $business_id = request()->session()->get('user.business_id');
        $salary = Hrm::where('business_id', $business_id)->select('salary')->get();
        return view('hrm.transactions.create',compact('hrmt','salary'));
    }

    public function store(Request $request){
        if (!auth()->user()->can('hrm_transaction.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $input = $request->only(['e_id', 'type','amount']);
            $input['business_id'] = $request->session()->get('user.business_id');

            HrmTransaction::create($input);
            $output = ['success' => true,
                            'msg' => __("Transaction Successful")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }
        return $output;
    }

    public function edit(Request $request,$id){
        if (!auth()->user()->can('hrm_transaction.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $hrmt = HrmTransaction::where('business_id', $business_id)->find($id);
            $hrm = Hrm::pluck('employee_id', 'id');
            return view('hrm.transactions.edit')->with(compact('hrmt','hrm'));
        }
    }

    public function update(Request $request, $id){
        if (!auth()->user()->can('hrm_transaction.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $input = $request->only(['e_id', 'type','amount']);
                $business_id = $request->session()->get('user.business_id');

                $hrmt = HrmTransaction::where('business_id', $business_id)->findOrFail($id);
                $hrmt->e_id = $input['e_id'];
                $hrmt->type = $input['type'];
                $hrmt->amount = $input['amount'];
                $hrmt->save();

                $output = ['success' => true,
                            'msg' => __("Transaction updated successfully")
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
        if (!auth()->user()->can('hrm_transaction.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $hrmt = HrmTransaction::where('business_id', $business_id)->findOrFail($id);
                $hrmt->delete();

                $output = ['success' => true,
                            'msg' => __("transaction Deleted!")
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
