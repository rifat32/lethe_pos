<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Datatables;
use Charts;

use App\Utils\TransactionUtil;
use App\Utils\ProductUtil;

use App\Contact;
use App\Product;
use App\Category;
use App\Unit;
use App\Brands;
use App\BusinessLocation;
use App\ExpenseCategory;
use App\CashRegister;
use App\User;
use App\PurchaseLine;
use App\Transaction;
use App\CustomerGroup;
use App\Order2;
use App\TransactionSellLine;
use App\TransactionPayment;
use App\Restaurant\ResTable;
use App\SellingPriceGroup;
use App\Transaction_sell_line_update_delete_history;
use App\VariationLocationDetails;
use App\Transaction_update_delete_history;


use App\TaxRate;
use App\Physical_stock_history;

//use App\TransactionSellLine;
//use Yajra\DataTables\Facades\DataTables;

use App\Utils\ContactUtil;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;


class ReportController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $transactionUtil;
    protected $productUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ProductUtil $productUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->productUtil = $productUtil;
    }

    /**
     * Shows profit\loss of a business
     *
     * @return \Illuminate\Http\Response
     */


    public function sellUpdateTracking()
    {

        $data['transactions'] = Transaction_update_delete_history::where('action_type','Edit')->latest()->get();
        $data['ActionType'] = "Update";
        return view('report.sell_update_tracking',$data);//



        if (!auth()->user()->can('sell.view') && !auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->leftJoin(
                    'transactions AS SR',
                    'transactions.id',
                    '=',
                    'SR.return_parent_id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.is_direct_sale',
                    'transactions.invoice_no',
                    'contacts.name',
                    'transactions.payment_status',
                    'transactions.final_total',
                    DB::raw('SUM(IF(tp.is_return = 1,-1*tp.amount,tp.amount)) as total_paid'),
                    'bl.name as business_location',
                    DB::raw('COUNT(SR.id) as return_exists'),
                    DB::raw('(SELECT SUM(TP2.amount) FROM transaction_payments AS TP2 WHERE
                        TP2.transaction_id=SR.id ) as return_paid'),
                    DB::raw('COALESCE(SR.final_total, 0) as amount_return'),
                    'SR.id as return_transaction_id'
                );



            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            //Add condition for created_by,used in sales representative sales report
            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (!empty($created_by)) {
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            //Add condition for location,used in sales representative expense report
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }

            //Check is_direct sell
            if (request()->has('is_direct_sale')) {
                $is_direct_sale = request()->is_direct_sale;
                if ($is_direct_sale == 0) {
                    $sells->where('transactions.is_direct_sale', 0);
                }
            }

            //Add condition for commission_agent,used in sales representative sales with commission report
            if (request()->has('commission_agent')) {
                $commission_agent = request()->get('commission_agent');
                if (!empty($commission_agent)) {
                    $sells->where('transactions.commission_agent', $commission_agent);
                }
            }

            if ($this->moduleUtil->isModuleInstalled('Woocommerce')) {
                $sells->addSelect('transactions.woocommerce_order_id');
            }
            $sells->groupBy('transactions.id');

            if(!empty(request()->suspended)) {
                $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
                $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
                $with = ['sell_lines'];

                if($is_tables_enabled) {
                    $with[] = 'table';
                }

                if($is_service_staff_enabled) {
                    $with[] = 'service_staff';
                }

                $sales = $sells->where('transactions.is_suspend', 1)
                            ->with($with)
                            ->addSelect('transactions.is_suspend', 'transactions.res_table_id', 'transactions.res_waiter_id', 'transactions.additional_notes')
                            ->get();

                return view('sale_pos.partials.suspended_sales_modal')->with(compact('sales', 'is_tables_enabled', 'is_service_staff_enabled'));
            }

            return Datatables::of($sells)
                ->addColumn(
                    'action',
                    '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                        data-toggle="dropdown" aria-expanded="false">' .
                        __("messages.actions") .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    @if(auth()->user()->can("sell.view") || auth()->user()->can("direct_sell.access") )
                        <li><a href="#" data-href="{{action(\'SellController@show\', [$id])}}" class="btn-modal" data-container=".view_modal"><i class="fa fa-external-link" aria-hidden="true"></i> @lang("messages.view")</a></li>
                    @endif
                    @if($is_direct_sale == 0)
                        @can("sell.update")
                        <li><a target="_blank" href="{{action(\'SellPosController@edit\', [$id])}}"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a></li>
                        @endcan
                        @else
                        @can("direct_sell.access")
                            <li><a target="_blank" href="{{action(\'SellController@edit\', [$id])}}"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a></li>
                        @endcan
                    @endif
                    @can("sell.delete")
                    <li><a href="{{action(\'SellPosController@destroy\', [$id])}}" class="delete-sale"><i class="fa fa-trash"></i> @lang("messages.delete")</a></li>
                    @endcan

                    @if(auth()->user()->can("sell.view") || auth()->user()->can("direct_sell.access") )
                        <li><a href="#" class="print-invoice" data-href="{{route(\'sell.printInvoice\', [$id])}}"><i class="fa fa-print" aria-hidden="true"></i> @lang("messages.print")</a></li>
                    @endif

                    <li class="divider"></li>
                    @if($payment_status != "paid")
                        @if(auth()->user()->can("sell.create") || auth()->user()->can("direct_sell.access") )
                            <li><a href="{{action(\'TransactionPaymentController@addPayment\', [$id])}}" class="add_payment_modal"><i class="fa fa-money"></i> @lang("purchase.add_payment")</a></li>
                        @endif
                    @endif
                        <li><a href="{{action(\'TransactionPaymentController@show\', [$id])}}" class="view_payment_modal"><i class="fa fa-money"></i> @lang("purchase.view_payments")</a></li>
                    @can("sell.create")
                        <li><a href="{{action(\'SellController@duplicateSell\', [$id])}}"><i class="fa fa-copy"></i> @lang("lang_v1.duplicate_sell")</a></li>

                        <li><a href="{{action(\'SellReturnController@add\', [$id])}}"><i class="fa fa-undo"></i> @lang("lang_v1.sell_return")</a></li>
                    @endcan
                    @can("send_notification")
                        <li><a href="#" data-href="{{action(\'NotificationController@getTemplate\', ["transaction_id" => $id,"template_for" => "new_sale"])}}" class="btn-modal" data-container=".view_modal"><i class="fa fa-envelope" aria-hidden="true"></i> @lang("lang_v1.new_sale_notification")</a></li>
                    @endcan
                    </ul></div>'
                )
                ->removeColumn('id')
                ->editColumn(
                    'final_total',
                    '<span class="display_currency final-total" data-currency_symbol="true" data-orig-value="{{$final_total}}">{{$final_total}}</span>'
                )
                ->editColumn(
                    'total_paid',
                    '<span class="display_currency total-paid" data-currency_symbol="true" data-orig-value="{{$total_paid}}">{{$total_paid}}</span>'
                )
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn(
                    'payment_status',
                    '<a href="{{ action("TransactionPaymentController@show", [$id])}}" class="view_payment_modal payment-status-label" data-orig-value="{{$payment_status}}" data-status-name="{{__(\'lang_v1.\' . $payment_status)}}"><span class="label @payment_status($payment_status)">{{__(\'lang_v1.\' . $payment_status)}}
                        </span></a>'
                )
                ->addColumn('total_remaining', function ($row) {
                    $total_remaining =  $row->final_total - $row->total_paid;
                    $total_remaining_html = '<strong>' . __('lang_v1.sell_due') .':</strong> <span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $total_remaining . '">' . $total_remaining . '</span>';

                    if (!empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $total_remaining_html .= '<br><strong>' . __('lang_v1.sell_return_due') .':</strong> <a href="' . action("TransactionPaymentController@show", [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="display_currency sell_return_due" data-currency_symbol="true" data-orig-value="' . $return_due . '">' . $return_due . '</span></a>';
                    }
                    return $total_remaining_html;
                })
                 ->editColumn('invoice_no', function ($row) {
                    $invoice_no = $row->invoice_no;
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fa fa-wordpress text-primary" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }
                    if (!empty($row->return_exists)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round" title="' . __('lang_v1.some_qty_returned_from_sell') .'"><i class="fa fa-undo"></i></small>';
                    }

                    return $invoice_no;
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("sell.view")) {
                            return  action('SellController@show', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }])
                ->rawColumns(['final_total', 'action', 'total_paid', 'total_remaining', 'payment_status', 'invoice_no'])
                ->make(true);
        }
        return view('sell.index');

    }


    public function sellDeleteTracking()
    {

        $data['transactions'] = Transaction_update_delete_history::where('action_type','Delete')->latest()->get();
         $data['ActionType'] = "Delete";
        return view('report.sell_update_tracking',$data);//
    }


   public function sellUpdateTrackingProduct($id)
   {
        $data['transactions'] = Transaction_sell_line_update_delete_history::select('*',DB::raw('SUM(quantity*unit_price) as subtotal'))
                                        ->where('transaction_id',$id)->get();
        $data['total_amount']   = $data['transactions']->sum('subtotal');
        return view('report.sell_update_tracking_products',$data);//
   }


       //from here ======
    public function getPhysicalStockReportAajax(Request $request)
    {
        $data =  VariationLocationDetails::where('product_id',$request->product_id)->first();
        $data->physical_qty = $request->physical_qty;
        $data->save();
        return response()->json([
            'status' => true,
            'data' => $data->physical_qty,
        ]);
    }



    public function getPhysicalStockReport(Request $request)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        if(request()->start_date !=''){
            $from=request()->start_date.' 00:00:00';
        }else{
            $from=date('Y-m-d').' 00:00:00';
        }

        if(request()->end_date !=''){
            $end=request()->end_date.' 23:59:00';
        }else{
            $end=date('Y-m-d').' 23:59:00';
        }

        $query =DB::table('physical_stock_histories as PSH')
                ->join('products as p','p.id','PSH.product_id')
                ->select(array('p.*',
                    DB::raw('COUNT(PSH.product_id) as all_aid'),
                    DB::raw('SUM(PSH.physical_qty) as physical_qty'),
                    DB::raw('SUM(PSH.current_stock) as current_stock')
                ))
                ->whereBetween('PSH.created_at',[$from,$end]);

                if(request()->name !=''){
                    $query->where('p.name','Like','%'.request()->name.'%');
                }
        $data['products']=$query->groupBy('PSH.product_id')->get();

        $product_id=[];

        foreach($data['products'] as $product){

            $product_id[]=$product->id;
        }

        $s_query=VariationLocationDetails::join('products as p','p.id','variation_location_details.product_id')
                ->select('variation_location_details.*')
                ->with('product')->whereNotIn('product_id',$product_id)
                ->where('qty_available','>',0);
                if(request()->name !=''){
                    $s_query->where('p.name','Like','%'.request()->name.'%');
                }

        $data['stocks']=$s_query->get();
        $data['name']=request()->name?request()->name:'';
        $data['from']=$from;
        $data['end']=$end;
        $data['total']  = $data['products']->sum('physical_qty');
        $data['total_current_stock']  = $data['products']->sum('current_stock');
        return view('report.physical_stock_report',$data);
    }

    public function StockReportdetails(Request $request,$id)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query =DB::table('physical_stock_histories as PSH')
                ->join('products as p','p.id','PSH.product_id')
                ->select(array('p.name','p.sku','PSH.*'))
                ->where('PSH.product_id',$id);

                if(request()->start_date && request()->end_date !=''){
                    $query->whereBetween('PSH.created_at',[$from,$end]);
                }
        $data['products']=$query->get();
        return view('report.physical_report_details',$data);
    }

    public function getPhysicalStockReportPrint(Request $request,$from,$to,$name = NULL)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        $query = $query = Physical_stock_history::join('products','products.id','=','physical_stock_histories.product_id')->select('products.sku','products.name','physical_stock_histories.*');

       if(request()->start_date !=''){
            $from=request()->start_date.' 00:00:00';
        }else{
            $from=date('Y-m-d').' 00:00:00';
        }

        if(request()->end_date !=''){
            $end=request()->end_date.' 23:59:00';
        }else{
            $end=date('Y-m-d').' 23:59:00';
        }
        if($request->name)
        {
            $product =  Product::where('name','like','%'.$request->name.'%')->first();
            if($product)
            {
                $product_id = $product->id;
                $query->where('physical_stock_histories.product_id',$product_id);
            }
        }
        if($request->start_date)
        {
             $query->whereBetween('physical_stock_histories.created_at',[$from.' 00:00:00',$end.' 23:59:00']);
        }
        $data['products'] = $query->latest()->get();
        $data['total']  = $data['products']->sum('physical_qty');
        $data['total_current_stock']  = $data['products']->sum('current_stock');
        return view('report.physical_stock_report_print',$data);
        //==================================================
    }



    public function getProfitLoss(Request $request)
    {
        if (!auth()->user()->can('profit_loss_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            $location_id = $request->get('location_id');

            //For Opening stock date should be 1 day before
            $day_before_start_date = \Carbon::createFromFormat('Y-m-d', $start_date)->subDay()->format('Y-m-d');
            //Get Opening stock
            $opening_stock = $this->transactionUtil->getOpeningClosingStock($business_id, $day_before_start_date, $location_id, true);

            //Get Closing stock
            $closing_stock = $this->transactionUtil->getOpeningClosingStock(
                $business_id,
                $end_date,
                $location_id
            );

            //Get Purchase details
            $purchase_details = $this->transactionUtil->getPurchaseTotals(
                $business_id,
                $start_date,
                $end_date,
                $location_id
            );

            //Get Purchase Return details
            $purchase_return_details = $this->transactionUtil->getTotalPurchaseReturn(
                $business_id,
                $location_id,
                $start_date,
                $end_date
            );

            //Get Sell details
            $sell_details = $this->transactionUtil->getSellTotals(
                $business_id,
                $start_date,
                $end_date,
                $location_id
            );

            //Get Sell details
            $cost_details = $this->transactionUtil->getCostTotals(
                $business_id,
                $start_date,
                $end_date,
                $location_id
            );

            //Get Sell Return details
            $sell_return_details = $this->transactionUtil->getTotalSellReturn(
                $business_id,
                $location_id,
                $start_date,
                $end_date
            );
            //Internal Banking
              $total_internal_balance = $this->transactionUtil->getTotalInternalBanking(
                $business_id,
                $location_id,
                $start_date,
                $end_date
            );

            //Get total expense
            $total_expense = $this->transactionUtil->getTotalExpense(
                $business_id,
                $start_date,
                $end_date,
                $location_id
            );

            //Add total shipping charges to total expenses
            $total_expense += $sell_details['total_shipping_charges'];

            //Get total stock adjusted
            $total_stock_adjustment = $this->transactionUtil->getTotalStockAdjustment(
                $business_id,
                $start_date,
                $end_date,
                $location_id
            );

            $total_transfer_shipping_charges = $this->transactionUtil->getTotalTransferShippingCharges(
                $business_id,
                $start_date,
                $end_date,
                $location_id
            );

            //Discounts
            $total_purchase_discount = $this->transactionUtil->getTotalDiscounts($business_id, 'purchase', $start_date, $end_date, $location_id);
            $total_sell_discount = $this->transactionUtil->getTotalDiscounts($business_id, 'sell', $start_date, $end_date, $location_id);
            $data['invoice_due'] = !empty($sell_details['invoice_due']) ? $sell_details['invoice_due']: 0;


            $data['opening_stock'] = !empty($opening_stock) ? $opening_stock : 0;
            $data['closing_stock'] = !empty($closing_stock) ? $closing_stock : 0;
            $data['total_purchase'] = !empty($purchase_details['total_purchase_exc_tax']) ? $purchase_details['total_purchase_exc_tax'] : 0;
            $data['total_sell'] = !empty($sell_details['total_sell_inc_tax']) ? $sell_details['total_sell_inc_tax'] : 0;
            $data['total_expense'] = !empty($total_expense) ? $total_expense : 0;

            $data['total_cost'] = $cost_details ?? 0;

            $data['total_adjustment'] = !empty($total_stock_adjustment->total_adjustment) ? $total_stock_adjustment->total_adjustment : 0;

            $data['total_recovered'] = !empty($total_stock_adjustment->total_recovered) ? $total_stock_adjustment->total_recovered : 0;

            $data['total_transfer_shipping_charges'] = !empty($total_transfer_shipping_charges) ? $total_transfer_shipping_charges : 0;

            $data['total_purchase_discount'] = !empty($total_purchase_discount) ? $total_purchase_discount : 0;
            $data['total_sell_discount'] = !empty($total_sell_discount) ? $total_sell_discount : 0;

            $data['total_purchase_return'] = !empty($purchase_return_details['total_purchase_return_exc_tax']) ? $purchase_return_details['total_purchase_return_exc_tax'] : 0;

            $data['total_sell_return'] = !empty($sell_return_details['total_sell_return_exc_tax']) ? $sell_return_details['total_sell_return_exc_tax'] : 0;
            $data['total_internal_expense'] = !empty($total_internal_balance['total_internal_expense']) ? $total_internal_balance['total_internal_expense'] : 0;
            $data['total_internal_received'] = !empty($total_internal_balance['total_internal_received']) ? $total_internal_balance['total_internal_received'] : 0;
            $data['total_internal_balance'] = !empty($total_internal_balance['total_internal_balance']) ? $total_internal_balance['total_internal_balance'] : 0;
            $data['net_profit'] = $data['total_sell'] -$data['total_cost']
                             -  $data['total_expense']- $data['total_sell_return'];
            return $data;
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);
        return view('report.profit_loss', compact('business_locations'));
    }

    /**
     * Shows product report of a business
     *
     * @return \Illuminate\Http\Response
     */

    public function getPurchaseSellOnly(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $location_id = $request->get('location_id');

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start_date, $end_date, $location_id);

            $purchase_return_details = $this->transactionUtil->getTotalPurchaseReturn($business_id, $location_id, $start_date, $end_date);

            $sell_details = $this->transactionUtil->getSellTotals(
                $business_id,
                $start_date,
                $end_date,
                $location_id
            );

            $sell_return_details = $this->transactionUtil->getTotalSellReturn($business_id, $location_id, $start_date, $end_date);

            $total_purchase_return_inc_tax = !empty($purchase_return_details['total_purchase_return_inc_tax']) ? $purchase_return_details['total_purchase_return_inc_tax'] : 0;

            $total_sell_return_inc_tax = !empty($sell_return_details['total_sell_return_inc_tax']) ? $sell_return_details['total_sell_return_inc_tax'] : 0;

            $difference = [
                'total' => $sell_details['total_sell_inc_tax'] + $total_sell_return_inc_tax - $purchase_details['total_purchase_inc_tax'] - $total_purchase_return_inc_tax,
                'due' => $sell_details['invoice_due'] - $purchase_details['purchase_due']
            ];

            return ['purchase' => $purchase_details,
                    'sell' => $sell_details,
                    'total_purchase_return' => $total_purchase_return_inc_tax,
                    'total_sell_return' => $total_sell_return_inc_tax,
                    'difference' => $difference
                ];
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.purchase_sell_only')
                    ->with(compact('business_locations'));
    }



    public function getPurchaseSell(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $location_id = $request->get('location_id');

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start_date, $end_date, $location_id);

            $purchase_return_details = $this->transactionUtil->getTotalPurchaseReturn($business_id, $location_id, $start_date, $end_date);

            $sell_details = $this->transactionUtil->getSellTotals(
                $business_id,
                $start_date,
                $end_date,
                $location_id
            );

            $sell_return_details = $this->transactionUtil->getTotalSellReturn($business_id, $location_id, $start_date, $end_date);

            $total_purchase_return_inc_tax = !empty($purchase_return_details['total_purchase_return_inc_tax']) ? $purchase_return_details['total_purchase_return_inc_tax'] : 0;

            $total_sell_return_inc_tax = !empty($sell_return_details['total_sell_return_inc_tax']) ? $sell_return_details['total_sell_return_inc_tax'] : 0;

            $difference = [
                'total' => $sell_details['total_sell_inc_tax'] + $total_sell_return_inc_tax - $purchase_details['total_purchase_inc_tax'] - $total_purchase_return_inc_tax,
                'due' => $sell_details['invoice_due'] - $purchase_details['purchase_due']
            ];

            return ['purchase' => $purchase_details,
                    'sell' => $sell_details,
                    'total_purchase_return' => $total_purchase_return_inc_tax,
                    'total_sell_return' => $total_sell_return_inc_tax,
                    'difference' => $difference
                ];
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.purchase_sell')
                    ->with(compact('business_locations'));
    }

    /**
     * Shows report for Supplier
     *
     * @return \Illuminate\Http\Response
     */
    public function getCustomerSuppliers(Request $request)
    {
        if (!auth()->user()->can('customer-supplier.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $contacts = Contact::where('contacts.type','!=', "supplier")->where('contacts.business_id', $business_id)
                ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->groupBy('contacts.id')
                ->select(
                    DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_received"),
                    DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                    DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                    'contacts.supplier_business_name',
                    'contacts.name',
                    'contacts.id'
                );
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $contacts->whereIn('t.location_id', $permitted_locations);
            }
            return Datatables::of($contacts)
                ->editColumn('name', function ($row) {
                    $name = $row->name;
                    // if (!empty($row->supplier_business_name)) {
                    //     $name .= ', ' . $row->supplier_business_name;
                    // }
                    return '<a href="' . action('ContactController@show', [$row->id]) . '" target="_blank">' .
                            $name .
                        '</a>';
                })
                ->editColumn('total_purchase', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->total_purchase . '</span>';
                })
                ->editColumn('total_purchase_return', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->total_purchase_return . '</span>';
                })
                ->editColumn('total_sell_return', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->total_sell_return . '</span>';
                })
                ->editColumn('total_invoice', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->total_invoice . '</span>';
                })
                ->addColumn(
                    'due',
                    '<span class="display_currency" data-currency_symbol=true data-highlight=true>{{($total_invoice - $invoice_received - $total_sell_return + $sell_return_paid) - ($total_purchase - $total_purchase_return + $purchase_return_received - $purchase_paid) + ($opening_balance - $opening_balance_paid)}}</span>'
                )
                ->addColumn(
                    'opening_balance_due',
                    '<span class="display_currency" data-currency_symbol=true>{{$opening_balance - $opening_balance_paid}}</span>'
                )
                ->removeColumn('supplier_business_name')
                ->removeColumn('invoice_received')
                ->removeColumn('purchase_paid')
                ->removeColumn('id')
                ->rawColumns(['total_purchase', 'total_invoice', 'due', 'name', 'total_purchase_return', 'total_sell_return', 'opening_balance_due'])
                ->make(true);
        }

        return view('report.contact');
    }


    public function getSuppliers(Request $request)
    {
        if (!auth()->user()->can('report-supplier.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $contacts = Contact::where('contacts.type','!=', "customer")->where('contacts.business_id', $business_id)
                ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->groupBy('contacts.id')
                ->select(
                    DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    DB::raw("SUM(IF(t.type = 'stock_return', final_total, 0)) as total_stock_return"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_received"),
                    DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                    DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                    'contacts.supplier_business_name',
                    'contacts.name',
                    'contacts.id'
                );
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $contacts->whereIn('t.location_id', $permitted_locations);
            }
            return Datatables::of($contacts)
                ->editColumn('name', function ($row) {
                    // $name = $row->supplier_business_name;

                        $name = $row->name;

                    return '<a href="' . action('ContactController@show', [$row->id]) . '" target="_blank">' .
                            $name .
                        '</a>';
                })
                ->editColumn('total_purchase', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->total_purchase . '</span>';
                })
                ->editColumn('total_purchase_return', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->total_purchase_return . '</span>';
                })
                ->editColumn('total_sell_return', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->total_sell_return . '</span>';
                })
                ->editColumn('total_invoice', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->total_invoice . '</span>';
                })
                ->addColumn(
                    'due',
                    '<span class="display_currency" data-currency_symbol=true data-highlight=true>{{($total_invoice - $invoice_received - $total_sell_return + $sell_return_paid) - ($total_purchase - $total_purchase_return + $purchase_return_received - $purchase_paid) + ($opening_balance - $opening_balance_paid) +$total_stock_return}}</span>'
                )
                ->addColumn(
                    'opening_balance_due',
                    '<span class="display_currency" data-currency_symbol=true>{{$opening_balance - $opening_balance_paid}}</span>'
                )
                ->removeColumn('supplier_business_name')
                ->removeColumn('invoice_received')
                ->removeColumn('purchase_paid')
                ->removeColumn('id')
                ->rawColumns(['total_purchase', 'total_invoice', 'due', 'name', 'total_purchase_return', 'total_sell_return', 'opening_balance_due'])
                ->make(true);
        }

        return view('report.supplierReports');
    }

    /**
     * Shows product stock report
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockReport(Request $request)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        $selling_price_groups = SellingPriceGroup::where('business_id', $business_id)
                                                ->get();
        $allowed_selling_price_group = false;
        foreach ($selling_price_groups as $selling_price_group) {
            if (auth()->user()->can('selling_price_group.' . $selling_price_group->id)) {
                $allowed_selling_price_group = true;
                break;
            }
        }

         //Return the details in ajax call
        if ($request->ajax()) {
            $query = Product::where('products.business_id', $business_id)
                    ->leftjoin('units', 'products.unit_id', '=', 'units.id')
                    ->leftjoin('variation_location_details as vld', 'products.id', '=', 'vld.product_id')
                    ->leftjoin('variations as V', function ($join) {
                        $join->on('products.id', '=', 'V.product_id')
                            ->where('products.type', 'single');
                    });

            $permitted_locations = auth()->user()->permitted_locations();
            $location_filter = '';

            if ($permitted_locations != 'all') {
                $query->whereIn('vld.location_id', $permitted_locations);

                $locations_imploded = implode(', ', $permitted_locations);
                $location_filter .= "AND transactions.location_id IN ($locations_imploded) ";
            }

            if (!empty($request->input('location_id'))) {
                $location_id = $request->input('location_id');

                $query->where('vld.location_id', $location_id);

                $location_filter .= "AND transactions.location_id=$location_id";
            }

            if (!empty($request->input('category_id'))) {
                $query->where('products.category_id', $request->input('category_id'));
            }
            $supplier_id = $request->get('supplier_id', null);
            if (!empty($supplier_id)) {
                $location_filter .= " AND transactions.contact_id = $supplier_id";
            }
            if (!empty($request->input('sub_category_id'))) {
                $query->where('products.sub_category_id', $request->input('sub_category_id'));
            }
            if (!empty($request->input('brand_id'))) {
                $query->where('products.brand_id', $request->input('brand_id'));
            }
            if (!empty($request->input('unit_id'))) {
                $query->where('products.unit_id', $request->input('unit_id'));
            }

            $products = $query->select(
                // DB::raw("(SELECT SUM(quantity) FROM transaction_sell_lines LEFT JOIN transactions ON transaction_sell_lines.transaction_id=transactions.id WHERE transactions.status='final' $location_filter AND transaction_sell_lines.product_id=products.id) as total_sold"),

                DB::raw("(SELECT SUM(IF(transactions.type='sell', TSL.quantity - TSL.quantity_returned , -1* TPL.quantity) ) FROM transactions
                        LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id

                        LEFT JOIN purchase_lines AS TPL ON transactions.id=TPL.transaction_id

                        WHERE transactions.status='final' AND transactions.type='sell' $location_filter
                        AND (TSL.product_id=products.id OR TPL.product_id=products.id)) as total_sold"),
                DB::raw("(SELECT SUM(IF(transactions.type='sell_transfer', TSL.quantity, 0) ) FROM transactions
                        LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                        WHERE transactions.status='final' AND transactions.type='sell_transfer' $location_filter
                        AND (TSL.product_id=products.id)) as total_transfered"),
                DB::raw("(SELECT SUM(IF(transactions.type='stock_adjustment', SAL.quantity, 0) ) FROM transactions
                        LEFT JOIN stock_adjustment_lines AS SAL ON transactions.id=SAL.transaction_id
                        WHERE transactions.status='received' AND transactions.type='stock_adjustment' $location_filter
                        AND (SAL.product_id=products.id)) as total_adjusted"),
                DB::raw("SUM(vld.qty_available) as stock"),
               // DB::raw("SUM(vld.qty_available) as initial_stock"),
                'sku',
                'products.name',
                'products.type',
                'products.mrp_price',
                'units.short_name as unit',
                'products.enable_stock as enable_stock',
                'products.id as DT_RowId',
                'V.default_purchase_price',
                'V.sell_price_inc_tax',
            )->groupBy('products.id');

            return Datatables::of($products)
           
            ->editColumn(
                'sell_price_inc_tax',
                '<span class="display_currency sell-price-inc-tax" data-currency_symbol="true" data-orig-value="{{$sell_price_inc_tax}}">{{$sell_price_inc_tax}}</span>'
            )
                ->editColumn('stock', function ($row) {
                    if ($row->enable_stock) {
                        $stock = $row->stock ? $row->stock : 0 ;
                        return  '<span class="current_stock display_currency" data-orig-value="' . (float)$stock . '" data-unit="' . $row->unit . '" data-currency_symbol=false > ' . (float)$stock . '</span>' . ' ' . $row->unit ;
                    } else {
                        return 'N/A';
                    }
                })

                ->editColumn('total_sold', function ($row) {
                    $total_sold = 0;
                    if ($row->total_sold) {
                        $total_sold =  (float)$row->total_sold;
                    }

                    return '<span class="display_currency total_sold" data-currency_symbol=false data-orig-value="' . $total_sold . '" data-unit="' . $row->unit . '" >' . $total_sold . '</span> ' . $row->unit;
                })
                ->editColumn(
                    'mrp_price',
                    function ($row) {
                        return      '<span class="display_currency mrp-price" data-currency_symbol=true data-orig-value="' . $row->default_purchase_price * $row->stock . '" . >' . ($row->default_purchase_price * $row->stock) . '</span> ' ;
                    //    return  '<span class="display_currency mrp-price" data-currency_symbol="true" data-orig-value="{{$row->default_purchase_price * $row->stock}}">{{$row->default_purchase_price * $row->stock}}</span>';
                    }
                   
                )
                ->editColumn(
                    'mrp_sell_price',
                    function ($row) {
                        return      '<span class="display_currency mrp-sell-price" data-currency_symbol=true data-orig-value="' . $row->sell_price_inc_tax * $row->stock . '" . >' . ($row->sell_price_inc_tax * $row->stock) . '</span> ' ;
                    //    return  '<span class="display_currency mrp-price" data-currency_symbol="true" data-orig-value="{{$row->default_purchase_price * $row->stock}}">{{$row->default_purchase_price * $row->stock}}</span>';
                    }
                   
                )
                
                

                ->editColumn('initial_stock', function ($row) {

                        $initial_stock = $row->stock + $row->total_sold;
                        return  '<span class="initial_stock display_currency" data-orig-value="' . (float)$initial_stock . '" data-unit="' . $row->unit . '" data-currency_symbol=false > ' . (float)$initial_stock . '</span>' . ' ' . $row->unit ;

                })
                ->editColumn('total_transfered', function ($row) {
                    $total_transfered = 0;
                    if ($row->total_transfered) {
                        $total_transfered =  (float)$row->total_transfered;
                    }

                    return '<span class="display_currency total_transfered" data-currency_symbol=false data-orig-value="' . $total_transfered . '" data-unit="' . $row->unit . '" >' . $total_transfered . '</span> ' . $row->unit;
                })
                ->editColumn('total_adjusted', function ($row) {
                    $total_adjusted = 0;
                    if ($row->total_adjusted) {
                        $total_adjusted =  (float)$row->total_adjusted;
                    }

                    return '<span class="display_currency total_adjusted" data-currency_symbol=false  data-orig-value="' . $total_adjusted . '" data-unit="' . $row->unit . '" >' . $total_adjusted . '</span> ' . $row->unit;
                })
                ->editColumn(
                    'default_purchase_price',
                    '<span class="display_currency default-purchase-price" data-currency_symbol="true" data-orig-value="{{$default_purchase_price}}">{{$default_purchase_price}}</span>'
                )
                ->removeColumn('enable_stock')
                ->removeColumn('unit')
                ->removeColumn('id')
                ->rawColumns(['default_purchase_price', 'total_transfered', 'total_sold',
                    'total_adjusted', 'stock','initial_stock',"sell_price_inc_tax","mrp_price","mrp_sell_price"])
                ->make(true);
        }

        $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->pluck('name', 'id');
        $brands = Brands::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);
        $suppliers = Contact::suppliersDropdown($business_id);

        return view('report.stock_report')
                ->with(compact('categories', 'brands', 'units', 'business_locations', 'suppliers'));
    }

    /**
     * Shows product stock report
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockAlertReport(Request $request)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        $selling_price_groups = SellingPriceGroup::where('business_id', $business_id)
                                                ->get();
        $allowed_selling_price_group = false;
        foreach ($selling_price_groups as $selling_price_group) {
            if (auth()->user()->can('selling_price_group.' . $selling_price_group->id)) {
                $allowed_selling_price_group = true;
                break;
            }
        }

         //Return the details in ajax call
        if ($request->ajax()) {
            $query = Product::where('products.business_id', $business_id)
                    ->leftjoin('units', 'products.unit_id', '=', 'units.id')
                    ->leftjoin('variation_location_details as vld', 'products.id', '=', 'vld.product_id')
                    ->leftjoin('variations as V', function ($join) {
                        $join->on('products.id', '=', 'V.product_id')
                            ->where('products.type', 'single');
                    });

            $permitted_locations = auth()->user()->permitted_locations();
            $location_filter = '';

            if ($permitted_locations != 'all') {
                $query->whereIn('vld.location_id', $permitted_locations);

                $locations_imploded = implode(', ', $permitted_locations);
                $location_filter .= "AND transactions.location_id IN ($locations_imploded) ";
            }

            if (!empty($request->input('location_id'))) {
                $location_id = $request->input('location_id');

                $query->where('vld.location_id', $location_id);

                $location_filter .= "AND transactions.location_id=$location_id";
            }

            if (!empty($request->input('category_id'))) {
                $query->where('products.category_id', $request->input('category_id'));
            }
            if (!empty($request->input('sub_category_id'))) {
                $query->where('products.sub_category_id', $request->input('sub_category_id'));
            }
            if (!empty($request->input('brand_id'))) {
                $query->where('products.brand_id', $request->input('brand_id'));
            }
            if (!empty($request->input('unit_id'))) {
                $query->where('products.unit_id', $request->input('unit_id'));
            }

            $products = $query->select(
                // DB::raw("(SELECT SUM(quantity) FROM transaction_sell_lines LEFT JOIN transactions ON transaction_sell_lines.transaction_id=transactions.id WHERE transactions.status='final' $location_filter AND
                //     transaction_sell_lines.product_id=products.id) as total_sold"),

                DB::raw("(SELECT SUM(IF(transactions.type='sell', TSL.quantity - TSL.quantity_returned , -1* TPL.quantity) ) FROM transactions
                        LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id

                        LEFT JOIN purchase_lines AS TPL ON transactions.id=TPL.transaction_id

                        WHERE transactions.status='final' AND transactions.type='sell' $location_filter
                        AND (TSL.product_id=products.id OR TPL.product_id=products.id)) as total_sold"),
                DB::raw("(SELECT SUM(IF(transactions.type='sell_transfer', TSL.quantity, 0) ) FROM transactions
                        LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                        WHERE transactions.status='final' AND transactions.type='sell_transfer' $location_filter
                        AND (TSL.product_id=products.id)) as total_transfered"),
                DB::raw("(SELECT SUM(IF(transactions.type='stock_adjustment', SAL.quantity, 0) ) FROM transactions
                        LEFT JOIN stock_adjustment_lines AS SAL ON transactions.id=SAL.transaction_id
                        WHERE transactions.status='received' AND transactions.type='stock_adjustment' $location_filter
                        AND (SAL.product_id=products.id)) as total_adjusted"),
                DB::raw("SUM(vld.qty_available) as stock"),
               // DB::raw("SUM(vld.qty_available) as initial_stock"),
                'sku',
                'products.name as product',
                'products.type',
                'products.mrp_price',
                'products.mrp_sell_price',
                'units.short_name as unit',
                'products.enable_stock as enable_stock',
                'products.id as DT_RowId',
                //'V.sell_price_inc_tax as unit_price'
                'V.default_purchase_price as unit_price',
                'products.alert_quantity'
            )->groupBy('products.id');

            //$query->where('products.alert_quantity', 'vld.qty_available');

            return Datatables::of($products)
                ->editColumn('stock', function ($row) {
                    if ($row->enable_stock) {
                        $stock = $row->stock ? $row->stock : 0 ;
                        return  '<span class="current_stock display_currency" data-orig-value="' . (float)$stock . '" data-unit="' . $row->unit . '" data-currency_symbol=false > ' . (float)$stock . '</span>' . ' ' . $row->unit ;
                    } else {
                        return 'N/A';
                    }
                })

                ->editColumn('total_sold', function ($row) {
                    $total_sold = 0;
                    if ($row->total_sold) {
                        $total_sold =  (float)$row->total_sold;
                    }

                    return '<span class="display_currency total_sold" data-currency_symbol=false data-orig-value="' . $total_sold . '" data-unit="' . $row->unit . '" >' . $total_sold . '</span> ' . $row->unit;
                })

                ->editColumn('initial_stock', function ($row) {

                        $initial_stock = $row->stock + $row->total_sold;
                        return  '<span class="initial_stock display_currency" data-orig-value="' . (float)$initial_stock . '" data-unit="' . $row->unit . '" data-currency_symbol=false > ' . (float)$initial_stock . '</span>' . ' ' . $row->unit ;

                })
                ->editColumn('total_transfered', function ($row) {
                    $total_transfered = 0;
                    if ($row->total_transfered) {
                        $total_transfered =  (float)$row->total_transfered;
                    }

                    return '<span class="display_currency total_transfered" data-currency_symbol=false data-orig-value="' . $total_transfered . '" data-unit="' . $row->unit . '" >' . $total_transfered . '</span> ' . $row->unit;
                })
                ->editColumn('total_adjusted', function ($row) {
                    $total_adjusted = 0;
                    if ($row->total_adjusted) {
                        $total_adjusted =  (float)$row->total_adjusted;
                    }

                    return '<span class="display_currency total_adjusted" data-currency_symbol=false  data-orig-value="' . $total_adjusted . '" data-unit="' . $row->unit . '" >' . $total_adjusted . '</span> ' . $row->unit;
                })
                ->editColumn('unit_price', function ($row) use ($allowed_selling_price_group) {
                    $html = '';
                    if ($row->type == 'single' && auth()->user()->can('access_default_selling_price')) {
                        $html .= '<span class="display_currency" data-currency_symbol=true >'
                        . $row->unit_price . '</span>';
                    }

                    if ($allowed_selling_price_group) {
                        $html .= ' <button type="button" class="btn btn-primary btn-xs btn-modal no-print" data-container=".view_modal" data-href="' . action('ProductController@viewGroupPrice', [$row->DT_RowId]) .'">' . __('lang_v1.view_group_prices') . '</button>';
                    }

                    return $html;
                })
                ->removeColumn('enable_stock')
                ->removeColumn('unit')
                ->removeColumn('id')
                ->rawColumns(['total_transfered', 'total_sold',
                    'total_adjusted', 'stock','initial_stock'])
                ->make(true);
        }

        $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->pluck('name', 'id');
        $brands = Brands::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.stock_alert_report')
                ->with(compact('categories', 'brands', 'units', 'business_locations'));
    }

    /**
     * Shows product stock details
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockDetails(Request $request)
    {
         //Return the details in ajax call
        if ($request->ajax()) {
            $business_id = $request->session()->get('user.business_id');
            $product_id = $request->input('product_id');
            $query = Product::leftjoin('units as u', 'products.unit_id', '=', 'u.id')
                ->join('variations as v', 'products.id', '=', 'v.product_id')
                ->join('product_variations as pv', 'pv.id', '=', 'v.product_variation_id')
                ->leftjoin('variation_location_details as vld', 'v.id', '=', 'vld.variation_id')
                ->where('products.business_id', $business_id)
                ->where('products.id', $product_id);

            $permitted_locations = auth()->user()->permitted_locations();
            $location_filter = '';
            if ($permitted_locations != 'all') {
                $query->whereIn('vld.location_id', $permitted_locations);
                $locations_imploded = implode(', ', $permitted_locations);
                $location_filter .= "AND transactions.location_id IN ($locations_imploded) ";
            }

            if (!empty($request->input('location_id'))) {
                $location_id = $request->input('location_id');

                $query->where('vld.location_id', $location_id);

                $location_filter .= "AND transactions.location_id=$location_id";
            }

            $product_details =  $query->select(
                'products.name as product',
                'u.short_name as unit',
                'pv.name as product_variation',
                'v.name as variation',
                'v.sub_sku as sub_sku',
                'v.sell_price_inc_tax',
                DB::raw("SUM(vld.qty_available) as stock"),
                DB::raw("(SELECT SUM(IF(transactions.type='sell', TSL.quantity - TSL.quantity_returned, -1* TPL.quantity) ) FROM transactions
                        LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id

                        LEFT JOIN purchase_lines AS TPL ON transactions.id=TPL.transaction_id

                        WHERE transactions.status='final' AND transactions.type='sell' $location_filter
                        AND (TSL.variation_id=v.id OR TPL.variation_id=v.id)) as total_sold"),
                DB::raw("(SELECT SUM(IF(transactions.type='sell_transfer', TSL.quantity, 0) ) FROM transactions
                        LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id
                        WHERE transactions.status='final' AND transactions.type='sell_transfer' $location_filter
                        AND (TSL.variation_id=v.id)) as total_transfered"),
                DB::raw("(SELECT SUM(IF(transactions.type='stock_adjustment', SAL.quantity, 0) ) FROM transactions
                        LEFT JOIN stock_adjustment_lines AS SAL ON transactions.id=SAL.transaction_id
                        WHERE transactions.status='received' AND transactions.type='stock_adjustment' $location_filter
                        AND (SAL.variation_id=v.id)) as total_adjusted")
                // DB::raw("(SELECT SUM(quantity) FROM transaction_sell_lines LEFT JOIN transactions ON transaction_sell_lines.transaction_id=transactions.id WHERE transactions.status='final' $location_filter AND
                //     transaction_sell_lines.variation_id=v.id) as total_sold")
            )
                        ->groupBy('v.id')
                        ->get();
            //$initial_stock=$product_details->stock+$product_details->total_sold;
            //dd($initial_stock);
            return view('report.stock_details')
                        ->with(compact('product_details'));
        }
    }

    /**
     * Shows tax report of a business
     *
     * @return \Illuminate\Http\Response
     */
    public function getTaxReport(Request $request)
    {
        if (!auth()->user()->can('tax_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            $location_id = $request->get('location_id');

            $input_tax_details = $this->transactionUtil->getInputTax($business_id, $start_date, $end_date, $location_id);

            $input_tax = view('report.partials.tax_details')->with(['tax_details' => $input_tax_details])->render();

            $output_tax_details = $this->transactionUtil->getOutputTax($business_id, $start_date, $end_date, $location_id);

            $output_tax = view('report.partials.tax_details')->with(['tax_details' => $output_tax_details])->render();

            return ['input_tax' => $input_tax,
                    'output_tax' => $output_tax,
                    'tax_diff' => $output_tax_details['total_tax'] - $input_tax_details['total_tax']
                ];
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.tax_report')
                    ->with(compact('business_locations'));
    }

    /**
     * Shows trending products
     *
     * @return \Illuminate\Http\Response
     */
    public function getTrendingProducts(Request $request)
    {
        if (!auth()->user()->can('trending_product_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $filters = $request->only(['category', 'sub_category', 'brand', 'unit', 'limit', 'location_id']);

        $date_range = $request->input('date_range');

        if (!empty($date_range)) {
            $date_range_array = explode('~', $date_range);
            $filters['start_date'] = $this->transactionUtil->uf_date(trim($date_range_array[0]));
            $filters['end_date'] = $this->transactionUtil->uf_date(trim($date_range_array[1]));
        }

        $products = $this->productUtil->getTrendingProducts($business_id, $filters);

        $values = [];
        $labels = [];
        foreach ($products as $product) {
            $values[] = $product->total_unit_sold;
            $labels[] = $product->product . ' (' . $product->unit . ')';
        }

        $chart = Charts::create('bar', 'highcharts')
            ->title(" ")
            ->dimensions(0, 400)
            ->template("material")
            ->values($values)
            ->labels($labels)
            ->elementLabel(__('report.total_unit_sold'));

        $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->pluck('name', 'id');
        $brands = Brands::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.trending_products')
                    ->with(compact('chart', 'categories', 'brands', 'units', 'business_locations'));
    }

    /**
     * Shows expense report of a business
     *
     * @return \Illuminate\Http\Response
     */
    public function getExpenseReport(Request $request)
    {
        if (!auth()->user()->can('expense_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        $filters = $request->only(['category', 'location_id']);

        $date_range = $request->input('date_range');

        if (!empty($date_range)) {
            $date_range_array = explode('~', $date_range);
            $filters['start_date'] = $this->transactionUtil->uf_date(trim($date_range_array[0]));
            $filters['end_date'] = $this->transactionUtil->uf_date(trim($date_range_array[1]));
        } else {
            $filters['start_date'] = \Carbon::now()->startOfMonth()->format('Y-m-d');
            $filters['end_date'] = \Carbon::now()->endOfMonth()->format('Y-m-d');
        }

        $expenses = $this->transactionUtil->getExpenseReport($business_id, $filters);

        $values = [];
        $labels = [];
        foreach ($expenses as $expense) {
            $values[] = $expense->total_expense;
            $labels[] = !empty($expense->category) ? $expense->category : __('report.others');
        }

        $chart = Charts::create('bar', 'highcharts')
            ->title(__('report.expense_report'))
            ->dimensions(0, 400)
            ->template("material")
            ->values($values)
            ->labels($labels)
            ->elementLabel(__('report.total_expense'));

        $categories = ExpenseCategory::where('business_id', $business_id)
                            ->pluck('name', 'id');

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.expense_report')
                    ->with(compact('chart', 'categories', 'business_locations'));
    }

    /**
     * Shows stock adjustment report
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockAdjustmentReport(Request $request)
    {

        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $query =  Transaction::where('business_id', $business_id)
                            ->where('type', 'stock_adjustment');

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('location_id', $permitted_locations);
            }

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }
            $location_id = $request->get('location_id');
            if (!empty($location_id)) {
                $query->where('location_id', $location_id);
            }

            $stock_adjustment_details = $query->select(
                DB::raw("SUM(final_total) as total_amount"),
                DB::raw("SUM(total_amount_recovered) as total_recovered"),
                DB::raw("SUM(IF(adjustment_type = 'normal', final_total, 0)) as total_normal"),
                DB::raw("SUM(IF(adjustment_type = 'abnormal', final_total, 0)) as total_abnormal")
            )->first();
            return $stock_adjustment_details;
        }
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.stock_adjustment_report')
                    ->with(compact('business_locations'));
    }

    /**
     * Shows register report of a business
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegisterReport(Request $request)
    {
        if (!auth()->user()->can('register_report.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $registers = CashRegister::join(
                'users as u',
                'u.id',
                '=',
                'cash_registers.user_id'
            )
                        ->where('cash_registers.business_id', $business_id)
                        ->select(
                            'cash_registers.*',
                            DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, ''), '<br>', COALESCE(email, '')) as user_name")
                        );

            if (!empty($request->input('user_id'))) {
                $registers->where('cash_registers.user_id', $request->input('user_id'));
            }
            if (!empty($request->input('status'))) {
                $registers->where('cash_registers.status', $request->input('status'));
            }
            return Datatables::of($registers)
                ->editColumn('total_card_slips', function ($row) {
                    if ($row->status == 'close') {
                        return $row->total_card_slips;
                    } else {
                        return '';
                    }
                })
                ->editColumn('total_cheques', function ($row) {
                    if ($row->status == 'close') {
                        return $row->total_cheques;
                    } else {
                        return '';
                    }
                })
                ->editColumn('closed_at', function ($row) {
                    if ($row->status == 'close') {
                        return $this->productUtil->format_date($row->closed_at, true);
                    } else {
                        return '';
                    }
                })
                ->editColumn('created_at', function ($row) {
                     return $this->productUtil->format_date($row->created_at, true);
                })
                ->editColumn('closing_amount', function ($row) {
                    if ($row->status == 'close') {
                        return '<span class="display_currency" data-currency_symbol="true">' .
                        $row->closing_amount . '</span>';
                    } else {
                        return '';
                    }
                })
                ->addColumn('action', '<button type="button" data-href="{{action(\'CashRegisterController@show\', [$id])}}" class="btn btn-xs btn-info btn-modal"
                    data-container=".view_register"><i class="fa fa-external-link" aria-hidden="true"></i> @lang("messages.view")</button>')
                ->filterColumn('user_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, ''), '<br>', COALESCE(email, '')) like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['action', 'user_name', 'closing_amount'])
                ->make(true);
        }

        $users = User::forDropdown($business_id, false);

        return view('report.register_report')
                    ->with(compact('users'));
    }

    /**
     * Shows sales representative report
     *
     * @return \Illuminate\Http\Response
     */
    public function getSalesRepresentativeReport(Request $request)
    {

        if (!auth()->user()->can('sales_representative.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        $users = User::allUsersDropdown($business_id, false);
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.sales_representative')
                ->with(compact('users', 'business_locations'));
    }

    /**
     * Shows sales representative total expense
     *
     * @return json
     */
    public function getSalesRepresentativeTotalExpense(Request $request)
    {

        if (!auth()->user()->can('sales_representative.view')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $business_id = $request->session()->get('user.business_id');

            $filters = $request->only(['expense_for', 'location_id', 'start_date', 'end_date']);

            $total_expense = $this->transactionUtil->getExpenseReport($business_id, $filters, 'total');

            return $total_expense;
        }
    }

    /**
     * Shows sales representative total sales
     *
     * @return json
     */
    public function getSalesRepresentativeTotalSell(Request $request)
    {
        if (!auth()->user()->can('sales_representative.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $location_id = $request->get('location_id');
            $created_by = $request->get('created_by');

            $sell_details = $this->transactionUtil->getSellTotals($business_id, $start_date, $end_date, $location_id, $created_by);

            return ['total_sell_exc_tax' => $sell_details['total_sell_exc_tax']];
        }
    }

    /**
     * Shows sales representative total commission
     *
     * @return json
     */
    public function getSalesRepresentativeTotalCommission(Request $request)
    {
        if (!auth()->user()->can('sales_representative.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $location_id = $request->get('location_id');
            $commission_agent = $request->get('commission_agent');

            $sell_details = $this->transactionUtil->getTotalSellCommission($business_id, $start_date, $end_date, $location_id, $commission_agent);

            //Get Commision
            $commission_percentage = User::find($commission_agent)->cmmsn_percent;
            $total_commission = $commission_percentage * $sell_details['total_sales_with_commission'] / 100;

            return ['total_sales_with_commission' =>
                        $sell_details['total_sales_with_commission'],
                    'total_commission' => $total_commission,
                    'commission_percentage' => $commission_percentage
                ];
        }
    }

    /**
     * Shows product stock expiry report
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockExpiryReport(Request $request)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $query = PurchaseLine::leftjoin(
                'transactions as t',
                'purchase_lines.transaction_id',
                '=',
                't.id'
            )
                            ->leftjoin(
                                'products as p',
                                'purchase_lines.product_id',
                                '=',
                                'p.id'
                            )
                            ->leftjoin(
                                'variations as v',
                                'purchase_lines.variation_id',
                                '=',
                                'v.id'
                            )
                            ->leftjoin(
                                'product_variations as pv',
                                'v.product_variation_id',
                                '=',
                                'pv.id'
                            )
                            ->leftjoin('business_locations as l', 't.location_id', '=', 'l.id')
                            ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                            ->where('t.business_id', $business_id)
                            //->whereNotNull('p.expiry_period')
                            //->whereNotNull('p.expiry_period_type')
                            ->whereNotNull('exp_date')
                            ->where('p.enable_stock', 1)
                            ->whereRaw('purchase_lines.quantity > purchase_lines.quantity_sold + quantity_adjusted + quantity_returned');

            $permitted_locations = auth()->user()->permitted_locations();

            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($request->input('location_id'))) {
                $location_id = $request->input('location_id');
                $query->where('t.location_id', $location_id);
            }

            if (!empty($request->input('category_id'))) {
                $query->where('p.category_id', $request->input('category_id'));
            }
            if (!empty($request->input('sub_category_id'))) {
                $query->where('p.sub_category_id', $request->input('sub_category_id'));
            }
            if (!empty($request->input('brand_id'))) {
                $query->where('p.brand_id', $request->input('brand_id'));
            }
            if (!empty($request->input('unit_id'))) {
                $query->where('p.unit_id', $request->input('unit_id'));
            }
            if (!empty($request->input('exp_date_filter'))) {
                $query->whereDate('exp_date', '<=', $request->input('exp_date_filter'));
            }

            $report = $query->select(
                'p.name as product',
                'p.sku',
                'p.type as product_type',
                'v.name as variation',
                'pv.name as product_variation',
                'l.name as location',
                'mfg_date',
                'exp_date',
                'u.short_name as unit',
                DB::raw("SUM(COALESCE(quantity, 0) - COALESCE(quantity_sold, 0) - COALESCE(quantity_adjusted, 0) - COALESCE(quantity_returned, 0)) as stock_left"),
                't.ref_no',
                't.id as transaction_id',
                'purchase_lines.id as purchase_line_id',
                'purchase_lines.lot_number'
            )
                                    ->groupBy('purchase_lines.id');

            return Datatables::of($report)
                ->editColumn('name', function ($row) {
                    if ($row->product_type == 'variable') {
                        return $row->product . ' - ' .
                        $row->product_variation . ' - ' . $row->variation;
                    } else {
                        return $row->product;
                    }
                })
                ->editColumn('mfg_date', function ($row) {
                    if (!empty($row->mfg_date)) {
                        return $this->productUtil->format_date($row->mfg_date);
                    } else {
                        return '--';
                    }
                })
                ->editColumn('exp_date', function ($row) {
                    if (!empty($row->exp_date)) {
                        $carbon_exp = \Carbon::createFromFormat('Y-m-d', $row->exp_date);
                        $carbon_now = \Carbon::now();
                        if ($carbon_now->diffInDays($carbon_exp, false) >= 0) {
                            return $this->productUtil->format_date($row->exp_date) . '<br><small>( <span class="time-to-now">' . $row->exp_date . '</span> )</small>';
                        } else {
                            return $this->productUtil->format_date($row->exp_date) . ' &nbsp; <span class="label label-danger">' . __('report.expired') . '</span><br><small>( <span class="time-from-now">' . $row->exp_date . '</span> )</small>';
                        }
                    } else {
                        return '--';
                    }
                })
                ->editColumn('ref_no', function ($row) {
                    return '<button type="button" data-href="' . action('PurchaseController@show', [$row->transaction_id])
                            . '" class="btn btn-link btn-modal" data-container=".view_modal"  >' . $row->ref_no . '</button>';
                })
                ->editColumn('stock_left', function ($row) {
                    return '<span class="display_currency stock_left" data-currency_symbol=false data-orig-value="' . $row->stock_left . '" data-unit="' . $row->unit . '" >' . $row->stock_left . '</span> ' . $row->unit;
                })
                ->addColumn('edit', function ($row) {
                    $html =  '<button type="button" class="btn btn-primary btn-xs stock_expiry_edit_btn" data-transaction_id="' . $row->transaction_id . '" data-purchase_line_id="' . $row->purchase_line_id . '"> <i class="fa fa-edit"></i> ' . __("messages.edit") .
                    '</button>';

                    if (!empty($row->exp_date)) {
                        $carbon_exp = \Carbon::createFromFormat('Y-m-d', $row->exp_date);
                        $carbon_now = \Carbon::now();
                        if ($carbon_now->diffInDays($carbon_exp, false) < 0) {
                             $html .=  ' <button type="button" class="btn btn-warning btn-xs remove_from_stock_btn" data-href="' . action('StockAdjustmentController@removeExpiredStock', [$row->purchase_line_id]) . '"> <i class="fa fa-trash"></i> ' . __("lang_v1.remove_from_stock") .
                            '</button>';
                        }
                    }

                    return $html;
                })
                ->rawColumns(['exp_date', 'ref_no', 'edit', 'stock_left'])
                ->make(true);
        }

        $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->pluck('name', 'id');
        $brands = Brands::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);
        $view_stock_filter = [
            \Carbon::now()->subDay()->format('Y-m-d') => __('report.expired'),
            \Carbon::now()->addWeek()->format('Y-m-d') => __('report.expiring_in_1_week'),
            \Carbon::now()->addDays(15)->format('Y-m-d') => __('report.expiring_in_15_days'),
            \Carbon::now()->addMonth()->format('Y-m-d') => __('report.expiring_in_1_month'),
            \Carbon::now()->addMonths(3)->format('Y-m-d') => __('report.expiring_in_3_months'),
            \Carbon::now()->addMonths(6)->format('Y-m-d') => __('report.expiring_in_6_months'),
            \Carbon::now()->addYear()->format('Y-m-d') => __('report.expiring_in_1_year')
        ];

        return view('report.stock_expiry_report')
                ->with(compact('categories', 'brands', 'units', 'business_locations', 'view_stock_filter'));
    }

    /**
     * Shows product stock expiry report
     *
     * @return \Illuminate\Http\Response
     */
    public function getStockExpiryReportEditModal(Request $request, $purchase_line_id)
    {

        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //Return the details in ajax call
        if ($request->ajax()) {
            $purchase_line = PurchaseLine::join(
                'transactions as t',
                'purchase_lines.transaction_id',
                '=',
                't.id'
            )
                                ->join(
                                    'products as p',
                                    'purchase_lines.product_id',
                                    '=',
                                    'p.id'
                                )
                                ->where('purchase_lines.id', $purchase_line_id)
                                ->where('t.business_id', $business_id)
                                ->select(['purchase_lines.*', 'p.name', 't.ref_no'])
                                ->first();

            if (!empty($purchase_line)) {
                if (!empty($purchase_line->exp_date)) {
                    $purchase_line->exp_date = date('m/d/Y', strtotime($purchase_line->exp_date));
                }
            }

            return view('report.partials.stock_expiry_edit_modal')
                ->with(compact('purchase_line'));
        }
    }

    /**
     * Update product stock expiry report
     *
     * @return \Illuminate\Http\Response
     */
    public function updateStockExpiryReport(Request $request)
    {

        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            //Return the details in ajax call
            if ($request->ajax()) {
                DB::beginTransaction();

                $input = $request->only(['purchase_line_id', 'exp_date']);

                $purchase_line = PurchaseLine::join(
                    'transactions as t',
                    'purchase_lines.transaction_id',
                    '=',
                    't.id'
                )
                                    ->join(
                                        'products as p',
                                        'purchase_lines.product_id',
                                        '=',
                                        'p.id'
                                    )
                                    ->where('purchase_lines.id', $input['purchase_line_id'])
                                    ->where('t.business_id', $business_id)
                                    ->select(['purchase_lines.*', 'p.name', 't.ref_no'])
                                    ->first();

                if (!empty($purchase_line) && !empty($input['exp_date'])) {
                    $purchase_line->exp_date = $this->productUtil->uf_date($input['exp_date']);
                    $purchase_line->save();
                }

                DB::commit();

                $output = ['success' => 1,
                            'msg' => __('lang_v1.updated_succesfully')
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

    /**
     * Shows product stock expiry report
     *
     * @return \Illuminate\Http\Response
     */
    public function getCustomerGroup(Request $request)
    {
        if (!auth()->user()->can('contacts_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {
            $query = Transaction::leftjoin('customer_groups AS CG', 'transactions.customer_group_id', '=', 'CG.id')
                        ->where('transactions.business_id', $business_id)
                        ->where('transactions.type', 'sell')
                        ->where('transactions.status', 'final')
                        ->groupBy('transactions.customer_group_id')
                        ->select(DB::raw("SUM(final_total) as total_sell"), 'CG.name');

            $group_id = $request->get('customer_group_id', null);
            if (!empty($group_id)) {
                $query->where('transactions.customer_group_id', $group_id);
            }

            $permitted_locations = auth()->user()->permitted_locations();

            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            $location_id = $request->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('transactions.location_id', $location_id);
            }

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }


            return Datatables::of($query)
                ->editColumn('total_sell', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->total_sell . '</span>';
                })
                ->rawColumns(['total_sell'])
                ->make(true);
        }

        $customer_group = CustomerGroup::forDropdown($business_id, false, true);
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.customer_group')
            ->with(compact('customer_group', 'business_locations'));
    }

    /**
     * Shows product purchase report
     *
     * @return \Illuminate\Http\Response
     */
    public function getproductPurchaseReport(Request $request)
    {

        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        if ($request->ajax()) {
            $variation_id = $request->get('variation_id', null);
            $query = PurchaseLine::join(
                'transactions as t',
                'purchase_lines.transaction_id',
                '=',
                't.id'
            )->join('variations as v',
                    'purchase_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('contacts as c', 't.contact_id', '=', 'c.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'purchase')
                ->select(
                    'p.name as product_name',
                    'p.type as product_type',
                    'pv.name as product_variation',
                    'v.name as variation_name',
                    'c.name as supplier',
                    't.id as transaction_id',
                    't.ref_no',
                    't.transaction_date as transaction_date',
                    'purchase_lines.purchase_price_inc_tax as unit_purchase_price',
                    'purchase_lines.quantity as purchase_qty',
                    'u.short_name as unit',
                    DB::raw('purchase_lines.quantity * purchase_lines.purchase_price_inc_tax as subtotal')
                )->groupBy('purchase_lines.id');
            if (!empty($variation_id)) {
                $query->where('purchase_lines.variation_id', $variation_id);
            }
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }

            $location_id = $request->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            $supplier_id = $request->get('supplier_id', null);
            if (!empty($supplier_id)) {
                $query->where('t.contact_id', $supplier_id);
            }
            $category_id = $request->get('category_id', null);
            //dd($category_id);
            if (!empty($category_id)) {
                $query->where('p.category_id', $category_id);
            }
            $sub_category_id = $request->get('sub_category_id', null);
            if (!empty($sub_category_id)) {
                $query->where('p.sub_category_id', $sub_category_id);
            }
            $brand_id = $request->get('brand_id', null);
            if (!empty($brand_id)) {
                $query->where('p.brand_id', $brand_id);
            }
            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;
                    if ($row->product_type == 'variable') {
                        $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                    }
                    return $product_name;
                })
                 ->editColumn('ref_no', function ($row) {
                    return '<a data-href="' . action('PurchaseController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->ref_no . '</a>';
                 })
                 ->editColumn('purchase_qty', function ($row) {
                    return '<span class="display_currency purchase_qty" data-currency_symbol=false data-orig-value="' . (float)$row->purchase_qty . '" data-unit="' . $row->unit . '" >' . (float) $row->purchase_qty . '</span> ' . $row->unit;
                 })
                 ->editColumn('subtotal', function ($row) {
                    return '<span class="display_currency row_subtotal" data-currency_symbol=true data-orig-value="' . $row->subtotal . '">' . $row->subtotal . '</span>';
                 })
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('unit_purchase_price', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->unit_purchase_price . '</span>';
                })
                ->rawColumns(['ref_no', 'unit_purchase_price', 'subtotal', 'purchase_qty'])
                ->make(true);
        }
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id);
        $categories = Category::where('business_id', $business_id)
        ->where('parent_id', 0)
        ->pluck('name', 'id');
        $brands = Brands::where('business_id', $business_id)
        ->pluck('name', 'id');
        return view('report.product_purchase_report')
            ->with(compact('business_locations', 'suppliers','categories','brands'));
    }

    /**
     * Shows product purchase report
     *
     * @return \Illuminate\Http\Response
     */
    public function getproductSellReport(Request $request)
    {

        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        if ($request->ajax()) {
            $variation_id = $request->get('variation_id', null);
            $query = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
                ->join(
                    'variations as v',
                    'transaction_sell_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('contacts as c', 't.contact_id', '=', 'c.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell')
                ->select(
                    'p.name as product_name',
                    'p.type as product_type',
                    'pv.name as product_variation',
                    'v.default_purchase_price',
                    'c.name as customer',
                    't.id as transaction_id',
                    't.invoice_no',
                    't.transaction_date as transaction_date',
                    'transaction_sell_lines.unit_price_inc_tax as unit_sale_price',
                    'transaction_sell_lines.quantity as sell_qty',
                    'u.short_name as unit',
                    DB::raw('transaction_sell_lines.quantity * transaction_sell_lines.unit_price_inc_tax as subtotal')
                )
                ->groupBy('transaction_sell_lines.id');

            if (!empty($variation_id)) {
                $query->where('transaction_sell_lines.variation_id', $variation_id);
            }
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }

            $location_id = $request->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            $customer_id = $request->get('customer_id', null);
            if (!empty($customer_id)) {
                $query->where('t.contact_id', $customer_id);
            }
            $category_id = $request->get('category_id', null);
            //dd($category_id);
            if (!empty($category_id)) {
                $query->where('p.category_id', $category_id);
            }
            $sub_category_id = $request->get('sub_category_id', null);
            if (!empty($sub_category_id)) {
                $query->where('p.sub_category_id', $sub_category_id);
            }
            $brand_id = $request->get('brand_id', null);
            if (!empty($brand_id)) {
                $query->where('p.brand_id', $brand_id);
            }
            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;
                    if ($row->product_type == 'variable') {
                        $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                    }

                    return $product_name;
                })
                 ->editColumn('invoice_no', function ($row) {
                    return '<a data-href="' . action('SellController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->invoice_no . '</a>';
                 })
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('unit_sale_price', function ($row) {
                    return '<span class="display_currency" data-currency_symbol = true>' . $row->unit_sale_price . '</span>';
                })
                ->editColumn('sell_qty', function ($row) {
                    return '<span class="display_currency sell_qty" data-currency_symbol=false data-orig-value="' . (float)$row->sell_qty . '" data-unit="' . $row->unit . '" >' . (float) $row->sell_qty . '</span> ' .$row->unit;
                })
                 ->editColumn('subtotal', function ($row) {
                    return '<span class="display_currency row_subtotal" data-currency_symbol = true data-orig-value="' . $row->subtotal . '">' . $row->subtotal . '</span>';
                 })
                ->rawColumns(['invoice_no', 'unit_sale_price', 'subtotal', 'sell_qty'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers = Contact::customersDropdown($business_id);
        $categories = Category::where('business_id', $business_id)
        ->where('parent_id', 0)
        ->pluck('name', 'id');
        $brands = Brands::where('business_id', $business_id)
        ->pluck('name', 'id');
        return view('report.product_sell_report')
            ->with(compact('business_locations', 'customers','categories','brands'));
    }

    /**
     * Shows product lot report
     *
     * @return \Illuminate\Http\Response
     */
    public function getLotReport(Request $request)
    {
        if (!auth()->user()->can('stock_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

         //Return the details in ajax call
        if ($request->ajax()) {
            $query = Product::where('products.business_id', $business_id)
                    ->leftjoin('units', 'products.unit_id', '=', 'units.id')
                    ->join('variations as v', 'products.id', '=', 'v.product_id')
                    ->join('purchase_lines as pl', 'v.id', '=', 'pl.variation_id')
                    ->leftjoin(
                        'transaction_sell_lines_purchase_lines as tspl',
                        'pl.id',
                        '=',
                        'tspl.purchase_line_id'
                    )
                    ->join('transactions as t', 'pl.transaction_id', '=', 't.id');

            $permitted_locations = auth()->user()->permitted_locations();
            $location_filter = 'WHERE ';

            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);

                $locations_imploded = implode(', ', $permitted_locations);
                $location_filter = " LEFT JOIN transactions as t2 on pls.transaction_id=t2.id WHERE t2.location_id IN ($locations_imploded) AND ";
            }

            if (!empty($request->input('location_id'))) {
                $location_id = $request->input('location_id');
                $query->where('t.location_id', $location_id);

                $location_filter = "LEFT JOIN transactions as t2 on pls.transaction_id=t2.id WHERE t2.location_id=$location_id AND ";
            }

            if (!empty($request->input('category_id'))) {
                $query->where('products.category_id', $request->input('category_id'));
            }

            if (!empty($request->input('sub_category_id'))) {
                $query->where('products.sub_category_id', $request->input('sub_category_id'));
            }

            if (!empty($request->input('brand_id'))) {
                $query->where('products.brand_id', $request->input('brand_id'));
            }

            if (!empty($request->input('unit_id'))) {
                $query->where('products.unit_id', $request->input('unit_id'));
            }

            $products = $query->select(
                'products.name as product',
                'v.name as variation_name',
                'sub_sku',
                'pl.lot_number',
                'pl.exp_date as exp_date',
                DB::raw("( COALESCE((SELECT SUM(quantity) from purchase_lines as pls $location_filter variation_id = v.id AND lot_number = pl.lot_number), 0) -
                    SUM(COALESCE(tspl.quantity, 0))) as stock"),
                // DB::raw("(SELECT SUM(IF(transactions.type='sell', TSL.quantity, -1* TPL.quantity) ) FROM transactions
                //         LEFT JOIN transaction_sell_lines AS TSL ON transactions.id=TSL.transaction_id

                //         LEFT JOIN purchase_lines AS TPL ON transactions.id=TPL.transaction_id

                //         WHERE transactions.status='final' AND transactions.type IN ('sell', 'sell_return') $location_filter
                //         AND (TSL.product_id=products.id OR TPL.product_id=products.id)) as total_sold"),

                DB::raw("COALESCE(SUM(IF(tspl.sell_line_id IS NULL, 0, tspl.quantity ) ), 0) as total_sold"),
                'products.type',
                'units.short_name as unit'
            )
            ->whereNotNull('pl.lot_number')
            ->groupBy('v.id')
            ->groupBy('pl.lot_number');

            return Datatables::of($products)
                ->editColumn('stock', function ($row) {
                    $stock = $row->stock ? $row->stock : 0 ;
                    return '<span class="display_currency total_stock" data-currency_symbol=false data-orig-value="' . (float)$stock . '" data-unit="' . $row->unit . '" >' . (float)$stock . '</span> ' . $row->unit;
                })
                ->editColumn('product', function ($row) {
                    if ($row->variation_name != 'DUMMY') {
                        return $row->product . ' (' . $row->variation_name . ')';
                    } else {
                        return $row->product;
                    }
                })
                ->editColumn('total_sold', function ($row) {
                    if ($row->total_sold) {
                        return '<span class="display_currency total_sold" data-currency_symbol=false data-orig-value="' . (float)$row->total_sold . '" data-unit="' . $row->unit . '" >' . (float)$row->total_sold . '</span> ' . $row->unit;
                    } else {
                        return '0' . ' ' . $row->unit;
                    }
                })
                ->editColumn('exp_date', function ($row) {
                    if (!empty($row->exp_date)) {
                        $carbon_exp = \Carbon::createFromFormat('Y-m-d', $row->exp_date);
                        $carbon_now = \Carbon::now();
                        if ($carbon_now->diffInDays($carbon_exp, false) >= 0) {
                            return $this->productUtil->format_date($row->exp_date) . '<br><small>( <span class="time-to-now">' . $row->exp_date . '</span> )</small>';
                        } else {
                            return $this->productUtil->format_date($row->exp_date) . ' &nbsp; <span class="label label-danger">' . __('report.expired') . '</span><br><small>( <span class="time-from-now">' . $row->exp_date . '</span> )</small>';
                        }
                    } else {
                        return '--';
                    }
                })
                ->removeColumn('unit')
                ->removeColumn('id')
                ->removeColumn('variation_name')
                ->rawColumns(['exp_date', 'stock', 'total_sold'])
                ->make(true);
        }

        $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->pluck('name', 'id');
        $brands = Brands::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $units = Unit::where('business_id', $business_id)
                            ->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.lot_report')
            ->with(compact('categories', 'brands', 'units', 'business_locations'));
    }

    /**
     * Shows purchase payment report
     *
     * @return \Illuminate\Http\Response
     */
    public function purchasePaymentReport(Request $request)
    {

        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        if ($request->ajax()) {
            $supplier_id = $request->get('supplier_id', null);
            $contact_filter1 = !empty($supplier_id) ? "AND t.contact_id=$supplier_id" : '';
            $contact_filter2 = !empty($supplier_id) ? "AND transactions.contact_id=$supplier_id" : '';

            $query = TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
                    $join->on('transaction_payments.transaction_id', '=', 't.id')
                    ->where('t.business_id', $business_id)
                    ->where('t.type', 'purchase');
            })
                ->where('transaction_payments.business_id', $business_id)
                ->where(function ($q) use ($business_id, $contact_filter1, $contact_filter2) {
                    $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type='purchase' AND transaction_payments.parent_id IS NULL $contact_filter1)")
                        ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type='purchase' AND transactions.business_id = $business_id AND tp.parent_id=transaction_payments.id $contact_filter2)");
                })

                ->select(
                    DB::raw("IF(transaction_payments.transaction_id IS NULL,
                                (SELECT c.name FROM transactions as ts
                                JOIN contacts as c ON ts.contact_id=c.id
                                WHERE ts.id=(
                                        SELECT tps.transaction_id FROM transaction_payments as tps
                                        WHERE tps.parent_id=transaction_payments.id LIMIT 1
                                    )
                                ),
                                (SELECT c.name FROM transactions as ts JOIN
                                    contacts as c ON ts.contact_id=c.id
                                    WHERE ts.id=t.id
                                )
                            ) as supplier"),
                    'transaction_payments.amount',
                    'method',
                    'paid_on',
                    'transaction_payments.payment_ref_no',
                    't.ref_no',
                    't.id as transaction_id',
                    'cheque_number',
                    'card_transaction_number',
                    'bank_account_number',
                    'transaction_no',
                    'transaction_payments.id as DT_RowId'
                )
                ->groupBy('transaction_payments.id');

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(paid_on)'), [$start_date, $end_date]);
            }

            $permitted_locations = auth()->user()->permitted_locations();

            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            $location_id = $request->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }


            return Datatables::of($query)
                 ->editColumn('ref_no', function ($row) {
                    if (!empty($row->ref_no)) {
                        return '<a data-href="' . action('PurchaseController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->ref_no . '</a>';
                    } else {
                        return '';
                    }
                 })
                ->editColumn('paid_on', '{{@format_date($paid_on)}}')
                ->editColumn('method', function ($row) {
                    $method = __('lang_v1.' . $row->method);
                    if ($row->method == 'cheque') {
                        $method .= '<br>(' . __('lang_v1.cheque_no') . ': ' . $row->cheque_number . ')';
                    } elseif ($row->method == 'card') {
                        $method .= '<br>(' . __('lang_v1.card_transaction_no') . ': ' . $row->card_transaction_number . ')';
                    } elseif ($row->method == 'bank_transfer') {
                        $method .= '<br>(' . __('lang_v1.bank_account_no') . ': ' . $row->bank_account_number . ')';
                    } elseif ($row->method == 'custom_pay_1') {
                        $method = __('lang_v1.custom_payment_1') . '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_2') {
                        $method = __('lang_v1.custom_payment_2') . '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_3') {
                        $method = __('lang_v1.custom_payment_3') . '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    }
                    return $method;
                })
                ->editColumn('amount', function ($row) {
                    return '<span class="display_currency paid-amount" data-currency_symbol = true data-orig-value="' . $row->amount . '">' . $row->amount . '</span>';
                })
                ->addColumn('action', '<button type="button" class="btn btn-primary btn-xs view_payment" data-href="{{ action("TransactionPaymentController@viewPayment", [$DT_RowId]) }}">@lang("messages.view")
                    </button>')
                ->rawColumns(['ref_no', 'amount', 'method', 'action'])
                ->make(true);
        }
        $business_locations = BusinessLocation::forDropdown($business_id);
        $suppliers = Contact::suppliersDropdown($business_id, false);

        return view('report.purchase_payment_report')
            ->with(compact('business_locations', 'suppliers'));
    }

    /**
     * Shows sell payment report
     *
     * @return \Illuminate\Http\Response
     */
    public function sellPaymentReport(Request $request)
    {

        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');
        if ($request->ajax()) {
            $customer_id = $request->get('supplier_id', null);
            $contact_filter1 = !empty($customer_id) ? "AND t.contact_id=$customer_id" : '';
            $contact_filter2 = !empty($customer_id) ? "AND transactions.contact_id=$customer_id" : '';

            $query = TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
                    $join->on('transaction_payments.transaction_id', '=', 't.id')
                    ->where('t.business_id', $business_id)
                    ->where('t.type', 'sell');
            })
                ->leftjoin('contacts as c', 't.contact_id', '=', 'c.id')
                ->where('transaction_payments.business_id', $business_id)
                ->where(function ($q) use ($business_id, $contact_filter1, $contact_filter2) {
                    $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type='sell' AND transaction_payments.parent_id IS NULL $contact_filter1)")
                        ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type='sell' AND transactions.business_id = $business_id AND tp.parent_id=transaction_payments.id $contact_filter2)");
                })
                ->select(
                    DB::raw("IF(transaction_payments.transaction_id IS NULL,
                                (SELECT c.name FROM transactions as ts
                                JOIN contacts as c ON ts.contact_id=c.id
                                WHERE ts.id=(
                                        SELECT tps.transaction_id FROM transaction_payments as tps
                                        WHERE tps.parent_id=transaction_payments.id LIMIT 1
                                    )
                                ),
                                (SELECT c.name FROM transactions as ts JOIN
                                    contacts as c ON ts.contact_id=c.id
                                    WHERE ts.id=t.id
                                )
                            ) as customer"),
                    'transaction_payments.amount',
                    'method',
                    'paid_on',
                    'transaction_payments.payment_ref_no',
                    't.invoice_no',
                    't.id as transaction_id',
                    'cheque_number',
                    'card_transaction_number',
                    'bank_account_number',
                    'transaction_payments.id as DT_RowId'
                )
                ->groupBy('transaction_payments.id');

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(paid_on)'), [$start_date, $end_date]);
            }

            $location_id = $request->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }
            return Datatables::of($query)
                 ->editColumn('invoice_no', function ($row) {
                    if (!empty($row->transaction_id)) {
                        return '<a data-href="' . action('SellController@show', [$row->transaction_id])
                            . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->invoice_no . '</a>';
                    } else {
                        return '';
                    }
                 })
                ->editColumn('paid_on', '{{@format_date($paid_on)}}')
                ->editColumn('method', function ($row) {
                    $method = __('lang_v1.' . $row->method);
                    if ($row->method == 'cheque') {
                        $method .= '<br>(' . __('lang_v1.cheque_no') . ': ' . $row->cheque_number . ')';
                    } elseif ($row->method == 'card') {
                        $method .= '<br>(' . __('lang_v1.card_transaction_no') . ': ' . $row->card_transaction_number . ')';
                    } elseif ($row->method == 'bank_transfer') {
                        $method .= '<br>(' . __('lang_v1.bank_account_no') . ': ' . $row->bank_account_number . ')';
                    } elseif ($row->method == 'custom_pay_1') {
                        $method = __('lang_v1.custom_payment_1') . '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_2') {
                        $method = __('lang_v1.custom_payment_2') . '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    } elseif ($row->method == 'custom_pay_3') {
                        $method = __('lang_v1.custom_payment_3') . '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                    }
                    return $method;
                })
                ->editColumn('amount', function ($row) {
                    return '<span class="display_currency paid-amount" data-orig-value="' . $row->amount . '" data-currency_symbol = true>' . $row->amount . '</span>';
                })
                ->addColumn('action', '<button type="button" class="btn btn-primary btn-xs view_payment" data-href="{{ action("TransactionPaymentController@viewPayment", [$DT_RowId]) }}">@lang("messages.view")
                    </button>')
                ->rawColumns(['invoice_no', 'amount', 'method', 'action'])
                ->make(true);
        }
        $business_locations = BusinessLocation::forDropdown($business_id);
        $customers = Contact::customersDropdown($business_id, false);

        return view('report.sell_payment_report')
            ->with(compact('business_locations', 'customers'));
    }

   public function ecomOrdersReport(Request $request) {
    $date = $request->date;
    $sort_search = null;
    $delivery_status = null;


    $orders = Order2::orderBy('id', 'desc');
    if ($request->has('search')) {
        $sort_search = $request->search;
        $orders = $orders->where('code', 'like', '%' . $sort_search . '%');
    }
    if ($request->delivery_status != null) {
        $orders = $orders->where('delivery_status', $request->delivery_status);
        $delivery_status = $request->delivery_status;
    }
    if ($date != null) {
        $orders = $orders->where('created_at', '>=', date('Y-m-d', strtotime(explode(" to ", $date)[0])))->where('created_at', '<=', date('Y-m-d', strtotime(explode(" to ", $date)[1])));
    }
    $orders = $orders->paginate(15);


// return $orders;
    return view('report.ecom_orders_report', compact('orders', 'sort_search', 'delivery_status', 'date'));




    // if (!auth()->user()->can('purchase_n_sell_report.view')) {
    //     abort(403, 'Unauthorized action.');
    // }

    $business_id = $request->session()->get('user.business_id');
    if ($request->ajax()) {
        $customer_id = $request->get('supplier_id', null);
        $contact_filter1 = !empty($customer_id) ? "AND t.contact_id=$customer_id" : '';
        $contact_filter2 = !empty($customer_id) ? "AND transactions.contact_id=$customer_id" : '';

        $query = TransactionPayment::leftjoin('transactions as t', function ($join) use ($business_id) {
                $join->on('transaction_payments.transaction_id', '=', 't.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell');
        })
            ->leftjoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->where('transaction_payments.business_id', $business_id)
            ->where(function ($q) use ($business_id, $contact_filter1, $contact_filter2) {
                $q->whereRaw("(transaction_payments.transaction_id IS NOT NULL AND t.type='sell' AND transaction_payments.parent_id IS NULL $contact_filter1)")
                    ->orWhereRaw("EXISTS(SELECT * FROM transaction_payments as tp JOIN transactions ON tp.transaction_id = transactions.id WHERE transactions.type='sell' AND transactions.business_id = $business_id AND tp.parent_id=transaction_payments.id $contact_filter2)");
            })
            ->select(
                DB::raw("IF(transaction_payments.transaction_id IS NULL,
                            (SELECT c.name FROM transactions as ts
                            JOIN contacts as c ON ts.contact_id=c.id
                            WHERE ts.id=(
                                    SELECT tps.transaction_id FROM transaction_payments as tps
                                    WHERE tps.parent_id=transaction_payments.id LIMIT 1
                                )
                            ),
                            (SELECT c.name FROM transactions as ts JOIN
                                contacts as c ON ts.contact_id=c.id
                                WHERE ts.id=t.id
                            )
                        ) as customer"),
                'transaction_payments.amount',
                'method',
                'paid_on',
                'transaction_payments.payment_ref_no',
                't.invoice_no',
                't.id as transaction_id',
                'cheque_number',
                'card_transaction_number',
                'bank_account_number',
                'transaction_payments.id as DT_RowId'
            )
            ->groupBy('transaction_payments.id');

        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween(DB::raw('date(paid_on)'), [$start_date, $end_date]);
        }

        $location_id = $request->get('location_id', null);
        if (!empty($location_id)) {
            $query->where('t.location_id', $location_id);
        }
        return Datatables::of($query)
             ->editColumn('invoice_no', function ($row) {
                if (!empty($row->transaction_id)) {
                    return '<a data-href="' . action('SellController@show', [$row->transaction_id])
                        . '" href="#" data-container=".view_modal" class="btn-modal">' . $row->invoice_no . '</a>';
                } else {
                    return '';
                }
             })
            ->editColumn('paid_on', '{{@format_date($paid_on)}}')
            ->editColumn('method', function ($row) {
                $method = __('lang_v1.' . $row->method);
                if ($row->method == 'cheque') {
                    $method .= '<br>(' . __('lang_v1.cheque_no') . ': ' . $row->cheque_number . ')';
                } elseif ($row->method == 'card') {
                    $method .= '<br>(' . __('lang_v1.card_transaction_no') . ': ' . $row->card_transaction_number . ')';
                } elseif ($row->method == 'bank_transfer') {
                    $method .= '<br>(' . __('lang_v1.bank_account_no') . ': ' . $row->bank_account_number . ')';
                } elseif ($row->method == 'custom_pay_1') {
                    $method = __('lang_v1.custom_payment_1') . '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                } elseif ($row->method == 'custom_pay_2') {
                    $method = __('lang_v1.custom_payment_2') . '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                } elseif ($row->method == 'custom_pay_3') {
                    $method = __('lang_v1.custom_payment_3') . '<br>(' . __('lang_v1.transaction_no') . ': ' . $row->transaction_no . ')';
                }
                return $method;
            })
            ->editColumn('amount', function ($row) {
                return '<span class="display_currency paid-amount" data-orig-value="' . $row->amount . '" data-currency_symbol = true>' . $row->amount . '</span>';
            })
            ->addColumn('action', '<button type="button" class="btn btn-primary btn-xs view_payment" data-href="{{ action("TransactionPaymentController@viewPayment", [$DT_RowId]) }}">@lang("messages.view")
                </button>')
            ->rawColumns(['invoice_no', 'amount', 'method', 'action'])
            ->make(true);
    }
    $business_locations = BusinessLocation::forDropdown($business_id);
    $customers = Contact::customersDropdown($business_id, false);

    return view('report.ecom_orders_report')
        ->with(compact('business_locations', 'customers'));
}
    /**
     * Shows tables report
     *
     * @return \Illuminate\Http\Response
     */
    public function getTableReport(Request $request)
    {
        if (!auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {
            $query = ResTable::leftjoin('transactions AS T', 'T.res_table_id', '=', 'res_tables.id')
                        ->where('T.business_id', $business_id)
                        ->where('T.type', 'sell')
                        ->where('T.status', 'final')
                        ->groupBy('res_tables.id')
                        ->select(DB::raw("SUM(final_total) as total_sell"), 'res_tables.name as table');

            $location_id = $request->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('T.location_id', $location_id);
            }

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }

            return Datatables::of($query)
                ->editColumn('total_sell', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->total_sell . '</span>';
                })
                ->rawColumns(['total_sell'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.table_report')
            ->with(compact('business_locations'));
    }

    /**
     * Shows service staff report
     *
     * @return \Illuminate\Http\Response
     */
    public function getServiceStaffReport(Request $request)
    {
        if (!auth()->user()->can('sales_representative.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        if ($request->ajax()) {
            $query = User::leftjoin('transactions AS T', 'T.res_waiter_id', '=', 'users.id')
                        ->where('T.business_id', $business_id)
                        ->where('T.type', 'sell')
                        ->where('T.status', 'final')
                        ->groupBy('users.id')
                        ->select(DB::raw("SUM(final_total) as total_sell"), DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as service_staff_name"));

            $location_id = $request->get('location_id', null);
            if (!empty($location_id)) {
                $query->where('T.location_id', $location_id);
            }

            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }

            return Datatables::of($query)
                ->editColumn('total_sell', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->total_sell . '</span>';
                })
                ->rawColumns(['total_sell'])
                ->filterColumn('service_staff_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('report.service_staff_report')
            ->with(compact('business_locations'));
    }

    public function productDownload(){
        $business_id = request()->session()->get('user.business_id');
        $products = Product::join('variations', 'products.id', '=', 'variations.product_id')
                ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
                ->join('variation_location_details as VLD', 'products.id', '=', 'VLD.product_id')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier')
                ->select(
                    'products.id',
                    'products.name as product',
                    'products.type',
                    'VLD.qty_available',
                    'c1.name as category',
                    'variations.default_purchase_price',
                    'variations.sell_price_inc_tax'
                )->paginate(50);

        return view('report.product_download')
            ->with(compact('products'));
    }

    public function productReview(){
        $business_id = request()->session()->get('user.business_id');
        $products = Product::LeftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
                ->select(
                    'products.id',
                    'products.name as product',
                    'c1.name as category',
                    'products.type'
                )->paginate(50);

        return view('report.product_review')
            ->with(compact('products'));
    }

     // new report

    public function supplierSellProduct(){
        $business_id = request()->session()->get('user.business_id');
        $suppliers=Contact::suppliersDropdown($business_id);
        $sup='';
        $date_filter='';
        if(request()->start !='' and request()->end !=''){
            $date_filter .= "AND tsl.created_at >= '" . request()->start .' 00:00:00'. "' AND tsl.created_at <= '" . request()->end .' 23:59:00'. "'";
        }

        $paginate=50;

        if(request('shorting') !=''){
            $paginate=request('shorting') ;

        }

        $query= DB::table('purchase_lines as pl')
            ->join('products as p','pl.product_id','=','p.id')
            ->join('variations as v','v.product_id','=','p.id')
            ->join('transactions as t','t.id','=','pl.transaction_id')
            ->select('p.id','p.name','p.sku','v.default_purchase_price','v.sell_price_inc_tax',
                DB::raw("SUM(pl.quantity) as purchase_qty"),
                DB::raw("(SELECT sum(tsl.quantity) FROM transaction_sell_lines as tsl

                LEFT JOIN transactions ON transactions.id=tsl.transaction_id
                where (tsl.product_id=pl.product_id) and transactions.status='final' AND transactions.type='sell'  $date_filter) as sell_qty"),





                DB::raw("(SELECT sum(tsl.quantity * tsl.line_discount_amount) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id) $date_filter) as discount_amount"),

                DB::raw("(SELECT sum(tsl.item_tax) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id) $date_filter) as tax"),

                DB::raw("(SELECT sum(tsl.quantity_returned) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id) $date_filter) as return_qty"))

            ->where('t.business_id',$business_id);


            if (request()->supplier_id !='') {
                    $query->where('t.contact_id',request()->supplier_id);
                    $sup=Contact::find(request()->supplier_id)->name;
            }

        $results=$query->groupBy('p.id')->orderBy('sell_qty','desc')
            ->paginate($paginate);

        return view('report.supplier_sell_product', compact('results','suppliers','sup'));

    }


    public function supplierAllStock(){

        $business_id = request()->session()->get('user.business_id');
        $suppliers=Contact::suppliersDropdown($business_id);
        $sup='';
        $query= DB::table('purchase_lines as pl')
            ->join('products as p','pl.product_id','=','p.id')
            ->join('variations as v','v.product_id','=','p.id')
            ->join('transactions as t','t.id','=','pl.transaction_id')
            ->select('p.id','p.name','p.sku','v.default_purchase_price','v.sell_price_inc_tax',
                DB::raw("SUM(pl.quantity_returned) as qty_returned"),
                DB::raw("SUM(pl.quantity) as purchase_qty"),
                DB::raw("(SELECT sum(tsl.quantity) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id)) as sell_qty")
            )->where('t.business_id',$business_id);

            if (request()->supplier_id !='') {
                    $query->where('t.contact_id',request()->supplier_id);
                    $sup=Contact::find(request()->supplier_id)->name;
            }


        $results=$query->groupBy('p.id')->orderBy('p.name')
            ->get();

        return view('report.supplier_all_stock', compact('suppliers','sup','results'));

    }
    //

    public function supplierSellSumery(){

        $business_id = request()->session()->get('user.business_id');
        $suppliers=Contact::suppliersDropdown($business_id);
        $sup='';

        $date_filter='';
        if(request()->start !='' and request()->end !=''){
            $date_filter .= "AND created_at >= '" . request()->start .' 00:00:00'. "' AND created_at <= '" . request()->end .' 23:59:00'. "'";
        }

        $query= DB::table('purchase_lines as pl')
            ->join('products as p','pl.product_id','=','p.id')
            ->join('variations as v','v.product_id','=','p.id')
            ->join('transactions as t','t.id','=','pl.transaction_id')
            ->select('p.id','p.name','p.sku','v.default_purchase_price','v.sell_price_inc_tax',

                DB::raw("(SELECT sum(tsl.quantity) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id) $date_filter) as sell_qty"),

                DB::raw("(SELECT sum(tsl.quantity_returned) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id) $date_filter) as return_qty"),

                DB::raw("(SELECT sum(tsl.quantity * tsl.unit_price_inc_tax) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id) $date_filter) as sell_price"),

                DB::raw("(SELECT sum((tsl.quantity - tsl.quantity_returned) * tsl.unit_price_inc_tax) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id) $date_filter) as net_sell_price"),

                DB::raw("(SELECT sum(tsl.quantity_returned * tsl.unit_price_inc_tax) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id) $date_filter) as return_price"),

                DB::raw("(SELECT sum(tsl.quantity * tsl.line_discount_amount) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id) $date_filter) as discount_price"),

                DB::raw("(SELECT sum(tsl.quantity * tsl.item_tax) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id) $date_filter) as tax_price"),

                DB::raw("(SELECT sum(tsl.quantity * v.default_purchase_price) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id) $date_filter) as purchase_price")
            )->where('t.business_id',$business_id);

            if (request()->supplier_id !='') {
                    $query->where('t.contact_id',request()->supplier_id);
                    $sup=Contact::find(request()->supplier_id)->name;
            }


        $results=$query->groupBy('p.id')->orderBy('p.name')
            ->get();


        return view('report.supplier_sell_summery', compact('results','suppliers','sup'));

    }
//
    public function supplierProductStock(){

        $business_id = request()->session()->get('user.business_id');
        $suppliers=Contact::suppliersDropdown($business_id);
        $sup='';
        $query= DB::table('purchase_lines as pl')
            ->join('products as p','pl.product_id','=','p.id')
            ->join('variations as v','v.product_id','=','p.id')
            ->join('transactions as t','t.id','=','pl.transaction_id')
            ->select('p.id','p.name','p.sku','v.default_purchase_price','v.sell_price_inc_tax',
                DB::raw("SUM(pl.quantity) as purchase_qty"),
                DB::raw("(SELECT sum(tsl.quantity) FROM transaction_sell_lines as tsl
                          where (tsl.product_id=pl.product_id)) as sell_qty")
            )->where('t.business_id',$business_id);

            if (request()->supplier_id !='') {
                    $query->where('t.contact_id',request()->supplier_id);
                    $sup=Contact::find(request()->supplier_id)->name;
            }


        $results=$query->groupBy('p.id')->orderBy('p.name')
            ->get();

        return view('report.supplier_product_stock', compact('results','suppliers','sup'));

    }

    public function supplierStockReceive(){
        $business_id = request()->session()->get('user.business_id');
        $suppliers=Contact::suppliersDropdown($business_id);
        $sup='';
        $query=Transaction::with('contact')->where('type','purchase');

                if (request()->supplier_id !='') {
                    $query->where('contact_id',request()->supplier_id);
                    $sup=Contact::find(request()->supplier_id)->name;
                }

                if (!empty(request()->start) && !empty(request()->end)) {
                    $start = request()->start;
                    $end =  request()->end;
                    $query->whereDate('transaction_date', '>=', $start)
                                ->whereDate('transaction_date', '<=', $end);
                }
        $results=$query->paginate(30);
        return view('report.supplier_stock_receive', compact('suppliers','results','sup'));
    }

    public function StockReceiveDetails($id){
        $business_id = request()->session()->get('user.business_id');
        $row=Transaction::with('contact','purchase_lines')->where('type','purchase')->find($id);

        $cats=PurchaseLine::where('transaction_id',$id)
                ->join('products as p','p.id','=','purchase_lines.product_id')
                ->Leftjoin('categories as c','c.id','=','p.category_id')
                ->select('p.category_id','c.name')
                ->groupBy('p.category_id')
                ->pluck('c.name','p.category_id')
                ->toArray();
        $total_qty=$row->purchase_lines->sum('quantity');
        return view('report.stock_receive_details', compact('row','cats','total_qty'));
    }

    public function supplierStockTrack(){

        $results=DB::table('purchase_lines as pl')
            ->join('products as p','pl.product_id','=','p.id')
            ->join('variations as v','v.product_id','=','p.id')
            ->join('transactions as t','t.id','=','pl.transaction_id')
            ->select('p.id as product_id','p.name','p.sku','pl.product_id','pl.quantity','pl.variation_id','t.id as transaction_id','pl.id')
            ->where('p.name','COCACOLA CAN NO SUGAR')
            ->paginate(20);

        return view('report.supplier_stock_track', compact('results'));
    }
}
