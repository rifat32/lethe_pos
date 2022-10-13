<?php

namespace App\Http\Controllers;

use App\Transaction;

use Illuminate\Http\Request;

use App\BusinessLocation;
use App\Physical_stock_history;
use App\Product;
use App\PurchaseLine;

use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\ModuleUtil;

use Datatables;
use DB;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('purchase.view') && !auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $stock_adjustments = Transaction::join(
                'business_locations AS BL',
                'transactions.location_id',
                '=',
                'BL.id'
            )
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'stock_adjustment')
                    ->select(
                        'transactions.id',
                        'transaction_date',
                        'ref_no',
                        'BL.name as location_name',
                        'adjustment_type',
                        'final_total',
                        'total_amount_recovered',
                        'additional_notes',
                        'transactions.id as DT_RowId'
                    );

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $stock_adjustments->whereIn('transactions.location_id', $permitted_locations);
            }

            $hide = '';
            $start_date = request()->get('start_date');
            $end_date = request()->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $stock_adjustments->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
                $hide = 'hide';
            }
            $location_id = request()->get('location_id');
            if (!empty($location_id)) {
                $stock_adjustments->where('transactions.location_id', $location_id);
            }
            
            return Datatables::of($stock_adjustments)
                ->addColumn('action', '
                    <a type="button" href="{{action("StockAdjustmentController@edit", [$id]) }}" class="btn btn-success btn-xs"><i class="glyphicon glyphicon-edit"></i>
                    @lang("messages.edit")</a>

                    <button type="button" title="{{__("stock_adjustment.view_details") }}" class="btn btn-primary btn-xs view_stock_adjustment"><i class="fa fa-eye-slash" aria-hidden="true"></i></button> &nbsp;
                    <button type="button" data-href="{{  action("StockAdjustmentController@destroy", [$id]) }}" class="btn btn-danger btn-xs delete_stock_adjustment ' . $hide . '"><i class="fa fa-trash" aria-hidden="true"></i> @lang("messages.delete")</button>

                ')
                ->removeColumn('id')
                ->editColumn(
                    'final_total',
                    '<span class="display_currency" data-currency_symbol="true">{{$final_total}}</span>'
                )
                ->editColumn(
                    'total_amount_recovered',
                    '<span class="display_currency" data-currency_symbol="true">{{$total_amount_recovered}}</span>'
                )
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('adjustment_type', function ($row) {
                        return __('stock_adjustment.' . $row->adjustment_type);
                })
                ->rawColumns(['final_total', 'action', 'total_amount_recovered'])
                ->make(true);
        }

        return view('stock_adjustment.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('StockAdjustmentController@index'));
        }

        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('stock_adjustment.create')
                ->with(compact('business_locations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $input_data = $request->only([ 'location_id', 'transaction_date', 'adjustment_type', 'additional_notes', 'total_amount_recovered', 'final_total', 'ref_no']);
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('StockAdjustmentController@index'));
            }
        
            $user_id = $request->session()->get('user.id');

            $input_data['type'] = 'stock_adjustment';
            $input_data['business_id'] = $business_id;
            $input_data['created_by'] = $user_id;
            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date']);
            $input_data['total_amount_recovered'] = $this->productUtil->num_uf($input_data['total_amount_recovered']);

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount('stock_adjustment');
            //Generate reference number
            if (empty($input_data['ref_no'])) {
                $input_data['ref_no'] = $this->productUtil->generateReferenceNumber('stock_adjustment', $ref_count);
            }

            $products = $request->input('products');

            if (!empty($products)) {
                $product_data = [];

                foreach ($products as $product) {
                    $adjustment_line = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'quantity' => $this->productUtil->num_uf($product['quantity']),
                        'unit_price' => $this->productUtil->num_uf($product['unit_price'])
                    ];
                    if (!empty($product['lot_no_line_id'])) {
                        //Add lot_no_line_id to stock adjustment line
                        $adjustment_line['lot_no_line_id'] = $product['lot_no_line_id'];
                    }
                    $product_data[] = $adjustment_line;

                    //Decrease available quantity
                    /*$this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $input_data['location_id'],
                        $this->productUtil->num_uf($product['quantity'])
                    );*/
                    
                    if($request->input('adjustment_type') != 'normal'){                           
                        $this->productUtil->updateProductQuantity(
                            $input_data['location_id'],
                            $product['product_id'],
                            $product['variation_id'],
                            $this->productUtil->num_uf($product['quantity'])
                        );
                    }else{
                        $this->productUtil->decreaseProductQuantity(
                            $product['product_id'],
                            $product['variation_id'],
                            $input_data['location_id'],
                            $this->productUtil->num_uf($product['quantity'])
                        );
                        
                    }
                }

                $stock_adjustment = Transaction::create($input_data);
                $stock_adjustment->stock_adjustment_lines()->createMany($product_data);

                //Map Stock adjustment & Purchase.
                $business = ['id' => $business_id,
                                'accounting_method' => $request->session()->get('business.accounting_method'),
                                'location_id' => $input_data['location_id']
                            ];
                // $this->transactionUtil->mapPurchaseSell($business, $stock_adjustment->stock_adjustment_lines, 'stock_adjustment');
            }

            $output = ['success' => 1,
                            'msg' => __('stock_adjustment.stock_adjustment_added_successfully')
                        ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
        }

        return redirect('stock-adjustments')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('purchase.view')) {
            abort(403, 'Unauthorized action.');
        }

        $stock_adjustment_details = Transaction::
                    join(
                        'stock_adjustment_lines as sl',
                        'sl.transaction_id',
                        '=',
                        'transactions.id'
                    )
                    ->join('products as p', 'sl.product_id', '=', 'p.id')
                    ->join('variations as v', 'sl.variation_id', '=', 'v.id')
                    ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                    ->where('transactions.id', $id)
                    ->where('transactions.type', 'stock_adjustment')
                    ->leftjoin('purchase_lines as pl', 'sl.lot_no_line_id', '=', 'pl.id')
                    ->select(
                        'p.name as product',
                        'p.type as type',
                        'pv.name as product_variation',
                        'v.name as variation',
                        'v.sub_sku',
                        'sl.quantity',
                        'sl.unit_price',
                        'pl.lot_number',
                        'pl.exp_date'
                    )
                    ->groupBy('sl.id')
                    ->get();

        $lot_n_exp_enabled = false;
        if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
            $lot_n_exp_enabled = true;
        }

        return view('stock_adjustment.partials.details')
                ->with(compact('stock_adjustment_details', 'lot_n_exp_enabled'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Transaction  $stockAdjustment
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
         $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('StockAdjustmentController@index'));
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
       $stock_adjustment = Transaction::where('id', $id)
                                    ->where('type', 'stock_adjustment')
                                    ->with(['stock_adjustment_lines'])
                                    ->first();
       return view('stock_adjustment.edit')
                ->with(compact('stock_adjustment','business_locations'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Transaction  $stockAdjustment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $input_data = $request->only([ 'location_id', 'transaction_date', 'adjustment_type', 'additional_notes', 'total_amount_recovered', 'final_total', 'ref_no']);
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('StockAdjustmentController@index'));
            }
        
            $user_id = $request->session()->get('user.id');
            $input_data['type'] = 'stock_adjustment';
            $input_data['business_id'] = $business_id;
            $input_data['created_by'] = $user_id;
            $input_data['transaction_date'] = $this->productUtil->uf_date($input_data['transaction_date']);
            $input_data['total_amount_recovered'] = $this->productUtil->num_uf($input_data['total_amount_recovered']);

            //Update reference count
            $ref_count = $this->productUtil->setAndGetReferenceCount('stock_adjustment');
            //Generate reference number
            if (empty($input_data['ref_no'])) {
                $input_data['ref_no'] = $this->productUtil->generateReferenceNumber('stock_adjustment', $ref_count);
            }

            $products = $request->input('products');

            if (!empty($products)) {
                $product_data = [];

                foreach ($products as $product) {
                    $adjustment_line = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                        'type' => $product['type_'],
                        'quantity' => $this->productUtil->num_uf($product['quantity']),
                        'unit_price' => $this->productUtil->num_uf($product['unit_price'])
                    ];
                    if (!empty($product['lot_no_line_id'])) {
                        //Add lot_no_line_id to stock adjustment line
                        $adjustment_line['lot_no_line_id'] = $product['lot_no_line_id'];
                    }
                    $product_data[] = $adjustment_line;

                    //Decrease available quantity
                    $this->productUtil->decreaseProductQuantity3(
                        $product['product_id'],
                        $product['variation_id'],
                        $input_data['location_id'],
                        $this->productUtil->num_uf($product['quantity']),
                        $product['type_']
                    );
                }

                $stock_adjustment = Transaction::create($input_data);
                $stock_adjustment->stock_adjustment_lines()->createMany($product_data);

                //Map Stock adjustment & Purchase.
                $business = ['id' => $business_id,
                                'accounting_method' => $request->session()->get('business.accounting_method'),
                                'location_id' => $input_data['location_id']
                            ];
                $this->transactionUtil->mapPurchaseSell($business, $stock_adjustment->stock_adjustment_lines, 'stock_adjustment');
            }

            $output = ['success' => 1,
                            'msg' => __('stock_adjustment.stock_adjustment_added_successfully')
                        ];

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect('stock-adjustments')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       
        try {
            if (request()->ajax()) {
                DB::beginTransaction();

                $stock_adjustment = Transaction::where('id', $id)
                                    ->where('type', 'stock_adjustment')
                                    ->with(['stock_adjustment_lines'])
                                    ->first();

                //Add deleted product quantity to available quantity
                $stock_adjustment_lines = $stock_adjustment->stock_adjustment_lines;
                if (!empty($stock_adjustment_lines)) {
                    $line_ids = [];
                    foreach ($stock_adjustment_lines as $stock_adjustment_line) {
                        $this->productUtil->updateProductQuantity(
                            $stock_adjustment->location_id,
                            $stock_adjustment_line->product_id,
                            $stock_adjustment_line->variation_id,
                            $this->productUtil->num_f($stock_adjustment_line->quantity)
                        );
                        $line_ids[] = $stock_adjustment_line->id;
                    }

                    $this->transactionUtil->mapPurchaseQuantityForDeleteStockAdjustment($line_ids);
                }
                $stock_adjustment->delete();

                //Remove Mapping between stock adjustment & purchase.

                $output = ['success' => 1,
                            'msg' => __('stock_adjustment.delete_success')
                        ];

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }
        return $output;
    }

    /**
     * Return product rows
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getProductRow(Request $request)
    {
        if (request()->ajax()) {
            $row_index = $request->input('row_index');
            $variation_id = $request->input('variation_id');
            $location_id = $request->input('location_id');

            $business_id = $request->session()->get('user.business_id');
            $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id);
            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);

            //Get lot number dropdown if enabled
            $lot_numbers = [];
            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($variation_id, $business_id, $location_id, true);
                foreach ($lot_number_obj as $lot_number) {
                    $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                    $lot_numbers[] = $lot_number;
                }
            }
            $product->lot_numbers = $lot_numbers;
            
            return view('stock_adjustment.partials.product_table_row')
            ->with(compact('product', 'row_index'));
        }
    }

    /**
     * Sets expired purchase line as stock adjustmnet
     *
     * @param int $purchase_line_id
     * @return json $output
     */
    public function removeExpiredStock($purchase_line_id)
    {

        if (!auth()->user()->can('purchase.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $purchase_line = PurchaseLine::where('id', $purchase_line_id)
                                    ->with(['transaction'])
                                    ->first();

            if (!empty($purchase_line)) {
                DB::beginTransaction();

                $qty_unsold = $purchase_line->quantity - $purchase_line->quantity_sold - $purchase_line->quantity_adjusted - $purchase_line->quantity_returned;
                $final_total = $purchase_line->purchase_price_inc_tax * $qty_unsold;

                $user_id = request()->session()->get('user.id');
                $business_id = request()->session()->get('user.business_id');

                //Update reference count
                $ref_count = $this->productUtil->setAndGetReferenceCount('stock_adjustment');

                $stock_adjstmt_data = [
                    'type' => 'stock_adjustment',
                    'business_id' => $business_id,
                    'created_by' => $user_id,
                    'transaction_date' => \Carbon::now()->format('Y-m-d'),
                    'total_amount_recovered' => 0,
                    'location_id' => $purchase_line->transaction->location_id,
                    'adjustment_type' => 'normal',
                    'final_total' => $final_total,
                    'ref_no' => $this->productUtil->generateReferenceNumber('stock_adjustment', $ref_count)
                ];

                //Create stock adjustment transaction
                $stock_adjustment = Transaction::create($stock_adjstmt_data);

                $stock_adjustment_line = [
                    'product_id' => $purchase_line->product_id,
                    'variation_id' => $purchase_line->variation_id,
                    'quantity' => $qty_unsold,
                    'unit_price' => $purchase_line->purchase_price_inc_tax,
                    'removed_purchase_line' => $purchase_line->id
                ];

                //Create stock adjustment line with the purchase line
                $stock_adjustment->stock_adjustment_lines()->create($stock_adjustment_line);

                //Decrease available quantity
                $this->productUtil->decreaseProductQuantity(
                    $purchase_line->product_id,
                    $purchase_line->variation_id,
                    $purchase_line->transaction->location_id,
                    $qty_unsold
                );

                //Map Stock adjustment & Purchase.
                $business = ['id' => $business_id,
                                'accounting_method' => request()->session()->get('business.accounting_method'),
                                'location_id' => $purchase_line->transaction->location_id
                            ];
                $this->transactionUtil->mapPurchaseSell($business, $stock_adjustment->stock_adjustment_lines, 'stock_adjustment', false, $purchase_line->id);

                DB::commit();

                $output = ['success' => 1,
                            'msg' => __('lang_v1.stock_removed_successfully')
                        ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }
        return $output;
    }
    
    
    
    
    
    
    
    
    
       //22.07.2020
    
    public function multiProductPhysicalStock()
    {
        
        return view('stock_adjustment.physical_stock.multiple_physical_stock');
    }

    public function multiProductPhysicalStockAjaxSession(Request $request)
    {  
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //if ($request->ajax()) {
            $query = Product::where('products.business_id', $business_id)
                    ->leftjoin('units', 'products.unit_id', '=', 'units.id')
                    ->leftjoin('variation_location_details as vld', 'products.id', '=', 'vld.product_id')
                    ->leftjoin('variations as V', function ($join) {
                        $join->on('products.id', '=', 'V.product_id')
                            ->where('products.type', 'single');
                    });
            if($request->sku)
            {
                $query->where('products.sku','like','%'.$request->sku.'%')->orwhere('products.name','like','%'.$request->sku.'%');
                $product = $query->select(
                    // DB::raw("(SELECT SUM(quantity) FROM transaction_sell_lines LEFT JOIN transactions ON transaction_sell_lines.transaction_id=transactions.id WHERE transactions.status='final' $location_filter AND transaction_sell_lines.product_id=products.id) as total_sold"),
                
                    DB::raw("SUM(vld.qty_available) as stock"),
                    DB::raw("SUM(vld.qty_available * V.default_purchase_price) as subTotal"),
                   // DB::raw("SUM(vld.qty_available) as initial_stock"),
                    'sku',
                    'products.name as product',
                    'products.type',
                    'products.id as product_id',
                    'products.mrp_price',
                    'units.short_name as unit',
                    'products.enable_stock as enable_stock',
                    'products.id as DT_RowId',
                    'vld.*',
                    //'V.sell_price_inc_tax as unit_price'
                    'V.default_purchase_price as unit_price'
                )->groupBy('products.id')
                ->first();


                $physicalStockSession = session()->get('physicalStockSession'); 

                    $product = $product;
                    $id = $product->product_id;
                
                    if(isset($physicalStockSession[$id])) 
                    {
                        // if( $stock->quantity < ($saleCart[$id]['quantity'] + $quantity) ) continue;
                        //$physicalStockSession[$id]['quantity']++ ;
                        //$physicalStockSession[$id]['total_price'] = $physicalStockSession[$id]['quantity'] * $physicalStockSession[$id]['unit_price'];
                        session()->put('physicalStockSession', $physicalStockSession);
                    }else{
                        // if item not exist in sell then add to sell with quantity
                        $physicalStockSession[$id] = [
                        'id' => $id,
                        'name' => $product->product,
                        'sku' => $product->sku,
                        'unit_price' => $product->unit_price,
                        'current_stock' => $product->stock,
                        'balance' => '',
                        'physical_qty' => '',
                    ];
                        session()->put('physicalStockSession', $physicalStockSession);
                    }
                
                //====================================================================
                $view = view('stock_adjustment.physical_stock.multiple_physical_stock_render')->render(); 
                return response()->json([
                    'status' => true,
                    'data' => $view
                ]);
            }    
            $view = view('stock_adjustment.physical_stock.multiple_physical_stock_render')->render(); 
            return response()->json([
                'status' => false,
                'data' => $view
            ]);
    }


    public function multiProductPhysicalStockAjaxSessionSingel(Request $request)
    {  
            $physicalStockSession = session()->get('physicalStockSession'); 

                $id = $request->product_id;
                $physical_qty = $request->physical_qty;
            
                if(isset($physicalStockSession[$id])) 
                {
                    $physicalStockSession[$id]['physical_qty'] = $physical_qty;
                    $physicalStockSession[$id]['balance'] =  $physicalStockSession[$id]['current_stock'] - $physical_qty;
                    session()->put('physicalStockSession', $physicalStockSession);
                }else{
                    // if item not exist in sell then add to sell with quantity
                    //session()->put('physicalStockSession', $physicalStockSession);
                }
            
            //====================================================================
            $view = view('stock_adjustment.physical_stock.multiple_physical_stock_render')->render(); 
            return response()->json([
                'status' => true,
                'data' => $view
            ]);
            
        $view = view('stock_adjustment.physical_stock.multiple_physical_stock_render')->render(); 
        return response()->json([
            'status' => false,
            'data' => $view
        ]);
    }
    public function multiProductPhysicalStockAjaxSessionDefault(Request $request)
    { 
        $physicalStockSession = session()->get('physicalStockSession');
        
        
        if(is_array($physicalStockSession) and  (count($physicalStockSession) > 0) )
        {
            $view = view('stock_adjustment.physical_stock.multiple_physical_stock_render')->render(); 
            return response()->json([
                'status' => true,
                'data' => $view
            ]);
        }else{
            $view = view('stock_adjustment.physical_stock.multiple_physical_stock_render')->render(); 
            return response()->json([
                'status' => false,
                'data' => $view
            ]);
        }
            
    }

    public function multiProductPhysicalStockAjaxSessionSingelRemove(Request $request)
    {  
        $physicalStockSession = session()->has('physicalStockSession') ? session()->get('physicalStockSession')  :[];
		unset($physicalStockSession[$request->input('product_id')]);	
		session(['physicalStockSession'=>$physicalStockSession]);

        $view = view('stock_adjustment.physical_stock.multiple_physical_stock_render')->render(); 
        return response()->json([
            'status' => true,
            'data' => $view
        ]);
    }


    public function multiProductPhysicalStockPost(Request $request)
    {
        if($request->product_id){
            
            $date=$request->date ? $request->date.' '.date('h:i:s'):date('Y-m-d h:i:s');
            
            foreach($request->product_id as $key => $product)
            {
                $stock = new Physical_stock_history();
                $stock->physical_qty = $request->physical_qty[$key] != null?$request->physical_qty[$key]:00.00;
                $stock->current_stock = $request->current_stock[$key] != null?$request->current_stock[$key]:00.00;
                $stock->product_id = $request->product_id[$key];
                $stock->created_by = Auth::user()->id;
                $stock->created_at = $date;
                $stock->save();
            }
            session()->put('physicalStockSession', []);
            return back()->with('success','Physical Stock is Stored Successfully!!');
        }
       return back();
    }
    
    public function PhysicalStock(){
        $query =DB::table('physical_stock_histories as PSH')
                ->join('products as p','p.id','PSH.product_id')
                ->select('p.name','p.sku','PSH.*');
                if(request()->name !=''){
                    $query->where('p.name','like','%'.request()->name.'%')
                        ->orwhere('p.sku','like','%'.request()->name.'%');
                }
        $data['products']=$query->orderby('PSH.id','desc')->paginate(40);
        return view('stock_adjustment.physical_stock.index',$data);
    }
    
    public function PhysicalStockEdit($id){
        $item=Physical_stock_history::with('products')->find($id);
        return view('stock_adjustment.physical_stock.edit',compact('item'));
        
    }
    
    public function PhysicalStockupdate($id){
        Physical_stock_history::where('id',$id)->update(['physical_qty'=>request()->physical_qty]);
         return back()->with('success','Physical Stock is Update Successfully!!');
    }
    
     public function PhysicalStockDelete($id){

        DB::table('physical_stock_histories')->where('id',$id)->delete();
         return back()->with('success','Physical Stock is Delete Successfully!!');
    }


}
