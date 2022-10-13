<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;

use DB;
use DataTables;
use App\Product;
use Carbon;
use DateTime;
class SalesCommissionAgentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('user.view') && !auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $users = User::where('business_id', $business_id)
                        ->where('is_cmmsn_agnt', 1)
                        ->select(['id',
                            DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"),
                            'email', 'contact_no', 'address', 'cmmsn_percent']);

            return Datatables::of($users)
                ->addColumn(
                    'action',
                    '@can("user.update")
                    <button type="button" data-href="{{action(\'SalesCommissionAgentController@edit\', [$id])}}" data-container=".commission_agent_modal" class="btn btn-xs btn-modal btn-primary"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                        <button data-href="{{action(\'SalesCommissionAgentController@addProduct\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".commission_agent_modal"><i class="glyphicon glyphicon-edit"></i> +Products</button>
                        
                        @endcan
                        @can("user.delete")
                        <button data-href="{{action(\'SalesCommissionAgentController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_commsn_agnt_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                        @endcan'
                )->editColumn('full_name', function ($row) {
                    $full_name = $row->full_name;
                    return '<a href="'.action('SalesCommissionAgentController@showProduct', [$row->id]) . '" target="_blank">'.$full_name .'</a>';
                })
                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->escapeColumns(null)
                ->make(true);
        }

        return view('sales_commission_agent.index');
    }

    public function showProduct($id){
        if (!auth()->user()->can('user.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $products = DB::table('commision_agent_stock')->where('commision_agent_stock.product_quantity','!=',0)->where('commision_agent_stock.business_id', $business_id)->where('commision_agent_stock.agent_id',$id)->join('variations','commision_agent_stock.variations_id','=','variations.id')->leftjoin('products', 'commision_agent_stock.product_id', '=', 'products.id')->select('products.name as product_name', 'products.id as product_id','commision_agent_stock.product_quantity as product_quantity','commision_agent_stock.id as capi','variations.sub_sku as sub_sku','variations.sell_price_inc_tax','variations.default_sell_price')->get();
        return view('sales_commission_agent.show_product')
            ->with(compact('products','id'));
        
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){
        if (!auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('sales_commission_agent.create');
    }

    public function addProduct($id){
        if (!auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
       
            $products = Product::where('products.business_id', $business_id)->join('variations','products.id','=','variations.product_id')->select('products.id as product_id','products.name as product_name','variations.sub_sku as sub_sku','variations.sub_sku as sub_sku','variations.id as variations_id')->get();
           // dd($products);
            // $dish_category = DB::table('kitchen_dish_category')->where('business_id', $business_id)->select(['dish_category_name', 'id','deleted_at'])->get();
            return view('sales_commission_agent.add_product')
                ->with(compact('products','id'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        if (!auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['surname', 'first_name', 'last_name', 'email', 'address', 'contact_no', 'cmmsn_percent']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;
            $input['username'] = uniqid();
            $input['password'] = 'DUMMY';
            $input['is_cmmsn_agnt'] = 1;

            $user = User::create($input);
            
            $output = ['success' => true,
                          'msg' => __("lang_v1.commission_agent_added_success")
                      ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                           'msg' => __("messages.something_went_wrong")
                       ];
        }

        return $output;
    }

    public function storeProduct(Request $request){
        if (!auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }
        $productAndvariation = $request->input('product_id');
        $product_variation = explode('-',$productAndvariation);
        $product_id_checker = $product_variation[0];
        $product_variation_checker = $product_variation[1];
        $alreasyInserted = DB::table('commision_agent_stock')->where('product_id',$product_id_checker)->where('variations_id',$product_variation_checker)->first();
        if(isset($alreasyInserted)){
            $input = $request->only(['agent_id', 'product_id','product_quantity']);
            $business_id = $request->session()->get('user.business_id');
            $new_qty = $alreasyInserted->product_quantity + $input['product_quantity'];
            $hrmt =DB::table('commision_agent_stock')->where('business_id', $business_id)->where('product_id',$alreasyInserted->product_id)->where('variations_id',$product_variation_checker)->update(['product_quantity' => $new_qty]);
        }else{
            $agent_id = $request->input('agent_id');
            $product_quantity = $request->input('product_quantity');
            $productAndvariation = $request->input('product_id');
            $product_variation = explode('-',$productAndvariation);
            $product_id = $product_variation[0];
            $variations_id = $product_variation[1];
            $business_id = $request->session()->get('user.business_id');
            $data=array('agent_id'=>$agent_id,'product_id'=>$product_id,'product_quantity'=>$product_quantity,'business_id'=>$business_id,'variations_id'=>$variations_id);
            DB::table('commision_agent_stock')->insert($data);
        }
        $request->session()->flash('alert-success', 'Product Transfered to COmmsision Agent successful!');
        return redirect()->action('SalesCommissionAgentController@showProduct',$request->input('agent_id'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        if (!auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        $user = User::findOrFail($id);

        return view('sales_commission_agent.edit')
                    ->with(compact('user'));
    }
    public function editProduct($id){
        if (!auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        $edit_products = DB::table('commision_agent_stock')->where('id',$id)->first();
        $business_id = request()->session()->get('user.business_id');
        $products =  Product::where('products.business_id', $business_id)->join('variations','products.id','=','variations.product_id')->select('products.id as product_id','products.name as product_name','variations.sub_sku as sub_sku','variations.sub_sku as sub_sku','variations.id as variations_id')->get();
        // dd($edit_products);
        return view('sales_commission_agent.edit_product')
                    ->with(compact('products','edit_products','id'));
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
        if (!auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['surname', 'first_name', 'last_name', 'email', 'address', 'contact_no', 'cmmsn_percent']);
                $business_id = $request->session()->get('user.business_id');

                $user = User::where('id', $id)
                            ->where('business_id', $business_id)
                            ->where('is_cmmsn_agnt', 1)
                            ->first();
                $user->update($input);

                $output = ['success' => true,
                            'msg' => __("lang_v1.commission_agent_updated_success")
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
    public function updateProduct(Request $request, $id)
    {
        if (!auth()->user()->can('user.update')) {
            abort(403, 'Unauthorized action.');
        }
       // dd($id);

        if (request()->ajax()) {
            try {
                $input = $request->only(['agent_id', 'product_id', 'product_quantity','variations_id']);
                $business_id = $request->session()->get('user.business_id');

                $user = DB::table('commision_agent_stock')->where('id', $id)
                            ->where('business_id', $business_id)
                            ->limit(1);
                $user->update($input);

                $output = ['success' => true,
                            'msg' => __("lang_v1.commission_agent_updated_success")
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
    public function destroy($id){
        if (!auth()->user()->can('user.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                User::where('id', $id)
                    ->where('business_id', $business_id)
                    ->where('is_cmmsn_agnt', 1)
                    ->delete();

                $output = ['success' => true,
                                'msg' => __("lang_v1.commission_agent_deleted_success")
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
    

    public function destroyProduct($id){
        if (!auth()->user()->can('user.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                DB::table('commision_agent_stock')->where('id', $id)
                    ->where('business_id', $business_id)
                    ->delete();

                $output = ['success' => true,
                                'msg' => __("lang_v1.commission_agent_deleted_success")
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
