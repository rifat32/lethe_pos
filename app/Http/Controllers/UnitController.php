<?php

namespace App\Http\Controllers;

use App\Unit;
use DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('unit.view') && !auth()->user()->can('unit.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $unit = Unit::where('business_id', $business_id)
                        ->select(['id','actual_name', 'short_name', 'allow_decimal', 'child_id','child_value']);

            return Datatables::of($unit)
                ->addColumn(
                    'action',
                    '@can("unit.update")
                    <button data-href="{{action(\'UnitController@edit\', [$id])}}" class="btn btn-xs btn-primary edit_unit_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                    @endcan
                    @can("unit.delete")
                        <button data-href="{{action(\'UnitController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_unit_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
                ->editColumn('allow_decimal', function ($row) {
                    if ($row->allow_decimal) {
                        return __('messages.yes');
                    } else {
                        return __('messages.no');
                    }
                })
                
                ->editColumn('child_id', function ($row) {
                    if ($row->child_id) {
                        $business_id = request()->session()->get('user.business_id');
                        $child_id= Unit::where('business_id', $business_id)->where('id',$row->child_id)
                        ->select(['actual_name'])->first();
                        return $child_id->actual_name ??'';
                        
                    } else {
                        return __('No Parent Unit');
                    }
                })
                ->editColumn('child_value', function ($row) {
                    if ($row->child_value) {
                        $business_id = request()->session()->get('user.business_id');
                        $child_id= Unit::where('business_id', $business_id)->where('id',$row->child_id)
                        ->select('actual_name','short_name')->first();
                       return $row->child_value.' '.$row->short_name??''.' = 1 '.$child_id->short_name??'';
                        
                    } else {
                        return __('No Parent Unit');
                    }
                })
               
                ->rawColumns([6])
                ->make(false);
        }

        return view('unit.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('unit.create')) {
            abort(403, 'Unauthorized action.');
        }

        $quick_add = false;
        if (!empty(request()->input('quick_add'))) {
            $quick_add = true;
        }
        $business_id = request()->session()->get('user.business_id');
        $parent_units = DB::Table('units')->where('business_id',$business_id)->get();
        return view('unit.create')
                ->with(compact('quick_add','parent_units'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('unit.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['actual_name', 'short_name', 'allow_decimal','child_id','child_value']);
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['created_by'] = $request->session()->get('user.id');
            

            $unit = Unit::create($input);
            $output = ['success' => true,
                        'data' => $unit,
                        'msg' => __("unit.added_success")
                    ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                        'msg' => __("messages.something_went_wrong")
                    ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('unit.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $unit = Unit::where('business_id', $business_id)->find($id);
            $business_id = request()->session()->get('user.business_id');
            $parent_units = DB::Table('units')->where('business_id',$business_id)->get();
            return view('unit.edit')
                ->with(compact('unit','parent_units'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('unit.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['actual_name', 'short_name', 'allow_decimal','child_id','child_value']);
                $business_id = $request->session()->get('user.business_id');

                $unit = Unit::where('business_id', $business_id)->findOrFail($id);
                $unit->actual_name = $input['actual_name'];
                $unit->short_name = $input['short_name'];
                $unit->allow_decimal = $input['allow_decimal'];
                $unit->child_id = $input['child_id'];
                $unit->child_value = $input['child_value'];
                $unit->save();

                $output = ['success' => true,
                            'msg' => __("unit.updated_success")
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('unit.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $unit = Unit::where('business_id', $business_id)->findOrFail($id);
                $unit->delete();

                $output = ['success' => true,
                            'msg' => __("unit.deleted_success")
                            ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => '__("messages.something_went_wrong")'
                        ];
            }

            return $output;
        }
    }
}
