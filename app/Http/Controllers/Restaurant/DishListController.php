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
use App\UsedItems;
class DishListController extends Controller
{
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

            $dish_list = DishList::where('business_id', $business_id)
                        ->select(['dis_name', 'dish_category_id','dish_type','dish_price','dish_availability', 'id']);

            return Datatables::of($dish_list)
                ->addColumn(
                    'action',
                    '@can("brand.update")
                    <button data-href="{{action(\'Restaurant\DishListController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".dish_list_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                    <button data-href="{{action(\'Restaurant\DishListController@showRawItems\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".dish_list_modal"><i class="glyphicon glyphicon-envelope"></i> View Raw Items</button>
                    &nbsp;
                    <button data-href="{{action(\'Restaurant\DishListController@createUsedRaw\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".dish_list_modal"><i class="glyphicon glyphicon-edit"></i> +Raw Items</button>
                        &nbsp;
                    @endcan
                    @can("brand.delete")
                        <button data-info="This Dish Will be Deleted!" data-href="{{action(\'Restaurant\DishListController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_brand_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
                ->removeColumn('id')
                ->rawColumns([5])
                ->make(false);
        }

        return view('restaurant.dish_list.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRawItems($id){
        if (!auth()->user()->can('brand.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $raw_items = DB::table('kitchen_used_raw_items')->where('kitchen_used_raw_items.business_id', $business_id)->where('kitchen_used_raw_items.dish_id',$id)->leftjoin('kitchen_raw_items', 'kitchen_raw_items.id', '=', 'kitchen_used_raw_items.raw_item_id')->leftjoin('units','units.id', '=', 'kitchen_raw_items.raw_item_used_unit')->select('kitchen_raw_items.raw_item_name as raw_item_name', 'kitchen_used_raw_items.used_quantity as used_quantity','units.actual_name as used_unit','kitchen_raw_items.raw_item_unit_price as unit_price','units.child_value')->get();
            return view('restaurant.used_raw.index')
                ->with(compact('raw_items','id'));
        }
    }

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
        $units = DB::table('units')->where('business_id', $business_id)->select(['actual_name', 'id','deleted_at'])->get();
        $dish_category = DB::table('kitchen_dish_category')->where('business_id', $business_id)->select(['dish_category_name', 'id','deleted_at'])->get();
        return view('restaurant.dish_list.create')
                ->with(compact('quick_add','units','dish_category'));
    }

    public function createUsedRaw($id){
        if (!auth()->user()->can('brand.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $raw_items = InternalKitchen::where('business_id', $business_id)->get();
            // $dish_category = DB::table('kitchen_dish_category')->where('business_id', $business_id)->select(['dish_category_name', 'id','deleted_at'])->get();
            return view('restaurant.used_raw.create')
                ->with(compact('raw_items','id'));
        }
    }
    public function storeRaw(Request $request)
    {
        if (!auth()->user()->can('brand.create')) {
            abort(403, 'Unauthorized action.');
        }
        $dish_id = $request->input('dish_id');
        $raw_item_id = $request->input('raw_item_id');
        $used_quantity = $request->input('used_quantity');
        $business_id = $request->session()->get('user.business_id');
        $data=array('dish_id'=>$dish_id,'raw_item_id'=>$raw_item_id,'used_quantity'=>$used_quantity,'business_id'=>$business_id);
        DB::table('kitchen_used_raw_items')->insert($data);
        $request->session()->flash('alert-success', 'raw Item was successful added!');
        return redirect()->action('Restaurant\DishListController@index');
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
            $input = $request->only(['dis_name', 'dish_category_id','dish_type','dish_price','dish_availability']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;
            $dish_list = DishList::create($input);
            $output = ['success' => true,
                            'data' => $dish_list,
                            'msg' => __("Dish is Added Successfully")
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
            $dish_list = DishList::where('business_id', $business_id)->find($id);
            $dish_category = DB::table('kitchen_dish_category')->where('business_id', $business_id)->select(['dish_category_name', 'id','deleted_at'])->get();
            return view('restaurant.dish_list.edit')
                ->with(compact('dish_list','dish_category'));
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
                $input = $request->only(['dis_name', 'dish_category_id','dish_type','dish_price','dish_availability']);
                $business_id = $request->session()->get('user.business_id');

                $dish_list = DishList::where('business_id', $business_id)->findOrFail($id);
                $dish_list->dis_name = $input['dis_name'];
                $dish_list->dish_category_id = $input['dish_category_id'];
                $dish_list->dish_type = $input['dish_type'];
                $dish_list->dish_price = $input['dish_price'];
                $dish_list->dish_availability = $input['dish_availability'];
                $dish_list->save();

                $output = ['success' => true,
                            'msg' => __("Your Dish is Updated")
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

                $dish_list = DishList::where('business_id', $business_id)->findOrFail($id);
                $dish_list->delete();

                $output = ['success' => true,
                            'msg' => __("This Dish is Deleted Successfully")
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
