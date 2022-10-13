<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Hrm;
use Redirect;
class HrmController extends Controller{
    public function index(){
        if (!auth()->user()->can('hrm_employyes.view') || !auth()->user()->can('hrm_employyes.create')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $ib_category = Hrm::where('business_id', $business_id)
                        ->select(['id', 'employee_id', 'name','department','designation','phone','address','doj','salary','status']);
            return Datatables::of($ib_category)
                ->addColumn(
                    'action',
                    '@can("hrm_employyes.update")
                    <button data-href="{{action(\'HrmController@show\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".user_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.view")</button>
                    &nbsp;
                    @endcan
                    @can("hrm_employyes.delete")
                    <button data-href="{{action(\'HrmController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_hrm_employee_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
                // ->editColumn('image', function ($row) { 
                //     $url= asset('img/'.$row->image);
                //     return '<img src="'.$url.'" border="0" width="40" class="img-rounded" align="center" />';
                // })
                ->editColumn('status', function ($row) { 
                    $business_id = request()->session()->get('user.business_id');
                    $status = Hrm::where('business_id', $business_id)->where('id',$row->id)->select('exit_date')->first();
                    if($status->exit_date==""){
                        return "Active";
                    }else{
                        return "Inactive";
                    }
                   
                })
                ->removeColumn('id')
                ->rawColumns([9])
                ->make(false);
        }
        return view('hrm.employees.index');
    }

    public function create(){
        if (!auth()->user()->can('hrm_employyes.create')) {
            abort(403, 'Unauthorized action.');
        }
        return view('hrm.employees.create');
    }

    public function store(Request $request){
        if (!auth()->user()->can('hrm_employyes.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $input = $request->only([ 'name', 'father_name','dob','gender','phone','address','p_address','employee_id','department','designation','doj','exit_date','status','salary']);
            $input['business_id'] = $request->session()->get('user.business_id');
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                if ($request->image->getSize() <= config('constants.image_size_limit')) {
                    $new_file_name = time() . '_' . $request->image->getClientOriginalName();
                   
                    $image_path = config('constants.product_img_path');
                    $path = $request->image->storeAs($image_path, $new_file_name);
                    
                    if ($path) {
                        $input['image'] = $new_file_name;
                    }
                }
            }
            Hrm::create($input);
//             return Redirect::back()->withErrors(['You Dont have Enough Balance']);
// ->with('message', 'IT WORKS!');
            return redirect()->action('HrmController@index')->withErrors("Employee Added!");
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            // $output = ['success' => false,
            //                 'msg' => __("messages.something_went_wrong")
            //             ];
            return redirect()->action('HrmController@index')->withErrors("Something went Wrong");
        }
    }
    public function show(Request $request, $id){
        if (!auth()->user()->can('hrm_employyes.view')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $expense_category = Hrm::where('business_id', $business_id)->find($id);
            //dd($expense_category->name);
            return view('hrm.employees.show',compact('expense_category'));
        }
    }
    public function edit(Request $request, $id){
        if (!auth()->user()->can('hrm_employyes.update')) {
            abort(403, 'Unauthorized action.');
        }
     
            $business_id = request()->session()->get('user.business_id');
            $expense_category = Hrm::where('business_id', $business_id)->find($id);
            //dd($expense_category->name);
            return view('hrm.employees.edit',compact('expense_category'));
        
    }

    public function update(Request $request, $id){
        if (!auth()->user()->can('hrm_employyes.update')) {
            abort(403, 'Unauthorized action.');
        }
       
            try {
                $input = $request->only(['image', 'name', 'father_name','dob','gender','phone','address','p_address','employee_id','department','designation','doj','exit_date','status','salary']);
                $business_id = $request->session()->get('user.business_id');
                $expense_category = Hrm::where('business_id', $business_id)->findOrFail($id);
                $expense_category->image = $input['image'];
                $expense_category->name = $input['name'];
                $expense_category->father_name = $input['father_name'];
                $expense_category->dob = $input['dob'];
                $expense_category->gender = $input['gender'];
                $expense_category->phone = $input['phone'];
                $expense_category->address = $input['address'];
                $expense_category->p_address = $input['p_address'];
                $expense_category->employee_id = $input['employee_id'];
                $expense_category->department = $input['department'];
                $expense_category->designation = $input['designation'];
                $expense_category->doj = $input['doj'];
                $expense_category->exit_date = $input['exit_date'];
                $expense_category->status = $input['status'];
                $expense_category->salary = $input['salary'];
                $expense_category->save();
                // $output = ['success' => true,
                //             'msg' => __("Banking category updated successfully")
                //             ];
                return redirect()->action('HrmController@index')->withErrors("Employee Updated!");
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                // $output = ['success' => false,
                //             'msg' => __("messages.something_went_wrong")
                //         ];
                return redirect()->action('HrmController@index')->withErrors("Something went Wrong");
            }
            //return $output;
        
    }

    public function destroy($id){
        if (!auth()->user()->can('hrm_employyes.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                $expense_category = Hrm::where('business_id', $business_id)->findOrFail($id);
                $expense_category->delete();
                $output = ['success' => true,
                            'msg' => __("expense.deleted_success")
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
