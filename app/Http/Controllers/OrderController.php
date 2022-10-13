<?php

namespace App\Http\Controllers;

use App\Order2;
use App\User2;
use App\VariationLocationDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use PDF;
use App\Utils\ModuleUtil;
use App\TaxRate;
use App\Transaction;
use App\BusinessLocation;
use App\TransactionSellLine;
use App\User;
use App\CustomerGroup;
use App\SellingPriceGroup;
use Yajra\DataTables\Facades\DataTables;


use App\Utils\ContactUtil;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;


class OrderController extends Controller
{
    protected $contactUtil;
    protected $businessUtil;
    protected $transactionUtil;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(ContactUtil $contactUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
        'is_return' => 0, 'transaction_no' => ''];
    }
    public function index(Request $request)
    {
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


        //  return response()->json($orders);

// return $orders;
        return view('EcomOrders.index', compact('orders', 'sort_search', 'delivery_status', 'date'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    public function createEcommerceSell($orderId)
    {
        if (!auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellController@index'));
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        
        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_location = null;
        if (count($business_locations) == 1) {
            foreach ($business_locations as $id => $name) {
                $default_location = $id;
            }
        }

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id);
        }

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

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->transactionUtil->payment_types();

        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $default_datetime = $this->businessUtil->format_date('now', true);
        $order = Order2::findOrFail($orderId);
        return view('sell.create3')
            ->with(compact(
                'business_details',
                'taxes',
                'walk_in_customer',
                'business_locations',
                'bl_attributes',
                'default_location',
                'commission_agent',
                'types',
                'customer_groups',
                'payment_line',
                'payment_types',
                'price_groups',
                'selling_price_groups',
                'default_datetime',
                "order"
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $order = Order2::findOrFail(decrypt($id));
        $order_shipping_address = json_decode($order->shipping_address);
        $deliveryMans = DB::table("delivery_man")->get();
        $order->discount = 0;
        $order->netTotal =   $order->grand_total;


        if(!empty($order->discount_type)) {

            if(empty($order->discount_amount)) {
                $order->discount_amount = 0;
            }
            if($order->discount_type == "fixed") {
                $order->discount = $order->discount_amount;
            } else {
                $order->discount = $order->grand_total  * ($order->discount_amount/100);
            }

        }

        return view('EcomOrders.show', compact('order','deliveryMans'));
    }

    public function update_payment_status(Request $request)
    {
        $order = Order2::findOrFail($request->order_id);
        $order->payment_status_viewed = '0';
        $order->save();

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        }

        $status = 'paid';
        foreach ($order->orderDetails as $key => $orderDetail) {
            if ($orderDetail->payment_status != 'paid') {
                $status = 'unpaid';
            }
        }
        $order->payment_status = $status;
        $order->save();


        if ($order->payment_status == 'paid' && $order->commission_calculated == 0) {


            $order->commission_calculated = 1;
            $order->save();
        }



        return 1;
    }
    public function update_shipping(Request $request)
    {
        $order = Order2::with("orderDetails")->findOrFail($request->order_id);
        $order->payment_status_viewed = '0';
        $order->area_shipping = $request->area_shipping;
        if($request->area_shipping == "0") {
            $order->shipping = $request->shipping;
        } else {
            $order->shipping = "0";
        }
 $order->delivery_man = $request->delivery_man;

 $order->discount_type = $request->discount_type;
 $order->discount_amount = $request->discount_amount;
        $order->save();

        $order->discount = 0;
        $order->netTotal =   $order->grand_total;


        if(!empty($order->discount_type)) {

            if(empty($order->discount_amount)) {
                $order->discount_amount = 0;
            }
            if($order->discount_type == "fixed") {
                $order->discount = $order->discount_amount;
            } else {
                $order->discount = $order->orderDetails->sum('price')  * ($order->discount_amount/100);
            }

        }


       return response()->json($order);


        return 1;
    }
    public function update_area_shipping(Request $request)
    {
        $order = Order2::findOrFail($request->order_id);
        $order->payment_status_viewed = '0';

        $order->area_shipping = $request->status;
        $order->delivery_man = $request->delivery_man;

        $order->save();

       return response()->json($order);


        return 1;
    }

    public function update_delivery_status(Request $request)
    {

        $order = Order2::findOrFail($request->order_id);
        if($order->delivery_status == "cancelled" || $order->delivery_status == "return") {
            return xdsd;
                    }
        $order->delivery_viewed = '0';
        $order->delivery_status = $request->status;
        $order->save();

        if ($request->status == 'cancelled' && $order->payment_type == 'wallet') {
            $user = User2::where('id', $order->user_id)->first();
            $user->balance += $order->grand_total;
            $user->save();
        }

        // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

        if (Auth::user()->user_type == 'seller') {
            foreach ($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail) {
                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();

                if ($request->status == 'cancelled') {
                    // $variant = $orderDetail->variation;
                    // if ($orderDetail->variation == null) {
                    //     $variant = '';
                    // }

                    // $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                    //     ->where('variant', $variant)
                    //     ->first();

                    // if ($product_stock != null) {
                    //     $product_stock->qty += $orderDetail->quantity;
                    //     $product_stock->save();
                    // }
                    // $qty_difference = $orderDetail->quantity - 0;

                    //     VariationLocationDetails::where('variation_id', $orderDetail->variation_id)
                    //         ->where('location_id', 9)
                    //         ->increment('qty_available', $qty_difference);

                }
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {

                $orderDetail->delivery_status = $request->status;
                $orderDetail->save();

                if ($request->status == 'cancelled') {


                    // $variant = $orderDetail->variation;
                    // if ($orderDetail->variation == null) {
                    //     $variant = '';
                    // }

                    // $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                    //     ->where('variant', $variant)
                    //     ->first();

                    // if ($product_stock != null) {
                    //     $product_stock->qty += $orderDetail->quantity;
                    //     $product_stock->save();
                    // }
                    $qty_difference = $orderDetail->quantity - 0;

                    VariationLocationDetails::where('variation_id', $orderDetail->variation_id)
                        // ->where('location_id', 9)
                        ->increment('qty_available', $qty_difference);
                }


            }
        }


        //sends Notifications to user
        // send_notification($order, $request->status);
        if (get_setting('google_firebase') == 1 && $order->user->device_token != null) {
            $request->device_token = $order->user->device_token;
            $request->title = "Order updated !";
            $status = str_replace("_", "", $order->delivery_status);
            $request->text = " Your order {$order->code} has been {$status}";

            $request->type = "order";
            $request->id = $order->id;
            $request->user_id = $order->user->id;

            // send_firebase_notification($request);
        }


        // if (\App\Addon::where('unique_identifier', 'delivery_boy')->first() != null &&
        //     \App\Addon::where('unique_identifier', 'delivery_boy')->first()->activated) {

        //     if (Auth::user()->user_type == 'delivery_boy') {
        //         $deliveryBoyController = new DeliveryBoyController;
        //         $deliveryBoyController->store_delivery_history($order);
        //     }
        // }

        return 1;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $order = Order2::findOrFail($id);
        if ($order != null) {
            foreach ($order->orderDetails as $key => $orderDetail) {


                    // $variant = $orderDetail->variation;
                    // if ($orderDetail->variation == null) {
                    //     $variant = '';
                    // }

                    // $product_stock = ProductStock::where('product_id', $orderDetail->product_id)
                    //     ->where('variant', $variant)
                    //     ->first();

                    // if ($product_stock != null) {
                    //     $product_stock->qty += $orderDetail->quantity;
                    //     $product_stock->save();
                    // }
                    $qty_difference = $orderDetail->quantity - 0;

                    VariationLocationDetails::where('variation_id', $orderDetail->variation_id)
                        ->where('location_id', 9)
                        ->increment('qty_available', $qty_difference);



            }
            $order->delete();
           return back()->with(["success"=>'Order has been deleted successfully']);
        } else {
            return back()->with('Something went wrong');

        }
        return back();
    }
    public function invoice_download($id) {

            // if(Session::has('currency_code')){
            //     $currency_code =  session('currency');
            // }
            // else{
            //     $currency_code = \App\Currency::findOrFail(get_setting('system_default_currency'))->code;
            // }
            $currency_code =  session('currency')["code"];

            $language_code = 'en';
            $direction = 'ltr';
            $text_align = 'left';
            $not_text_align = 'right';
            // if(\App\Language::where('code', $language_code)->first()->rtl == 1){
            //     $direction = 'rtl';
            //     $text_align = 'right';
            //     $not_text_align = 'left';
            // }else{
            //     $direction = 'ltr';
            //     $text_align = 'left';
            //     $not_text_align = 'right';
            // }

            if($currency_code == 'BDT' || $language_code == 'bd'){
                // bengali font
                $font_family = "'Hind Siliguri','sans-serif'";
            }elseif($currency_code == 'KHR' || $language_code == 'kh'){
                // khmer font
                $font_family = "'Hanuman','sans-serif'";
            }elseif($currency_code == 'AMD'){
                // Armenia font
                $font_family = "'arnamu','sans-serif'";
            }elseif($currency_code == 'ILS'){
                // Israeli font
                $font_family = "'Varela Round','sans-serif'";
            }elseif($currency_code == 'AED' || $currency_code == 'EGP' || $language_code == 'sa' || $currency_code == 'IQD'){
                // middle east/arabic font
                $font_family = "'XBRiyaz','sans-serif'";
            }else{
                // general for all
                $font_family = "'Roboto','sans-serif'";
            }

            $order = Order2::findOrFail($id);
            $order->discount = 0;
            $order->netTotal =   $order->grand_total;


            if(!empty($order->discount_type)) {

                if(empty($order->discount_amount)) {
                    $order->discount_amount = 0;
                }
                if($order->discount_type == "fixed") {
                    $order->discount = $order->discount_amount;
                } else {
                    $order->discount = $order->grand_total  * ($order->discount_amount/100);
                }

            }
            return PDF::loadView('EcomOrders.invoice',[
                'order' => $order,
                'font_family' => $font_family,
                'direction' => $direction,
                'text_align' => $text_align,
                'not_text_align' => $not_text_align
            ], [], [])->download('order-'.$order->code.'.pdf');

    }


    public function invoice_print($id) {
  // if(Session::has('currency_code')){
            //     $currency_code =  session('currency');
            // }
            // else{
            //     $currency_code = \App\Currency::findOrFail(get_setting('system_default_currency'))->code;
            // }
            $currency_code =  session('currency')["code"];

            $language_code = 'en';
            $direction = 'ltr';
            $text_align = 'left';
            $not_text_align = 'right';
            // if(\App\Language::where('code', $language_code)->first()->rtl == 1){
            //     $direction = 'rtl';
            //     $text_align = 'right';
            //     $not_text_align = 'left';
            // }else{
            //     $direction = 'ltr';
            //     $text_align = 'left';
            //     $not_text_align = 'right';
            // }

            if($currency_code == 'BDT' || $language_code == 'bd'){
                // bengali font
                $font_family = "'Hind Siliguri','sans-serif'";
            }elseif($currency_code == 'KHR' || $language_code == 'kh'){
                // khmer font
                $font_family = "'Hanuman','sans-serif'";
            }elseif($currency_code == 'AMD'){
                // Armenia font
                $font_family = "'arnamu','sans-serif'";
            }elseif($currency_code == 'ILS'){
                // Israeli font
                $font_family = "'Varela Round','sans-serif'";
            }elseif($currency_code == 'AED' || $currency_code == 'EGP' || $language_code == 'sa' || $currency_code == 'IQD'){
                // middle east/arabic font
                $font_family = "'XBRiyaz','sans-serif'";
            }else{
                // general for all
                $font_family = "'Roboto','sans-serif'";
            }

            $order = Order2::findOrFail($id);




        return response()->json(["html" => View::make('EcomOrders.invoice', [
            'order' => $order,
            'font_family' => $font_family,
            'direction' => $direction,
            'text_align' => $text_align,
            'not_text_align' => $not_text_align
        ])
        ->render()
]) ;
    }



}
