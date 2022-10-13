<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Hrm;
use Carbon;
use DateTime;
use App\HrmAttendence;
use Yajra\DataTables\Facades\DataTables;
class HrmAttendenceController extends Controller{
  
    public function index(){
        if (!auth()->user()->can('hrm_attendence.view') || !auth()->user()->can('hrm_attendence.create')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $hrma = HrmAttendence::where('business_id', $business_id)
                        ->select(['e_id','date', 'status','id']);
            return Datatables::of($hrma)
                ->addColumn(
                    'action',
                    '@can("hrm_attendence.update")
                    <button data-href="{{action(\'HrmAttendenceController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".hrm_attendence_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                    &nbsp;
                    @endcan
                    @can("hrm_attendence.delete")
                    <button data-href="{{action(\'HrmAttendenceController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_hrm_attendence"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
               
                ->editColumn('e_id', function ($row) { 
                    $business_id = request()->session()->get('user.business_id');
                    $bb = Hrm::where('business_id', $business_id)->where('id',$row->e_id)->first();
                    return $bb->employee_id;
                })
                ->removeColumn('id')
                ->rawColumns([3])
                ->make(false);
        }
        return view('hrm.attendence.index');
    }

    public function create(){
        if (!auth()->user()->can('hrm_attendence.create')) {
            abort(403, 'Unauthorized action.');
        }
        $hrma = Hrm::select('employee_id','name', 'id')->where('exit_date',null)->get();
        $dt = new DateTime();
        $cdate = $dt->format('Y-m-d');
        // dd($cdate);
        $hrmd = HrmAttendence::select('date')->where('date', $cdate)->first();
        
        //dd($hrmd);
        return view('hrm.attendence.create',compact('hrma','hrmd'));
    }

    public function store(Request $request){
        if (!auth()->user()->can('hrm_attendence.create')) {
            abort(403, 'Unauthorized action.');
        }
        
       
        try {
            $Attendance = [];
            $Users = $request->except('_token', 'date', 'datatable_length');
          
            foreach ($Users as $ID => $Status) {
                $exp = explode("-",$ID);
                $real_id =$exp[1];
               // dd($real_id);
                $Attendance[] = [
                    'e_id' => $real_id,
                    'date' => Carbon::parse($request->date),
                    'status' => $Status,
                    'business_id' => $request->session()->get('user.business_id'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            //dd($Attendance);
            //  for($i=0;i<count($request->status);$i++){
            //      dd($request->status[0]);
            //     // $input['e_id'] = $request->session()->get('user.business_id');
            //     // $input['status'] = $request->session()->get('user.business_id');
            //     // $input['business_id'] = $request->session()->get('user.business_id');
            //     // dd($input);
            //     // HrmAttendence::create($input);
            //  }
            // $input = $request->only(['e_id', 'status']);
            // $input['business_id'] = $request->session()->get('user.business_id');

             HrmAttendence::insert($Attendance);
             return redirect()->back()->with('message', 'Attendence Saved!');
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
        if (!auth()->user()->can('hrm_attendence.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $hrma = HrmAttendence::where('business_id', $business_id)->find($id);
            $hrm = Hrm::pluck('employee_id', 'id');
            return view('hrm.attendence.edit')->with(compact('hrma','hrm'));
        }
    }

    public function update(Request $request, $id){
        if (!auth()->user()->can('hrm_attendence.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $input = $request->only(['e_id', 'date','status']);
                $business_id = $request->session()->get('user.business_id');
                $hrmt = HrmAttendence::where('business_id', $business_id)->findOrFail($id);
                $hrmt->e_id = $input['e_id'];
                $hrmt->date = $input['date'];
                $hrmt->status = $input['status'];
                $hrmt->save();

                $output = ['success' => true,
                            'msg' => __("Attendence updated successfully")
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
        if (!auth()->user()->can('hrm_attendence.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $hrmt = HrmAttendence::where('business_id', $business_id)->findOrFail($id);
                $hrmt->delete();

                $output = ['success' => true,
                            'msg' => __("Attendence Deleted!")
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
