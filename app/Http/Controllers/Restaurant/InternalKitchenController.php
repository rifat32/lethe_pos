<?php

namespace App\Http\Controllers\Restaurant;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use App\Restaurant\InternalKitchen;
use App\Restaurant\DishCategory;
use App\Restaurant\DishList;
use Yajra\DataTables\Facades\DataTables;
use DB;
class InternalKitchenController extends Controller{
      
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('brand.view') && !auth()->user()->can('brand.create')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $raw_items = InternalKitchen::where('business_id', $business_id)->select(['raw_item_name', 'quantity','raw_item_unit','raw_item_unit_price','raw_item_used_unit', 'id','business_id']);
            return Datatables::of($raw_items)
                ->addColumn(
                    'action',
                    '@can("brand.update")
                    <button data-href="{{action(\'Restaurant\InternalKitchenController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".raw_item_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                    &nbsp;
                    @endcan
                    @can("brand.delete")
                        <button data-info="This Raw Item will be Deleted" data-href="{{action(\'Restaurant\InternalKitchenController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_brand_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )->editColumn('raw_item_unit', function ($row) {
                    $raw_item_unit = DB::table('units')->where('id', $row->raw_item_unit)->select(['actual_name'])->first();
                    return $raw_item_unit=$raw_item_unit->actual_name;
               
            })->editColumn('raw_item_used_unit', function ($row) {
                $raw_item_used_unit = DB::table('units')->where('id', $row->raw_item_used_unit)->select(['actual_name'])->first();
                return $raw_item_used_unit=$raw_item_used_unit->actual_name;
           
        }) ->editColumn('business_id', function ($row) {
            $aa = request()->session()->get('user.business_id');
            $business_id = DB::table('kitchen_raw_items')->where('business_id', $aa)->where('id',$row->id)->select(['raw_item_unit_price','quantity'])->first();
            return $bb=$business_id->raw_item_unit_price * $business_id->quantity;
       
    })
                ->removeColumn('id')
                ->rawColumns([5])
                ->escapeColumns(null)
                ->make(false);
        }

        return view('restaurant.raw_items.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('brand.create')) {
            abort(403, 'Unauthorized action.');
        }

        $quick_add = false;
        if (!empty(request()->input('quick_add'))) {
            $quick_add = true;
        }
        $business_id = request()->session()->get('user.business_id');
        $units = DB::table('units')->where('business_id', $business_id)
        ->select(['actual_name', 'id','deleted_at'])->get();
        return view('restaurant.raw_items.create')
                ->with(compact('quick_add','units'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('brand.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['raw_item_name', 'quantity','raw_item_unit','raw_item_unit_price','raw_item_used_unit']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;
            $raw_items = InternalKitchen::create($input);
            $output = ['success' => true,
                            'data' => $raw_items,
                            'msg' => __("Raw Items Added Successfully!")
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
        if (!auth()->user()->can('brand.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $raw_items = InternalKitchen::where('business_id', $business_id)->find($id);
            $units = DB::table('units')->where('business_id', $business_id)->get();
          
              //  dd($units);
            
            return view('restaurant.raw_items.edit')
                ->with(compact('raw_items','units'));
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
        if (!auth()->user()->can('brand.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['raw_item_name', 'quantity','raw_item_unit','raw_item_unit_price','raw_item_used_unit']);
                $business_id = $request->session()->get('user.business_id');

                $raw_items = InternalKitchen::where('business_id', $business_id)->findOrFail($id);
                $raw_items->raw_item_name = $input['raw_item_name'];
                $raw_items->quantity = $input['quantity'];
                $raw_items->raw_item_unit = $input['raw_item_unit'];
                $raw_items->raw_item_unit_price = $input['raw_item_unit_price'];
                $raw_items->raw_item_used_unit = $input['raw_item_used_unit'];
                $raw_items->save();

                $output = ['success' => true,
                            'msg' => __("Raw Item Updated Successfully")
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
        if (!auth()->user()->can('brand.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $brand = InternalKitchen::where('business_id', $business_id)->findOrFail($id);
                $brand->delete();

                $output = ['success' => true,
                            'msg' => __("Raw Items Deleted Successfully")
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
