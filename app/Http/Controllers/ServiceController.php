<?php

namespace App\Http\Controllers;

use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Product;
use App\ProductVariation;
use App\Variation;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Unit;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
     /**
     * All Utils instance.
     *
     */
    protected $productUtil;

    private $barcode_types;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;

        //barcode types
        $this->barcode_types = $this->productUtil->barcode_types();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getAllServices()
    {
        if (request()->ajax()) {
            $term = request()->input('q', '');

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $products = Product::join('variations', 'products.id', '=', 'variations.product_id')

                ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')


                ->where("c1.name","=", "doctor" );




            if (!empty($term)) {
                $products->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term .'%');


                });
            }

            $products = $products->select(
                'products.id',
                'products.name as text',
                //  'products.discount as discount',
                //  'products.doctor_commission',
                // // 'products.shipping_price',
                // 'products.type',
                // 'c1.name as category',
                // 'c2.name as sub_category',

                // 'brands.name as brand',
                // 'tax_rates.name as tax',
                // 'variations.default_purchase_price',
                // 'variations.cost',
                // 'variations.sell_price_inc_tax',
                // 'products.sku',
                // 'products.image'
            )

                    ->get();




            return json_encode($products);
        }
    }

    public function index()
    {
        if (!auth()->user()->can('product.view') && !auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);

        if (request()->ajax()) {

            $products = Product::join('variations', 'products.id', '=', 'variations.product_id')
                /*->leftjoin(
                    'variation_location_details AS VLD',
                    function ($join) use ($location_id) {

                        $join->on('variations.id', '=', 'VLD.variation_id');

                    //Include Location
                        if (!empty($location_id)) {
                            $join->where(function ($query) use ($location_id) {
                                $query->where('VLD.location_id', '=', $location_id);
                                //Check null to show products even if no quantity is available in a location.
                                //TODO: Maybe add a settings to show product not available at a location or not.
                                $query->orWhereNull('VLD.location_id');
                            });
                                ;
                        }
                    }
                )*/
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')

                ->leftJoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
                ->leftJoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
                
                ->where('products.business_id', $business_id)
                // ->where('products.created_by', $user_id)
                ->where('products.type', '!=', 'modifier')
                ->where("c1.name","=", "doctor" )
                ->select(
                    'products.id',
                    'products.name as product',
                     'products.discount as discount',
                     'products.doctor_commission',
                    // 'products.shipping_price',
                    'products.type',
                    'c1.name as category',
                    'c2.name as sub_category',

                    'brands.name as brand',
                    'tax_rates.name as tax',
                    'variations.default_purchase_price',
                    'variations.cost',
                    'variations.sell_price_inc_tax',
                    'products.sku',
                    'products.image'
                );
            return datatables()::of($products)
                ->addColumn(
                    'action',
                    function ($row) use ($selling_price_group_count) {
                        $html =
                        '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">'. __("messages.actions") . '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <li><a href="' . action('LabelsController@show') . '?product_id=' . $row->id . '" data-toggle="tooltip" title="Print Barcode/Label"><i class="fa fa-barcode"></i> ' . __('barcode.labels') . '</a></li>';

                        if (auth()->user()->can('product.view')) {
                            $html .=
                            '<li><a href="' . action('ProductController@view', [$row->id]) . '" class="view-product"><i class="fa fa-eye"></i> ' . __("messages.view") . '</a></li>';
                        }

                        if (auth()->user()->can('product.update')) {
                            $html .=
                            '<li><a href="' . action('ServiceController@edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a></li>';
                        }

                        if (auth()->user()->can('product.delete')) {
                            $html .=
                            '<li><a href="' . action('ProductController@destroy', [$row->id]) . '" class="delete-product"><i class="fa fa-trash"></i> ' . __("messages.delete") . '</a></li>';
                        }

                        $html .= '<li class="divider"></li>';

                        if (auth()->user()->can('product.create')) {
                            $html .=
                            '<li><a href="#" data-href="' . action('OpeningStockController@add', ['product_id' => $row->id]) . '" class="add-opening-stock"><i class="fa fa-database"></i> ' . __("lang_v1.add_edit_opening_stock") . '</a></li>';
                            if ($selling_price_group_count > 0) {
                                $html .=
                                '<li><a href="' . action('ProductController@addSellingPrices', [$row->id]) . '"><i class="fa fa-money"></i> ' . __("lang_v1.add_selling_price_group_prices") . '</a></li>';
                            }

                            $html .=
                                '<li><a href="' . action('ProductController@create', ["d" => $row->id]) . '"><i class="fa fa-copy"></i> ' . __("lang_v1.duplicate_product") . '</a></li>';
                        }

                        $html .= '</ul></div>';

                        return $html;
                    }
                )->editColumn('tax', function ($row) {
                    $query =  DB::table('variation_location_details') ->select('qty_available')
                    ->where('product_id','=', $row->id);
                    $permitted_locations = auth()->user()->permitted_locations();
                    if ($permitted_locations != 'all') {
                        $query=$query->whereIn('variation_location_details.location_id', $permitted_locations);
                    }
                    $stocks=$query->get();
                    $flag = 0;
                    foreach ($stocks as $stock) {
                        $flag+=$stock->qty_available;
                    }
                    return  $flag;
                })
                ->editColumn('image', function ($row) {

                    return '<div style="display: flex;"><img src="' . asset($row->image_url) . '" alt="Product image" class="product-thumbnail-small"></div>';
                })
                ->addColumn('mass_delete', function ($row) {
                    if (auth()->user()->can("product.delete")) {
                        return  '<input type="checkbox" class="row-select" value="' . $row->id .'">' ;
                    } else {
                        return '';
                    }
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("product.view")) {
                            return  action('ProductController@view', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }])
                ->rawColumns(['action', 'image', 'mass_delete'])
                ->make(true);
        }

        $rack_enabled = (request()->session()->get('business.enable_racks') || request()->session()->get('business.enable_row') || request()->session()->get('business.enable_position'));


        return view('hospital.index')
            ->with(compact('rack_enabled'));
    }




    public function create()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }


        $business_id = request()->session()->get('user.business_id');
        $business = Business::findorfail($business_id);
        $profit_percent = $business->default_profit_percent;
        //Check if subscribed or not, then check for products quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('products', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('products', $business_id, action('ProductController@index'));
        }

        $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->pluck('name', 'id');
        $brands = Brands::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;
        $barcode_default =  $this->productUtil->barcode_default();

        $default_profit_percent = Business::where('id', $business_id)->value('default_profit_percent');

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        //Duplicate product
        $duplicate_product = null;
        $rack_details = null;

        $sub_categories = [];
        if (!empty(request()->input('d'))) {
            $duplicate_product = Product::where('business_id', $business_id)->find(request()->input('d'));
            $duplicate_product->name .= ' (copy)';

            if (!empty($duplicate_product->category_id)) {
                $sub_categories = Category::where('business_id', $business_id)
                        ->where('parent_id', $duplicate_product->category_id)

                        ->pluck('name', 'id')
                        ->toArray();
            }

            //Rack details
            if (!empty($duplicate_product->id)) {
                $rack_details = $this->productUtil->getRackDetails($business_id, $duplicate_product->id);
            }
        }

        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);


        return view('hospital.create')
            ->with(compact('categories', 'brands', 'units', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'barcode_default', 'business_locations', 'duplicate_product', 'sub_categories', 'rack_details', 'selling_price_group_count','profit_percent'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }



        try {
            $business_id = $request->session()->get('user.business_id');
            $product_details = $request->only(['name', 'brand_id', 'unit_id', 'category_id', 'tax', 'type','reseller_price','mrp_price', 'barcode_type', 'sku', 'alert_quantity', 'tax_type', 'weight', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_description',
            'discount','doctor_commission'
            // 'shipping_price'
        ]);
            $product_details['business_id'] = $business_id;
            $product_details['created_by'] = $request->session()->get('user.id');

            if (!empty($request->input('enable_stock')) &&  $request->input('enable_stock') == 1) {
                $product_details['enable_stock'] = 1 ;
            }

            if (!empty($request->input('sub_category_id'))) {
                $product_details['sub_category_id'] = $request->input('sub_category_id') ;
            }

            if (empty($product_details['sku'])) {
                $product_details['sku'] = ' ';
            }

            $expiry_enabled = $request->session()->get('business.enable_product_expiry');
            if (!empty($request->input('expiry_period_type')) && !empty($request->input('expiry_period')) && !empty($expiry_enabled) && ($product_details['enable_stock'] == 1)) {
                $product_details['expiry_period_type'] = $request->input('expiry_period_type');
                $product_details['expiry_period'] = $this->productUtil->num_uf($request->input('expiry_period'));
            }

            if (!empty($request->input('enable_sr_no')) &&  $request->input('enable_sr_no') == 1) {
                $product_details['enable_sr_no'] = 1 ;
            }

            //upload document
            if ($request->hasFile('image') && $request->file('image')->isValid()) {

                // dd()
                if ($request->image->getSize() <= config('constants.image_size_limit')) {
                    $new_file_name = time() . '_' . $request->image->getClientOriginalName();
                    $image_path = config('constants.product_img_path');
                    $path = $request->image->storeAs($image_path, $new_file_name);
                    if ($path) {
                        $product_details['image'] = $new_file_name;
                        // dd($product_details['image'] = $new_file_name);
                    }
                }
            }
            // dd($product_details);
            DB::beginTransaction();
            $product = Product::create($product_details);
            //start insert multiple images
            if($request->hasfile('images')){
                foreach($request->file('images') as $img){

                    $name=$img->getClientOriginalName();
                    $new_file_name = time() . '_' . $img->getClientOriginalName();
                    $image_path = config('constants.product_img_path');
                    $path = $img->storeAs($image_path, $new_file_name);
                    if ($path){
                        DB::table('product_images')
                        ->insert(['product_id'=>$product->id,'image'=>$new_file_name]);
                     }
                }
            }
            //end insert multiple images



            if (empty(trim($request->input('sku')))) {
                $sku = $this->productUtil->generateProductSku($product->id);
                $product->sku = $sku;
                $product->save();
            }

            if ($product->type == 'single') {
                $this->productUtil->createSingleProductVariation2($product->id, $product->sku, $request->input('single_dpp'), $request->input('single_dpp_inc_tax'), $request->input('profit_percent'), $request->input('single_dsp'), $request->input('single_dsp_inc_tax'),$request->input('cost'));
            } elseif ($product->type == 'variable') {
                if (!empty($request->input('product_variation'))) {
                    $input_variations = $request->input('product_variation');
                    $this->productUtil->createVariableProductVariations($product->id, $input_variations);
                }
            }

            //Add product racks details.
            $product_racks = $request->get('product_racks', null);
            if (!empty($product_racks)) {
                $this->productUtil->addRackDetails($business_id, $product->id, $product_racks);
            }




            DB::commit();
            $output = ['success' => 1,
                            'msg' => ('service  added')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
            return redirect('services')->with('status', $output);
        }

        if ($request->input('submit_type') == 'submit_n_add_opening_stock') {
            return redirect()->action(
                'OpeningStockController@add2',
                ['product_id' => $product->id]
            );
        } else if ($request->input('submit_type') == 'submit_n_add_selling_prices') {
            return redirect()->action(
                'ProductController@addSellingPrices',
                [$product->id]
            );
        } else if ($request->input('submit_type') == 'save_n_add_another') {
            return redirect()->action(
                'ServiceController@create'
            )->with('status', $output);
        }

        return redirect('services')->with('status', $output);
    }
    public function edit($id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->pluck('name', 'id');
        $brands = Brands::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;

        $product = Product::where('business_id', $business_id)
                            ->where('id', $id)
                            ->first();

        $sub_categories = [];

        $sub_categories = Category::where('business_id', $business_id)
                        ->where('parent_id', $product->category_id)
                        ->pluck('name', 'id')
                        ->toArray();

        $sub_categories = [ "" => "None"] + $sub_categories;

        $default_profit_percent = Business::where('id', $business_id)->value('default_profit_percent');

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);
        //Rack details
        $rack_details = $this->productUtil->getRackDetails($business_id, $id);

        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);

        return view('hospital.edit')
                ->with(compact('categories', 'brands', 'units', 'taxes', 'tax_attributes', 'barcode_types', 'product', 'sub_categories', 'default_profit_percent', 'business_locations', 'rack_details', 'selling_price_group_count'));
    }
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            $request->validate([
                'sku' => 'required|unique:products,sku, ' . $id,
            ]);

            $product_details = $request->only(['name', 'brand_id', 'unit_id', 'category_id', 'tax', 'barcode_type', 'sku', 'alert_quantity', 'tax_type','reseller_price','mrp_price', 'weight', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_description',
            'discount'
            // 'shipping_price',

        ]);


            DB::beginTransaction();

            $product = Product::where('business_id', $business_id)
                                ->where('id', $id)
                                ->with(['product_variations'])
                                ->first();
            $product->name = $product_details['name'];
            $product->brand_id = $product_details['brand_id'];
            $product->unit_id = $product_details['unit_id'];
            $product->category_id = $product_details['category_id'];
            $product->tax = $product_details['tax'];
            $product->barcode_type = $product_details['barcode_type'];
            $product->sku = $product_details['sku'];
            $product->alert_quantity = $product_details['alert_quantity'];
            $product->tax_type = $product_details['tax_type'];
            $product->reseller_price = $product_details['reseller_price'];
            $product->mrp_price = $product_details['mrp_price'];
            $product->weight = $product_details['weight'];
            $product->product_custom_field1 = $product_details['product_custom_field1'];
            $product->product_custom_field2 = $product_details['product_custom_field2'];
            $product->product_custom_field3 = $product_details['product_custom_field3'];
            $product->product_custom_field4 = $product_details['product_custom_field4'];
            $product->product_description = $product_details['product_description'];
            $product->discount = $product_details['discount'];
            // $product->shipping_price = $product_details['shipping_price'];

            if (!empty($request->input('enable_stock')) &&  $request->input('enable_stock') == 1) {
                $product->enable_stock = 1;
            } else {
                $product->enable_stock = 0;
            }
            if (!empty($request->input('sub_category_id'))) {
                $product->sub_category_id = $request->input('sub_category_id');
            } else {
                $product->sub_category_id = null;
            }

            $expiry_enabled = $request->session()->get('business.enable_product_expiry');
            if (!empty($expiry_enabled)) {
                if (!empty($request->input('expiry_period_type')) && !empty($request->input('expiry_period')) && ($product->enable_stock == 1)) {
                    $product->expiry_period_type = $request->input('expiry_period_type');
                    $product->expiry_period = $this->productUtil->num_uf($request->input('expiry_period'));
                } else {
                    $product->expiry_period_type = null;
                    $product->expiry_period = null;
                }
            }

            if (!empty($request->input('enable_sr_no')) &&  $request->input('enable_sr_no') == 1) {
                $product->enable_sr_no = 1;
            } else {
                $product->enable_sr_no = 0;
            }

            //upload document
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                if ($request->image->getSize() <= config('constants.image_size_limit')) {
                    $new_file_name = time() . '_' . $request->image->getClientOriginalName();
                    $image_path = config('constants.product_img_path');
                    $path = $request->image->storeAs($image_path, $new_file_name);
                    if ($path) {
                        $product->image = $new_file_name;
                    }
                }
            }

            $product->save();

            if ($product->type == 'single') {
                $single_data = $request->only(['single_variation_id', 'single_dpp', 'single_dpp_inc_tax','cost', 'single_dsp_inc_tax', 'profit_percent', 'single_dsp']);
                if(!empty($single_data)){
                   $variation = Variation::find($single_data['single_variation_id']);
                    $variation->sub_sku = $product->sku;
                    $variation->default_purchase_price = $this->productUtil->num_uf($single_data['single_dpp']);
                    $variation->dpp_inc_tax = $this->productUtil->num_uf($single_data['single_dpp_inc_tax']);
                    $variation->cost = $this->productUtil->num_uf($single_data['cost']);
                    $variation->profit_percent = $this->productUtil->num_uf($single_data['profit_percent']);
                    $variation->default_sell_price = $this->productUtil->num_uf($single_data['single_dsp']);
                    $variation->sell_price_inc_tax = $this->productUtil->num_uf($single_data['single_dsp_inc_tax']);
                    $variation->save();
                }
            } elseif ($product->type == 'variable') {
                //Update existing variations
                $input_variations_edit = $request->get('product_variation_edit');
                if (!empty($input_variations_edit)) {
                    $this->productUtil->updateVariableProductVariations($product->id, $input_variations_edit);
                }

                //Add new variations created.
                $input_variations = $request->input('product_variation');
                if (!empty($input_variations)) {
                    $this->productUtil->createVariableProductVariations($product->id, $input_variations);
                }
            }

            //Add product racks details.
            $product_racks = $request->get('product_racks', null);
            if (!empty($product_racks)) {
                $this->productUtil->addRackDetails($business_id, $product->id, $product_racks);
            }

            $product_racks_update = $request->get('product_racks_update', null);
            if (!empty($product_racks_update)) {
                $this->productUtil->updateRackDetails($business_id, $product->id, $product_racks_update);
            }

            DB::commit();
            $output = ['success' => 1,
                            'msg' => __('product.product_updated_success')
                        ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                            'msg' => __("Set A Unique Sku")
                        ];
        }

        if ($request->input('submit_type') == 'update_n_edit_opening_stock') {
            return redirect()->action(
                'OpeningStockController@add',
                ['product_id' => $product->id]
            );
        } else if ($request->input('submit_type') == 'submit_n_add_selling_prices') {
            return redirect()->action(
                'ProductController@addSellingPrices',
                [$product->id]
            );
        } else if ($request->input('submit_type') == 'save_n_add_another') {
            return redirect()->action(
                'ProductController@create'
            )->with('status', $output);
        }
        return redirect('services')->with('status', $output);
      
    }
}
