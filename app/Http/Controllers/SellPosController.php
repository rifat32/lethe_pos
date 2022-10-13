<?php

namespace App\Http\Controllers;

use App\TaxRate;
use App\Transaction;
use App\TransactionSellLine;
use App\BusinessLocation;
use App\Business;
use App\User;
use App\Category;
use App\Brands;
use App\Product;
use App\CustomerGroup;
use App\SellingPriceGroup;
use App\NotificationTemplate;
use Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Contact;
use App\Transaction_update_delete_history;
use App\Transaction_sell_line_update_delete_history;
use App\Transaction_payment_update_delete_history;
use App\TransactionPayment;
use App\Sale_edit_delete_history;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class SellPosController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $contactUtil;
    protected $productUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $cashRegisterUtil;
    protected $moduleUtil;
    protected $notificationUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(
        ContactUtil $contactUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        CashRegisterUtil $cashRegisterUtil,
        ModuleUtil $moduleUtil,
        NotificationUtil $notificationUtil
    ) {

        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
        $this->notificationUtil = $notificationUtil;

        $this->dummyPaymentLine = [
            'method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => ''
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('sale_pos.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('HomeController@index'));
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellPosController@index'));
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create');
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);
        $payment_types = $this->productUtil->payment_types();

        $payment_lines[] = $this->dummyPaymentLine;

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_location = null;
        if (count($business_locations) == 1) {
            foreach ($business_locations as $id => $name) {
                $default_location = $id;
            }
        }

        //Shortcuts
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }

        $categories = Category::catAndSubCategories($business_id);
        $brands = Brands::where('business_id', $business_id)
            ->pluck('name', 'id');
        $brands->prepend(__('lang_v1.all_brands'), 'all');

        $change_return = $this->dummyPaymentLine;

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $selling_price_groups = SellingPriceGroup::forDropdown($business_id);

        //Accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);
        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);
        // dd($bl_attributes);
        // this is just to to show demo shop


        return view('sale_pos.create')
            ->with(compact(
                'business_details',
                'taxes',
                'payment_types',
                'walk_in_customer',
                'payment_lines',
                'business_locations',
                'bl_attributes',
                'default_location',
                'shortcuts',
                'commission_agent',
                'categories',
                'brands',
                'pos_settings',
                'change_return',
                'types',
                'customer_groups',
                'accounts',
                'price_groups',
                'selling_price_groups'
            ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
  
    public function store(Request $request)
    {


        if (!auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        $is_direct_sale = false;
        if (!empty($request->input('is_direct_sale'))) {
            $is_direct_sale = true;
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create');
        }

        try {
            $input = $request->except('_token');

            //Check Customer credit limit
            $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($input);

            if ($is_credit_limit_exeeded !== false) {
                $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                $output = [
                    'success' => 0,
                    'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount])
                ];
                if (!$is_direct_sale) {
                    return $output;
                }
                else if(!empty($input["order_id"])) {
                    return redirect('ecommerce-sells')->with('status', $output);
                
                }
                
                else {
                    return redirect('sells')->with('status', $output);
                }
            }

            //status is send as quotation from Add sales screen.
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_quotation'] = 1;
            }

            if (!empty($input['products'])) {
                $business_id = $request->session()->get('user.business_id');

                //Check if subscribed or not, then check for users quota
                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse();
                } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
                    return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellPosController@index'));
                }

                $user_id = $request->session()->get('user.id');
                $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');

                $discount = [
                    'discount_type' => $input['discount_type'],
                    'discount_amount' => $input['discount_amount']
                ];
                $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

                DB::beginTransaction();

                if (empty($request->input('transaction_date'))) {
                    $input['transaction_date'] =  \Carbon::now();
                } else {
                    $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
                }
                if ($is_direct_sale) {
                    $input['is_direct_sale'] = 1;
                }

                $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
                if ($commsn_agnt_setting == 'logged_in_user') {
                    $input['commission_agent'] = $user_id;
                }

                if (isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0) {
                    $input['exchange_rate'] = 1;
                }

                //Customer group details
                $contact_id = $request->get('contact_id', null);
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

                //set selling price group id
                if ($request->has('price_group')) {
                    $input['selling_price_group_id'] = $request->input('price_group');
                }

                $input['is_suspend'] = isset($input['is_suspend']) && 1 == $input['is_suspend']  ? 1 : 0;
                if ($input['is_suspend']) {
                    $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
                }

            
                $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);
              

              
                $this->transactionUtil->createOrUpdateSellLines(
                    $transaction,
                     $input['products'],
                      $contact_id, 
                      $input['location_id'], 
                      false,
                       null,
                        [],
                         $input);

                if (!$is_direct_sale && empty($input["is_ecommerce_sale"])) {
                    //Add change return
                    $change_return = $this->dummyPaymentLine;
                    $change_return['amount'] = $input['change_return'];
                    $change_return['is_return'] = 1;
                    $input['payment'][] = $change_return;
                }


                if (!$transaction->is_suspend) {
                    // $input['payment'][0]['bank_name']=$request->bank_name;
                    //  dd( $input['payment'][0]['bank_name']);
                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
                }


                if ($this->transactionUtil->isModuleEnabled('tables')) {
                    $transaction->res_table_id = request()->get('res_table_id');
                    $transaction->save();
                }
                if ($this->transactionUtil->isModuleEnabled('service_staff')) {
                    $transaction->res_waiter_id = request()->get('res_waiter_id');
                    $transaction->save();
                }

                //Check for final and do some processing.
                if ($input['status'] == 'final') {
                    //update product stock
                    foreach ($input['products'] as $product) {
                        // return response()->json($product,500);

                        if ($product['enable_stock']) {
                            $this->productUtil->decreaseProductQuantity(
                                $product['product_id'],
                                $product['variation_id'],
                                $input['location_id'],
                                $this->productUtil->num_uf($product['quantity'])
                            );
                        }
                    }

                    //Add payments to Cash Register
                    if ($input['payment'][0]['method'] == "advance") {
                        $up_amount =  $input['payment'][0]['amount'];
                        $payment_for = $input['contact_id'];
                        $business_id = $request->session()->get('user.business_id');
                        $expense_category = Contact::where('business_id', $business_id)->findOrFail($payment_for);
                        if ($up_amount <= $expense_category->balance) {
                            $expense_category->balance -= $up_amount;
                            $expense_category->save();
                        } else {
                            $output = [
                                'success' => false,
                                'msg' => __("You Do not have enough Balance!")
                            ];
                            //return Redirect::back()->withErrors(['You Dont have Enough Balance']);
                            return $output;
                        }
                    }

                    ///hey jacky work here 590 on transaction util
                    //Add payments to Card Register
                    // if($input['payment'][0]['method']=="card"){
                    //     $up_amount =  $input['payment'][0]['amount'];
                    //    $bank_name=$request->bank_name;
                    //    if($bank_name=="sk")
                    //    {
                    //      $tax_amount=  ($up_amount*3)/100;
                    //      $amount= $up_amount- $tax_amount;
                    //      //echo $amount;

                    //    }
                    //    else
                    //    {
                    //     $tax_amount=  ($up_amount*5)/100;
                    //     $amount= $up_amount- $tax_amount;

                    //    }

                    // }





                    if (!$is_direct_sale && !$transaction->is_suspend) {
                        $this->cashRegisterUtil->addSellPayments($transaction, $input['payment']);
                    }

                    //Update payment status
                    $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

                    //Allocate the quantity from purchase and add mapping of
                    //purchase & sell lines in
                    //transaction_sell_lines_purchase_lines table
                    $business = [
                        'id' => $business_id,
                        'accounting_method' => $request->session()->get('business.accounting_method'),
                        'location_id' => $input['location_id']
                    ];

                    $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');

                    //Auto send notification
                    // $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);
                }

                DB::commit();

                $msg = '';
                $receipt = '';
                if ($input['status'] == 'draft' && $input['is_quotation'] == 0) {
                    $msg = trans("sale.draft_added");
                } elseif ($input['status'] == 'draft' && $input['is_quotation'] == 1) {
                    $msg = trans("lang_v1.quotation_added");
                    if (!$is_direct_sale) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
                    } else {
                        $receipt = '';
                    }
                } elseif ($input['status'] == 'final') {
                    $msg = trans("sale.pos_sale_added");
                    if (!$is_direct_sale && !$transaction->is_suspend) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
                    } else {
                        $receipt = '';
                    }
                }

                $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt];
            } else {
                $output = [
                    'success' => 0,
                    'msg' => trans("messages.something_went_wrong")
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();

            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            } else {
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
                $msg =  $e->getMessage();
            }

            $output = [
                'success' => 0,
                'msg' => "File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage()
            ];
        }

        if (!$is_direct_sale && empty($input["is_ecommerce_sale"])) {
            return $output;
        } else {
            if ($input['status'] == 'draft') {
                if (isset($input['is_quotation']) && $input['is_quotation'] == 1) {
                    return redirect()
                        ->action('SellController@getQuotations')
                        ->with('status', $output);
                } else {
                    return redirect()
                        ->action('SellController@getDrafts')
                        ->with('status', $output);
                }
            } 
            else if(!empty($input["order_id"])) {
                return redirect('ecommerce-sells')->with('status', $output);
            
            }
            else {
                return redirect('sells')->with('status', $output);
            }
        }
    }

    /**
     * Returns the content for the receipt
     *
     * @param  int  $business_id
     * @param  int  $location_id
     * @param  int  $transaction_id
     * @param string $printer_type = null
     *
     * @return array
     */
    private function receiptContent(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null
    ) {

        $output = [
            'is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => []
        ];

        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);

        //Check if printing of invoice is enabled or not.
        if ($location_details->print_receipt_on_invoice == 1) {
            //If enabled, get print type.
            $output['is_enabled'] = true;

            $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $location_id, $location_details->invoice_layout_id);

            //Check if printer setting is provided.
            $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;

            $receipt_details = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);

            $receipt_details->currency = session('currency');

            //If print type browser - return the content, printer - return printer config data, and invoice format config
            if ($receipt_printer_type == 'printer') {
                $output['print_type'] = 'printer';
                $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
                $output['data'] = $receipt_details;
            } else {
                $layout = !empty($receipt_details->design) ? 'sale_pos.receipts.' . $receipt_details->design : 'sale_pos.receipts.classic';

                $output['html_content'] = view($layout, compact('receipt_details'))->render();
            }
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
        if (!auth()->user()->can('sell.update')) {
            abort(403, 'Unauthorized action.');
        }
      

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', [
                    'success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])
                ]);
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create');
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', [
                'success' => 0,
                'msg' => __('lang_v1.return_exist')
            ]);
        }

        $business_id = request()->session()->get('user.business_id');
        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);
        $payment_types = $this->productUtil->payment_types();

        $transaction = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->findorfail($id);

        $location_id = $transaction->location_id;
        $location_printer_type = BusinessLocation::find($location_id)->receipt_printer_type;

        $sell_details = TransactionSellLine::join(
                'products AS p',
                'transaction_sell_lines.product_id',
                '=',
                'p.id'
            )
            ->join(
                'variations AS variations',
                'transaction_sell_lines.variation_id',
                '=',
                'variations.id'
            )
            ->join(
                'product_variations AS pv',
                'variations.product_variation_id',
                '=',
                'pv.id'
            )
            ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                $join->on('variations.id', '=', 'vld.variation_id')
                    ->where('vld.location_id', '=', $location_id);
            })
            ->leftjoin('units', 'units.id', '=', 'p.unit_id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->select(
                DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                'p.id as product_id',
                'p.enable_stock',
                'p.name as product_actual_name',
                'pv.name as product_variation_name',
                'pv.is_dummy as is_dummy',
                'variations.name as variation_name',
                'variations.sub_sku',
                'p.barcode_type',
                'p.enable_sr_no',
                'variations.id as variation_id',
                'units.short_name as unit',
                'units.allow_decimal as unit_allow_decimal',
                'transaction_sell_lines.tax_id as tax_id',
                'transaction_sell_lines.item_tax as item_tax',
                'transaction_sell_lines.unit_price as default_sell_price',
                'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                'transaction_sell_lines.id as transaction_sell_lines_id',
                'transaction_sell_lines.quantity as quantity_ordered',
                'transaction_sell_lines.sell_line_note as sell_line_note',
                'transaction_sell_lines.parent_sell_line_id',
                'transaction_sell_lines.lot_no_line_id',
                'transaction_sell_lines.line_discount_type',
                'transaction_sell_lines.line_discount_amount',
                'transaction_sell_lines.doctor_id',
                'transaction_sell_lines.assistant_id',
                DB::raw('vld.qty_available + transaction_sell_lines.quantity AS qty_available')
            )
            ->get();
            
        if (!empty($sell_details)) {
            foreach ($sell_details as $key => $value) {
                //If modifier sell line then unset
                if (!empty($sell_details[$key]->parent_sell_line_id)) {
                    unset($sell_details[$key]);
                } else {
                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available);

                    //Add available lot numbers for dropdown to sell lines
                    $lot_numbers = [];
                    if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                        $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                        foreach ($lot_number_obj as $lot_number) {
                            //If lot number is selected added ordered quantity to lot quantity available
                            if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                                $lot_number->qty_available += $value->quantity_ordered;
                            }

                            $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                            $lot_numbers[] = $lot_number;
                        }
                    }
                    $sell_details[$key]->lot_numbers = $lot_numbers;

                    if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                        //Add modifier details to sel line details
                        $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)->get();
                        $modifiers_ids = [];
                        if (count($sell_line_modifiers) > 0) {
                            $sell_details[$key]->modifiers = $sell_line_modifiers;
                            foreach ($sell_line_modifiers as $sell_line_modifier) {
                                $modifiers_ids[] = $sell_line_modifier->variation_id;
                            }
                        }
                        $sell_details[$key]->modifiers_ids = $modifiers_ids;

                        //add product modifier sets for edit
                        $this_product = Product::find($sell_details[$key]->product_id);
                        if (count($this_product->modifier_sets) > 0) {
                            $sell_details[$key]->product_ms = $this_product->modifier_sets;
                        }
                    }
                }
            }
        }

        $payment_lines = $this->transactionUtil->getPaymentDetails($id);
        //If no payment lines found then add dummy payment line.
        if (empty($payment_lines)) {
            $payment_lines[] = $this->dummyPaymentLine;
        }

        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }

        $categories = Category::catAndSubCategories($business_id);
        $brands = Brands::where('business_id', $business_id)
            ->pluck('name', 'id');
        $brands->prepend(__('lang_v1.all_brands'), 'all');

        $change_return = $this->dummyPaymentLine;

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $selling_price_groups = SellingPriceGroup::forDropdown($business_id);


        //Accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);
        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);
       
    
        return view('sale_pos.edit')
            ->with(compact('business_details', 'taxes', 'payment_types', 'walk_in_customer', 'sell_details', 'transaction', 'payment_lines', 'location_printer_type', 'shortcuts', 'commission_agent', 'categories', 'pos_settings', 'change_return', 'types', 'customer_groups', 'brands', 'accounts', 'price_groups', 'selling_price_groups'));
    }
    public function assistantSellDetails($id)
    {
        if (!auth()->user()->can('sell.update')) {
            abort(403, 'Unauthorized action.');
        }
      

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', [
                    'success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])
                ]);
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create');
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', [
                'success' => 0,
                'msg' => __('lang_v1.return_exist')
            ]);
        }

        $business_id = request()->session()->get('user.business_id');
        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);
        $payment_types = $this->productUtil->payment_types();

        $transaction = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->findorfail($id);

        $location_id = $transaction->location_id;
        $location_printer_type = BusinessLocation::find($location_id)->receipt_printer_type;

        $sell_details = TransactionSellLine::join(
                'products AS p',
                'transaction_sell_lines.product_id',
                '=',
                'p.id'
            )
            ->join(
                'variations AS variations',
                'transaction_sell_lines.variation_id',
                '=',
                'variations.id'
            )
            ->join(
                'product_variations AS pv',
                'variations.product_variation_id',
                '=',
                'pv.id'
            )
            ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                $join->on('variations.id', '=', 'vld.variation_id')
                    ->where('vld.location_id', '=', $location_id);
            })
            ->leftjoin('units', 'units.id', '=', 'p.unit_id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->select(
                DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                'p.id as product_id',
                'p.enable_stock',
                'p.name as product_actual_name',
                'pv.name as product_variation_name',
                'pv.is_dummy as is_dummy',
                'variations.name as variation_name',
                'variations.sub_sku',
                'p.barcode_type',
                'p.enable_sr_no',
                'variations.id as variation_id',
                'units.short_name as unit',
                'units.allow_decimal as unit_allow_decimal',
                'transaction_sell_lines.tax_id as tax_id',
                'transaction_sell_lines.item_tax as item_tax',
                'transaction_sell_lines.unit_price as default_sell_price',
                'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                'transaction_sell_lines.id as transaction_sell_lines_id',
                'transaction_sell_lines.quantity as quantity_ordered',
                'transaction_sell_lines.sell_line_note as sell_line_note',
                'transaction_sell_lines.parent_sell_line_id',
                'transaction_sell_lines.lot_no_line_id',
                'transaction_sell_lines.line_discount_type',
                'transaction_sell_lines.line_discount_amount',
                'transaction_sell_lines.doctor_id',
                'transaction_sell_lines.assistant_id',
                DB::raw('vld.qty_available + transaction_sell_lines.quantity AS qty_available')
            )
            ->get();
            
        if (!empty($sell_details)) {
            foreach ($sell_details as $key => $value) {
                //If modifier sell line then unset
                if (!empty($sell_details[$key]->parent_sell_line_id)) {
                    unset($sell_details[$key]);
                } else {
                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available);

                    //Add available lot numbers for dropdown to sell lines
                    $lot_numbers = [];
                    if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                        $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                        foreach ($lot_number_obj as $lot_number) {
                            //If lot number is selected added ordered quantity to lot quantity available
                            if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                                $lot_number->qty_available += $value->quantity_ordered;
                            }

                            $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                            $lot_numbers[] = $lot_number;
                        }
                    }
                    $sell_details[$key]->lot_numbers = $lot_numbers;

                    if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                        //Add modifier details to sel line details
                        $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)->get();
                        $modifiers_ids = [];
                        if (count($sell_line_modifiers) > 0) {
                            $sell_details[$key]->modifiers = $sell_line_modifiers;
                            foreach ($sell_line_modifiers as $sell_line_modifier) {
                                $modifiers_ids[] = $sell_line_modifier->variation_id;
                            }
                        }
                        $sell_details[$key]->modifiers_ids = $modifiers_ids;

                        //add product modifier sets for edit
                        $this_product = Product::find($sell_details[$key]->product_id);
                        if (count($this_product->modifier_sets) > 0) {
                            $sell_details[$key]->product_ms = $this_product->modifier_sets;
                        }
                    }
                }
            }
        }

        $payment_lines = $this->transactionUtil->getPaymentDetails($id);
        //If no payment lines found then add dummy payment line.
        if (empty($payment_lines)) {
            $payment_lines[] = $this->dummyPaymentLine;
        }

        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }

        $categories = Category::catAndSubCategories($business_id);
        $brands = Brands::where('business_id', $business_id)
            ->pluck('name', 'id');
        $brands->prepend(__('lang_v1.all_brands'), 'all');

        $change_return = $this->dummyPaymentLine;

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $selling_price_groups = SellingPriceGroup::forDropdown($business_id);


        //Accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);
        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);
       
    
        return view('sale_pos.assistant-details')
            ->with(compact('business_details', 'taxes', 'payment_types', 'walk_in_customer', 'sell_details', 'transaction', 'payment_lines', 'location_printer_type', 'shortcuts', 'commission_agent', 'categories', 'pos_settings', 'change_return', 'types', 'customer_groups', 'brands', 'accounts', 'price_groups', 'selling_price_groups'));
    }
    
    /**
     * Update the specified resource in storage.
     * TODO: Add edit log.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('sell.update') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }



        try {
            $input = $request->except('_token');

            //status is send as quotation from edit sales screen.
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_quotation'] = 1;
            }

            $is_direct_sale = false;
            if (!empty($input['products'])) {
                
                //Get transaction value before updating.
                $transaction_before = Transaction::find($id);
                $status_before =  $transaction_before->status;

                if ($transaction_before->is_direct_sale == 1) {
                    $is_direct_sale = true;
                }

                //Check Customer credit limit
                $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($input, $id);

                if ($is_credit_limit_exeeded !== false) {
                    $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                    $output = [
                        'success' => 0,
                        'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount])
                    ];
                    if (!$is_direct_sale) {
                        return $output;
                    }
                    else if(!empty($input["order_id"])) {
                        return redirect('ecommerce-sells')->with('status', $output);
                    
                    }
                    
                    else {
                        return redirect('sells')->with('status', $output);
                    }
                }

                //Check if there is a open register, if no then redirect to Create Register screen.
                if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
                    return redirect()->action('CashRegisterController@create');
                }

                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');

                $discount = [
                    'discount_type' => $input['discount_type'],
                    'discount_amount' => $input['discount_amount']
                ];
                $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

                if (!empty($request->input('transaction_date'))) {
                    $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
                }

                $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
                if ($commsn_agnt_setting == 'logged_in_user') {
                    $input['commission_agent'] = $user_id;
                }

                if (isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0) {
                    $input['exchange_rate'] = 1;
                }
                
                //Customer group details
                $contact_id = $request->get('contact_id', null);
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

                //set selling price group id
                if ($request->has('price_group')) {
                    $input['selling_price_group_id'] = $request->input('price_group');
                }

                $input['is_suspend'] = isset($input['is_suspend']) && 1 == $input['is_suspend']  ? 1 : 0;
                if ($input['is_suspend']) {
                    $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
                }

                //Begin transaction
                DB::beginTransaction();

                $this->insertUpdateDeleteTransactionExistingData($id, "Edit");

                $transaction = $this->transactionUtil->updateSellTransaction($id, $business_id, $input, $invoice_total, $user_id);

                //Update Sell lines
                $deleted_lines = $this->transactionUtil->createOrUpdateSellLines($transaction,
                 $input['products'],
                 $contact_id ,
                  $input['location_id'],
                   true,
                      null,
                       [],
                        $input);

              
    
                //Update update lines
                if (!$is_direct_sale && !$transaction->is_suspend) {
                    //Add change return
                    $change_return = $this->dummyPaymentLine;
                    $change_return['amount'] = $input['change_return'];
                    $change_return['is_return'] = 1;
                    if (!empty($input['change_return_id'])) {
                        $change_return['id'] = $input['change_return_id'];
                    }
                    $input['payment'][] = $change_return;

                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);

                    //Update cash register
                    $this->cashRegisterUtil->updateSellPayments($status_before, $transaction, $input['payment']);
                }

                //Update payment status
                $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

                //Update product stock
                $this->productUtil->adjustProductStockForInvoice($status_before, $transaction, $input);

                //Allocate the quantity from purchase and add mapping of
                //purchase & sell lines in
                //transaction_sell_lines_purchase_lines table
                $business = [
                    'id' => $business_id,
                    'accounting_method' => $request->session()->get('business.accounting_method'),
                    'location_id' => $input['location_id']
                ];
                $this->transactionUtil->adjustMappingPurchaseSell($status_before, $transaction, $business, $deleted_lines);

                if ($this->transactionUtil->isModuleEnabled('tables')) {
                    $transaction->res_table_id = request()->get('res_table_id');
                    $transaction->save();
                }
                if ($this->transactionUtil->isModuleEnabled('service_staff')) {
                    $transaction->res_waiter_id = request()->get('res_waiter_id');
                    $transaction->save();
                }

                //====== sale edit delete history==============
                $edit_delete = new Sale_edit_delete_history();
                $edit_delete->transaction_id = $id;
                $edit_delete->action_type = "Edit";
                $edit_delete->action_by =  Auth::user()->id;
                $edit_delete->save();
                //====== sale edit delete history==============

                DB::commit();



                $msg = '';
                $receipt = '';

                if ($input['status'] == 'draft' && $input['is_quotation'] == 0) {
                    $msg = trans("sale.draft_added");
                } elseif ($input['status'] == 'draft' && $input['is_quotation'] == 1) {
                    $msg = trans("lang_v1.quotation_updated");
                    if (!$is_direct_sale) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
                    } else {
                        $receipt = '';
                    }
                } elseif ($input['status'] == 'final') {
                    $msg = trans("sale.pos_sale_updated");
                    if (!$is_direct_sale && !$transaction->is_suspend) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
                    } else {
                        $receipt = '';
                        if($transaction->order_id) {
                            $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
                            
                        }
                    }
                }

                $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt];
            } else {
                $output = [
                    'success' => 0,
                    'msg' => trans("messages.something_went_wrong")
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getFile()
            ];
        }

        if (!$is_direct_sale) {
            return $output;
        } else {
            if($transaction->order_id) {
                return $output;
                
            }
            if ($input['status'] == 'draft') {
                if (isset($input['is_quotation']) && $input['is_quotation'] == 1) {
                    return redirect()
                        ->action('SellController@getQuotations')
                        ->with('status', $output);
                } else {
                    return redirect()
                        ->action('SellController@getDrafts')
                        ->with('status', $output);
                }
            }
            else if(!empty($input["order_id"])) {
                return redirect('ecommerce-sells')->with('status', $output);
            
            }

            else {
                return redirect('sells')->with('status', $output);
            }
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
        if (!auth()->user()->can('sell.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                //Check if return exist then not allowed
                if ($this->transactionUtil->isReturnExist($id)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.return_exist')
                    ];
                    return $output;
                }

                $business_id = request()->session()->get('user.business_id');
                $transaction = Transaction::where('id', $id)
                    ->where('business_id', $business_id)
                    ->where('type', 'sell')
                    ->with(['sell_lines'])
                    ->first();

                //Begin transaction
                DB::beginTransaction();

                $this->insertUpdateDeleteTransactionExistingData($id, "Delete");
                //====== sale edit delete history==============
                $edit_delete = new Sale_edit_delete_history();
                $edit_delete->transaction_id = $id;
                $edit_delete->action_type = "Delete";
                $edit_delete->action_by =  Auth::user()->id;
                $edit_delete->save();
                //====== sale edit delete history==============

                if (!empty($transaction)) {
                    //If status is draft direct delete transaction
                    if ($transaction->status == 'draft') {
                        $transaction->delete();
                    } else {
                        $deleted_sell_lines = $transaction->sell_lines;
                        $deleted_sell_lines_ids = $deleted_sell_lines->pluck('id')->toArray();
                        $this->transactionUtil->deleteSellLines(
                            $deleted_sell_lines_ids,
                            $transaction->location_id
                        );

                        $transaction->status = 'draft';
                        $business = [
                            'id' => $business_id,
                            'accounting_method' => request()->session()->get('business.accounting_method'),
                            'location_id' => $transaction->location_id
                        ];

                        $this->transactionUtil->adjustMappingPurchaseSell('final', $transaction, $business, $deleted_sell_lines_ids);

                        $transaction->delete();
                    }
                }


                DB::commit();
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.sale_delete_success')
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output['success'] = false;
                $output['msg'] = trans("messages.something_went_wrong");
            }

            return $output;
        }
    }


    public function insertUpdateDeleteTransactionExistingData($transaction_id, $action_type)
    {
        $oldData =  Transaction::findOrFail($transaction_id);
        $this->insertUpdateDeleteTransactionSellLineExistingData($transaction_id, $action_type);
        if ($oldData->payment_status == "paid") {
            $this->insertUpdateDeleteTransactionPaymentExistingData($transaction_id, $action_type);
        }

        $data = new Transaction_update_delete_history();
        $data->transaction_id = $transaction_id;
        $data->action_by = Auth::user()->id;
        $data->action_type = $action_type;
        $data->business_id = $oldData->business_id;
        $data->location_id = $oldData->location_id;
        $data->res_table_id = $oldData->res_table_id;
        $data->res_waiter_id = $oldData->res_waiter_id;
        $data->res_order_status = $oldData->res_order_status;
        $data->type = $oldData->type;
        $data->status = $oldData->status;
        $data->is_quotation = $oldData->is_quotation;
        $data->payment_status = $oldData->payment_status;
        $data->adjustment_type = $oldData->adjustment_type;
        $data->contact_id = $oldData->contact_id;
        $data->customer_group_id = $oldData->customer_group_id;
        $data->invoice_no = $oldData->invoice_no;
        $data->ref_no = $oldData->ref_no;
        $data->transaction_date = $oldData->transaction_date;
        $data->total_before_tax = $oldData->total_before_tax;
        $data->tax_id = $oldData->tax_id;
        $data->tax_amount = $oldData->tax_amount;
        $data->discount_type = $oldData->discount_type;
        $data->discount_amount = $oldData->discount_amount;
        $data->shipping_details = $oldData->shipping_details;
        $data->shipping_charges = $oldData->shipping_charges;
        $data->additional_notes = $oldData->additional_notes;
        $data->staff_note = $oldData->staff_note;
        $data->final_total = $oldData->final_total;
        $data->expense_category_id = $oldData->expense_category_id;
        $data->expense_for = $oldData->expense_for;
        $data->commission_agent = $oldData->commission_agent;
        $data->document = $oldData->document;
        $data->is_direct_sale = $oldData->is_direct_sale;
        $data->is_suspend = $oldData->is_suspend;
        $data->exchange_rate = $oldData->exchange_rate;
        $data->total_amount_recovered = $oldData->total_amount_recovered;
        $data->transfer_parent_id = $oldData->transfer_parent_id;
        $data->return_parent_id = $oldData->return_parent_id;
        $data->opening_stock_product_id = $oldData->opening_stock_product_id;
        $data->created_by = $oldData->created_by;
        $data->pay_term_number = $oldData->pay_term_number;
        $data->pay_term_type = $oldData->pay_term_type;
        $data->woocommerce_order_id = $oldData->woocommerce_order_id;
        $data->selling_price_group_id = $oldData->selling_price_group_id;
        $data->supplier_id = $oldData->supplier_id;
        $data->sale_created_date = $oldData->created_at;
        $data->sale_updated_date = $oldData->updated_at;
        $data->save();
        return $data;
    }


    public function insertUpdateDeleteTransactionSellLineExistingData($transaction_id, $action_type)
    {
        $oldData = TransactionSellLine::where('transaction_id', $transaction_id)->first();
        $data = new Transaction_sell_line_update_delete_history();
        $data->action_type = $action_type;
        $data->action_by = Auth::user()->id;
        $data->transaction_id = $oldData->transaction_id;
        $data->product_id = $oldData->product_id;
        $data->variation_id = $oldData->variation_id;
        $data->quantity = $oldData->quantity;
        $data->quantity_returned = $oldData->quantity_returned;
        $data->unit_price_before_discount = $oldData->unit_price_before_discount;
        $data->unit_price = $oldData->unit_price;
        $data->mrp_price = $oldData->mrp_price;
        $data->line_discount_type = $oldData->line_discount_type;
        $data->line_discount_amount = $oldData->line_discount_amount;
        $data->unit_price_inc_tax = $oldData->unit_price_inc_tax;
        $data->item_tax = $oldData->item_tax;
        $data->tax_id = $oldData->tax_id;
        $data->lot_no_line_id = $oldData->lot_no_line_id;
        $data->sell_line_note = $oldData->sell_line_note;
        $data->woocommerce_line_items_id = $oldData->woocommerce_line_items_id;
        $data->sell_created_date = $oldData->created_at;
        $data->sell_updated_date = $oldData->updated_at;
        $data->save();
        return $data;
    }


    public function insertUpdateDeleteTransactionPaymentExistingData($transaction_id, $action_type)
    {
        $oldData = TransactionPayment::where('transaction_id', $transaction_id)->first();

        $data = new Transaction_payment_update_delete_history();
        $data->transaction_id = $oldData->transaction_id;
        $data->action_type = $action_type;
        $data->action_by = Auth::user()->id;
        $data->business_id = $oldData->business_id;
        $data->is_return = $oldData->is_return;
        $data->amount = $oldData->amount;
        $data->method = $oldData->method;
        $data->transaction_no = $oldData->transaction_no;
        $data->card_transaction_number = $oldData->card_transaction_number;
        $data->bank_name = $oldData->bank_name;
        $data->tax_amount = $oldData->tax_amount;
        $data->remain_amount = $oldData->remain_amount;
        $data->card_number = $oldData->card_number;
        $data->card_type = $oldData->card_type;
        $data->card_holder_name = $oldData->card_holder_name;
        $data->card_month = $oldData->card_month;
        $data->card_year = $oldData->card_year;
        $data->card_security = $oldData->card_security;
        $data->cheque_number = $oldData->cheque_number;
        $data->bank_account_number = $oldData->bank_account_number;
        $data->paid_on = $oldData->paid_on;
        $data->created_by = $oldData->created_by;
        $data->payment_for = $oldData->payment_for;
        $data->parent_id = $oldData->parent_id;
        $data->note = $oldData->note;
        $data->payment_ref_no = $oldData->payment_ref_no;
        $data->account_id = $oldData->account_id;
        $data->payment_created_date = $oldData->created_at;
        $data->payment_updated_date = $oldData->updated_at;
        $data->save();
        return $data;
    }




    /**
     * Returns the HTML row for a product in POS
     *
     * @param  int  $variation_id
     * @param  int  $location_id
     * @return \Illuminate\Http\Response
     */
    public function getProductRow($variation_id, $location_id)
    {
        $output = [];

        try {
            $row_count = request()->get('product_row');
            $row_count = $row_count + 1;
            $is_direct_sell = false;
            if (request()->get('is_direct_sell') == 'true') {
                $is_direct_sell = true;
            }

            $business_id = request()->session()->get('user.business_id');

            $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id);
            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);

            //Get customer group and change the price accordingly
            $customer_id = request()->get('customer_id', null);
            $cg = $this->contactUtil->getCustomerGroup($business_id, $customer_id);
            $percent = (empty($cg) || empty($cg->amount)) ? 0 : $cg->amount;
            $cpg = $this->contactUtil->getCustomerPriceGroup($business_id, $customer_id);

            //dd($cpg->selling_price_groups_id);


            //code for reseller
            //     $reseller_check=DB::Table('contacts')
            //     ->where('id',$customer_id)
            //     ->first();
            //    //dd($reseller_check);
            //     if($reseller_check->customer_type=="reseller")
            //     {
            //         $reseller_price=DB::Table('products')
            //             ->where('id',$product->product_id)
            //             ->first();
            //             if( $reseller_price)
            //             {
            //                 $product->default_sell_price = $reseller_price->reseller_price + ($percent * $product->default_sell_price / 100);
            //                 $product->sell_price_inc_tax = $product->sell_price_inc_tax + ($percent * $product->sell_price_inc_tax / 100);
            //             }
            //     }
            //end code for reseller

            $product->default_sell_price = $product->default_sell_price + ($percent * $product->default_sell_price / 100);
            $product->sell_price_inc_tax = $product->sell_price_inc_tax + ($percent * $product->sell_price_inc_tax / 100);




            $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);

            $enabled_modules = $this->transactionUtil->allModulesEnabled();

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

            if ($customer_id > 0 && $cpg->selling_price_groups_id > 0) {
                $variation_group_prices = $this->productUtil->getVariationGroupPrice($variation_id, $cpg->selling_price_groups_id, $product->tax_id);
                if (!empty($variation_group_prices['price_inc_tax'])) {
                    $product->sell_price_inc_tax = $variation_group_prices['price_inc_tax'];
                    $product->default_sell_price = $variation_group_prices['price_exc_tax'];
                }
            }

            $price_group = request()->input('price_group');
            if (!empty($price_group)) {
                $variation_group_prices = $this->productUtil->getVariationGroupPrice($variation_id, $price_group, $product->tax_id);

                if (!empty($variation_group_prices['price_inc_tax'])) {
                    $product->sell_price_inc_tax = $variation_group_prices['price_inc_tax'];
                    $product->default_sell_price = $variation_group_prices['price_exc_tax'];
                }
            }

            $business_details = $this->businessUtil->getDetails($business_id);
            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);


            $output['success'] = true;
            if (request()->get('type') == 'sell-return') {
                $output['html_content'] =  view('sell_return.partials.product_row')
                    ->with(compact('product', 'row_count', 'tax_dropdown', 'enabled_modules'))
                    ->render();
            } else {
                $output["product_category_id"] = Product::where([
                    "id" => $product->product_id
                ])
                    ->first()
                    ->category_id;
                $product_category_id = $output["product_category_id"];
                $output['html_content'] =  view('sale_pos.product_row')
                    ->with(compact('product', 'row_count', 'tax_dropdown', 'enabled_modules', 'pos_settings', 'product_category_id'))
                    ->render();
            }

            $output['enable_sr_no'] = $product->enable_sr_no;

            if ($this->transactionUtil->isModuleEnabled('modifiers')  && !$is_direct_sell) {
                $this_product = Product::where('business_id', $business_id)
                    ->find($product->product_id);
                if (count($this_product->modifier_sets) > 0) {
                    $product_ms = $this_product->modifier_sets;
                    $output['html_modifier'] =  view('restaurant.product_modifier_set.modifier_for_product')
                        ->with(compact('product_ms', 'row_count'))->render();
                }
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output['success'] = false;
            $output['msg'] = $e->getMessage();
        }

        return $output;
    }
    public function getProductRow2($variation_id, $location_id)
    {
        $output = [];

        try {
            $row_count = request()->get('product_row');
            $row_count = $row_count + 1;
            $is_direct_sell = false;
            if (request()->get('is_direct_sell') == 'true') {
                $is_direct_sell = true;
            }

            $business_id = request()->session()->get('user.business_id');

            $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id);
            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available);

            //Get customer group and change the price accordingly
            $customer_id = request()->get('customer_id', null);
            $cg = $this->contactUtil->getCustomerGroup($business_id, $customer_id);
            $percent = (empty($cg) || empty($cg->amount)) ? 0 : $cg->amount;
            $cpg = $this->contactUtil->getCustomerPriceGroup($business_id, $customer_id);

            //dd($cpg->selling_price_groups_id);


            //code for reseller
            //     $reseller_check=DB::Table('contacts')
            //     ->where('id',$customer_id)
            //     ->first();
            //    //dd($reseller_check);
            //     if($reseller_check->customer_type=="reseller")
            //     {
            //         $reseller_price=DB::Table('products')
            //             ->where('id',$product->product_id)
            //             ->first();
            //             if( $reseller_price)
            //             {
            //                 $product->default_sell_price = $reseller_price->reseller_price + ($percent * $product->default_sell_price / 100);
            //                 $product->sell_price_inc_tax = $product->sell_price_inc_tax + ($percent * $product->sell_price_inc_tax / 100);
            //             }
            //     }
            //end code for reseller

            $product->default_sell_price = $product->default_sell_price + ($percent * $product->default_sell_price / 100);
            $product->sell_price_inc_tax = $product->sell_price_inc_tax + ($percent * $product->sell_price_inc_tax / 100);




            $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);

            $enabled_modules = $this->transactionUtil->allModulesEnabled();

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

            if ($customer_id > 0 && $cpg->selling_price_groups_id > 0) {
                $variation_group_prices = $this->productUtil->getVariationGroupPrice($variation_id, $cpg->selling_price_groups_id, $product->tax_id);
                if (!empty($variation_group_prices['price_inc_tax'])) {
                    $product->sell_price_inc_tax = $variation_group_prices['price_inc_tax'];
                    $product->default_sell_price = $variation_group_prices['price_exc_tax'];
                }
            }

            $price_group = request()->input('price_group');
            if (!empty($price_group)) {
                $variation_group_prices = $this->productUtil->getVariationGroupPrice($variation_id, $price_group, $product->tax_id);

                if (!empty($variation_group_prices['price_inc_tax'])) {
                    $product->sell_price_inc_tax = $variation_group_prices['price_inc_tax'];
                    $product->default_sell_price = $variation_group_prices['price_exc_tax'];
                }
            }

            $business_details = $this->businessUtil->getDetails($business_id);
            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);


            $output['success'] = true;
            if (request()->get('type') == 'sell-return') {
                $output['html_content'] =  view('sell_return.partials.product_row')
                    ->with(compact('product', 'row_count', 'tax_dropdown', 'enabled_modules'))
                    ->render();
            } else {
                $output["product_category_id"] = Product::where([
                    "id" => $product->product_id
                ])
                    ->first()
                    ->category_id;
                $product_category_id = $output["product_category_id"];
                $output['html_content'] =  view('sale_pos.product_row2')
                    ->with(compact('product', 'row_count', 'tax_dropdown', 'enabled_modules', 'pos_settings', 'product_category_id'))
                    ->render();
            }

            $output['enable_sr_no'] = $product->enable_sr_no;

            if ($this->transactionUtil->isModuleEnabled('modifiers')  && !$is_direct_sell) {
                $this_product = Product::where('business_id', $business_id)
                    ->find($product->product_id);
                if (count($this_product->modifier_sets) > 0) {
                    $product_ms = $this_product->modifier_sets;
                    $output['html_modifier'] =  view('restaurant.product_modifier_set.modifier_for_product')
                        ->with(compact('product_ms', 'row_count'))->render();
                }
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output['success'] = false;
            $output['msg'] = __('lang_v1.item_out_of_stock');
        }

        return $output;
    }
    
    

    /**
     * Returns the HTML row for a payment in POS
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getPaymentRow(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $row_index = $request->input('row_index');
        $removable = true;
        $payment_types = $this->productUtil->payment_types();

        $payment_line = $this->dummyPaymentLine;

        //Accounts
        $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

        return view('sale_pos.partials.payment_row')
            ->with(compact('payment_types', 'row_index', 'removable', 'payment_line', 'accounts'));
    }

    /**
     * Returns recent transactions
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getRecentTransactions(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $transaction_status = $request->get('status');

        $query = Transaction::where('business_id', $business_id)
            ->where('created_by', $user_id)
            ->where('type', 'sell')
            ->where('is_direct_sale', 0);

        if ($transaction_status == 'quotation') {
            $query->where('status', 'draft')
                ->where('is_quotation', 1);
        } elseif ($transaction_status == 'draft') {
            $query->where('status', 'draft')
                ->where('is_quotation', 0);
        } else {
            $query->where('status', $transaction_status);
        }

        $transactions = $query->latest()
            ->limit(10)
            ->get();

        return view('sale_pos.partials.recent_transactions')
            ->with(compact('transactions'));
    }

    /**
     * Prints invoice for sell
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function printInvoice(Request $request, $transaction_id)
    {
        if (request()->ajax()) {
            try {
                $output = [
                    'success' => 0,
                    'msg' => trans("messages.something_went_wrong")
                ];

                $business_id = $request->session()->get('user.business_id');

                $transaction = Transaction::where('business_id', $business_id)
                    ->where('id', $transaction_id)
                    ->first();

                if (empty($transaction)) {
                    return $output;
                }

                $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction_id, 'browser');

                if (!empty($receipt)) {
                    $output = ['success' => 1, 'receipt' => $receipt];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output = [
                    'success' => 0,
                    'msg' => $e->getMessage()
                ];
            }

            return $output;
        }
    }

    /**
     * Gives suggetion for product based on category
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getProductSuggestion(Request $request)
    {
        if ($request->ajax()) {
            $category_id = $request->get('category_id');
            $brand_id = $request->get('brand_id');
            $location_id = $request->get('location_id');
            $doctor_id = $request->get('doctor_id');
            $term = $request->get('term');

            $check_qty = false;
            $business_id = $request->session()->get('user.business_id');

            $products = Product::join(
                'variations',
                'products.id',
                '=',
                'variations.product_id'
            )
                ->leftjoin(
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
                            });;
                        }
                    }
                )
                ->join(
                    'doctor_commissions',
                    'products.id',
                    '=',
                    'doctor_commissions.service_id'
                )
                ->join(
                    'doctors',
                    'doctors.id',
                    '=',
                    'doctor_commissions.doctor_id'
                )
                ->where('doctor_commissions.doctor_id', $doctor_id)
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier')
                ->where('products.category_id', 30);

            //Include search
            if (!empty($term)) {
                $products->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term . '%');
                    $query->orWhere('sku', 'like', '%' . $term . '%');
                    $query->orWhere('sub_sku', 'like', '%' . $term . '%');
                });
            }

            //Include check for quantity
            if ($check_qty) {
                $products->where('VLD.qty_available', '>', 0);
            }

            if ($category_id != 'all') {
                $products->where(function ($query) use ($category_id) {
                    $query->where('products.category_id', $category_id);
                    $query->orWhere('products.sub_category_id', $category_id);
                });
            }
            if ($brand_id != 'all') {
                $products->where('products.brand_id', $brand_id);
            }

            $products = $products->select(
                'products.id as product_id',
                'products.name',
                'products.type',
                'products.enable_stock',
                'variations.id as variation_id',
                'variations.name as variation',
                'VLD.qty_available',
                'variations.default_sell_price as selling_price',
                'variations.sub_sku',
                'products.image'
            )
                ->orderBy('products.name', 'asc')
                ->groupBy('variations.id')
                ->paginate(20);

            return view('sale_pos.partials.product_list')
                ->with(compact('products'));
        }
    }
}
