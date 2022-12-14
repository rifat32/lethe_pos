<?php

namespace App\Http\Controllers;

use App\Transaction;

use Illuminate\Http\Request;

use App\BusinessLocation;
use App\PurchaseLine;
use App\Contact;

use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Utils\ModuleUtil;

use Datatables;
use DB;

class StockReturnController extends Controller
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

            $stock_return = Transaction::join(
                'business_locations AS BL','transactions.location_id','=','BL.id')
            ->Leftjoin('contacts AS CN','transactions.supplier_id','=','CN.id')
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'stock_return')
                    ->select(
                        'transactions.id',
                        'transaction_date',
                        'ref_no',
                        'BL.name as location_name',
                        'CN.name as supplier_name',
                        'final_total',
                        'total_amount_recovered',
                        'additional_notes',
                        'transactions.id as DT_RowId'
                    );

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $stock_return->whereIn('transactions.location_id', $permitted_locations);
            }

            $hide = '';
            $start_date = request()->get('start_date');
            $end_date = request()->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $stock_return->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
                $hide = 'hide';
            }
            $location_id = request()->get('location_id');
            if (!empty($location_id)) {
                $stock_return->where('transactions.location_id', $location_id);
            }
            
            return Datatables::of($stock_return)
                
                /*->addColumn('action', '<button type="button" title="{{__("stock_adjustment.view_details") }}" class="btn btn-primary btn-xs view_stock_return"><i class="fa fa-eye-slash" aria-hidden="true"></i></button> &nbsp;
                    
                    <button type="button" data-href="{{  action("StockReturnController@printInvoice", [$id]) }}" class="btn btn-info btn-xs print_stock_return ' . $hide . '"><i class="fa fa-print" aria-hidden="true"></i> @lang("messages.print")</button>

                     <button type="button" data-href="{{  action("StockReturnController@destroy", [$id]) }}" class="btn btn-danger btn-xs delete_stock_adjustment ' . $hide . '"><i class="fa fa-trash" aria-hidden="true"></i> @lang("messages.delete")</button>

                    ')
                    */
                    ->addColumn('action', '<button type="button" title="{{__("stock_adjustment.view_details") }}" class="btn btn-primary btn-xs view_stock_return"><i class="fa fa-eye-slash" aria-hidden="true"></i></button> &nbsp;
                    
                    <a href="{{  action("StockReturnController@printInvoiceNew", [$id]) }}" class="btn btn-info btn-xs btnPrint' . $hide . '"><i class="fa fa-print" aria-hidden="true"></i> @lang("messages.print")</a>

                     <button type="button" data-href="{{  action("StockReturnController@destroy", [$id]) }}" class="btn btn-danger btn-xs delete_stock_adjustment ' . $hide . '"><i class="fa fa-trash" aria-hidden="true"></i> @lang("messages.delete")</button>
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

        return view('stock_return.index');
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
        $contacts = Contact::where('type','supplier')->where('business_id',$business_id)->select('id','name')->get();

        return view('stock_return.create')
                ->with(compact('business_locations','contacts'));
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
            $input_data = $request->only([ 'location_id','contact_id', 'transaction_date', 'adjustment_type', 'additional_notes', 'total_amount_recovered', 'final_total', 'ref_no']);
            $business_id = $request->session()->get('user.business_id');

            //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse(action('StockAdjustmentController@index'));
            }
        
            $user_id = $request->session()->get('user.id');

            $input_data['type'] = 'stock_return';
            $input_data['supplier_id'] = $input_data['contact_id'];
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
                    $this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $input_data['location_id'],
                        $this->productUtil->num_uf($product['quantity'])
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

        return redirect('stock-return')->with('status', $output);
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
                    ->where('transactions.type', 'stock_return')
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

        return view('stock_return.partials.details')
                ->with(compact('stock_adjustment_details', 'lot_n_exp_enabled'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Transaction  $stockAdjustment
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $stockAdjustment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Transaction  $stockAdjustment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $stockAdjustment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('purchase.delete')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            if (request()->ajax()) {
                DB::beginTransaction();

                $stock_adjustment = Transaction::where('id', $id)
                                    ->where('type', 'stock_return')
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
            
            return view('stock_return.partials.product_table_row')
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



     public function printInvoice($id)
    {
            $business_id = request()->session()->get('user.business_id');
            
            $sell_transfer = Transaction::where('transactions.business_id', $business_id)
                                ->where('transactions.id', $id)
                                ->where('transactions.type', 'stock_return')
                                ->join('contacts','contacts.id','=','transactions.supplier_id')
                                ->select('transactions.*','contacts.name')
                                ->first();
                $products=DB::table('stock_adjustment_lines as TSL')
                        ->where('TSL.transaction_id',$id)
                        ->join('products','products.id','=','TSL.product_id')
                        ->select('TSL.*','products.name')
                        ->get();

            $output = ['success' => 1, 'receipt' => []];
            $output['receipt']['html_content'] = view('stock_return.print', compact('sell_transfer','products'))->render();

        return $output;
    }
    
    
     #------------------------------------
    #moinul
    public function printInvoiceNew($id)
    {
            $business_id = request()->session()->get('user.business_id');
            
            $sell_transfer = Transaction::where('transactions.business_id', $business_id)
                                ->where('transactions.id', $id)
                                ->where('transactions.type', 'stock_return')
                                ->join('contacts','contacts.id','=','transactions.supplier_id')
                                ->select('transactions.*','contacts.name')
                                ->first();
                $products=DB::table('stock_adjustment_lines as TSL')
                        ->where('TSL.transaction_id',$id)
                        ->join('products','products.id','=','TSL.product_id')
                        ->select('TSL.*','products.name')
                        ->get();

        return view('stock_return.printNew', compact('sell_transfer','products'));
    }
     #------------------------------------

}
