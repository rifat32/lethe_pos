<?php

namespace App\Http\Controllers;

use App\CashRegister;
use Illuminate\Http\Request;

use App\Utils\CashRegisterUtil;

use DB;
use App\TransferBalanceBank;
use App\ReceiveBalanceBank;
use App\TransferBalancePersonal;
use App\ReceiveBalancePersonal;
class CashRegisterController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $cashRegisterUtil;

    /**
     * Constructor
     *
     * @param CashRegisterUtil $cashRegisterUtil
     * @return void
     */
    public function __construct(CashRegisterUtil $cashRegisterUtil)
    {
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->payment_types = ['cash' => 'Cash', 'card' => 'Card', 'cheque' => 'Cheque', 'bank_transfer' => 'Bank Transfer', 'other' => 'Other'];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cash_register.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Check if there is a open register, if yes then redirect to POS screen.
        if ($this->cashRegisterUtil->countOpenedRegister() != 0) {
            return redirect()->action('SellPosController@create');
        }
        $last_balance =DB::table('cash_registers')->orderBy('id', 'desc')->first();
        return view('cash_register.create',compact('last_balance'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $initial_amount = 0;
            if (!empty($request->input('amount'))) {
                $initial_amount = $this->cashRegisterUtil->num_uf($request->input('amount'));
            }
            $user_id = $request->session()->get('user.id');
            $business_id = $request->session()->get('user.business_id');

            $register = CashRegister::create([
                        'business_id' => $business_id,
                        'user_id' => $user_id,
                        'status' => 'open'
                    ]);
            $register->cash_register_transactions()->create([
                            'amount' => $initial_amount,
                            'pay_method' => 'cash',
                            'type' => 'credit',
                            'transaction_type' => 'initial'
                        ]);
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        }

        return redirect()->action('SellPosController@create');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CashRegister  $cashRegister
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $register_details =  $this->cashRegisterUtil->getRegisterDetails($id);
        $user_id = $register_details->user_id;
        $open_time = $register_details['open_time'];
        $close_time = \Carbon::now()->toDateTimeString();
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time);

        return view('cash_register.register_details')
                    ->with(compact('register_details', 'details'));
    }

    /**j
     * Shows register details modal.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getRegisterDetails(Request $request){
        $business_id = $request->session()->get('user.business_id');
        $cash_register_id=DB::table('cash_registers')->where('business_id',$business_id)->select('id','status')->orderby('id','DESC')->first();
        $register_details =  $this->cashRegisterUtil->getRegisterDetails();
        $user_id = auth()->user()->id;
        $open_time = $register_details['open_time'];
        $close_time = \Carbon::now()->toDateTimeString();
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time);
        $sell_return = $this->cashRegisterUtil->getRegisterTransactionSellReturn($user_id, $open_time, $close_time);
        // $total_bank_receive=DB::table('receive_balance_bank')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
        // $total_personal_receive=DB::table('receive_balance_personal')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
        // $total_bank_transfer=DB::table('transfer_balance_bank')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
        // $total_personal_transfer=DB::table('transfer_balance_personal')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
        // $total_internal_transfer=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Transfered')->select(DB::raw("SUM(balance) as total"))->first();
        // $total_internal_receive=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Received')->select(DB::raw("SUM(balance) as total"))->first();
        $total_bank_receive=DB::table('receive_balance_bank')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
        $total_personal_receive=DB::table('receive_balance_personal')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
        $total_bank_transfer=DB::table('transfer_balance_bank')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
        $total_personal_transfer=DB::table('transfer_balance_personal')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
        $total_internal_transfer=DB::table('bank_transactions')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->where('type','Transfered')->select(DB::raw("SUM(balance) as total"))->first();
        $total_internal_receive=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Received')->select(DB::raw("SUM(balance) as total"))->first();
        // //
        // $total_balance_receive= $total_bank_receive->total + $total_personal_receive->total + $total_internal_receive;
        // $total_balance_transfer= $total_bank_transfer->total +  $total_personal_transfer->total + $total_internal_transfer;
        return view('cash_register.register_details')
                ->with(compact('sell_return','register_details', 'details','total_bank_receive','total_personal_receive','total_bank_transfer','total_personal_transfer','total_internal_transfer','total_internal_receive'));
    }

    /**
     * Shows close register form.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getCloseRegister(Request $request){

        $register_details =  $this->cashRegisterUtil->getRegisterDetails();
        $business_id = $request->session()->get('user.business_id');
        $cash_register_id=DB::table('cash_registers')->where('business_id',$business_id)->select('id','status')->orderby('id','DESC')->first();
        $user_id = auth()->user()->id;
        $open_time = $register_details['open_time'];
        $close_time = \Carbon::now()->toDateTimeString();
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time);
        $sell_return = $this->cashRegisterUtil->getRegisterTransactionSellReturn($user_id, $open_time, $close_time);
        $total_bank_receive=DB::table('receive_balance_bank')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
        $total_personal_receive=DB::table('receive_balance_personal')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
        $total_bank_transfer=DB::table('transfer_balance_bank')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
        $total_personal_transfer=DB::table('transfer_balance_personal')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
        $total_internal_transfer=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Transfered')->select(DB::raw("SUM(balance) as total"))->first();
        $total_internal_receive=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Received')->select(DB::raw("SUM(balance) as total"))->first();
        $total_balance_receive= $total_bank_receive->total + $total_personal_receive->total;
        $total_balance_transfer= $total_bank_transfer->total +  $total_personal_transfer->total;
        return view('cash_register.close_register_modal')
                ->with(compact('sell_return','register_details', 'details','total_bank_receive','total_personal_receive','total_bank_transfer','total_personal_transfer','total_balance_receive','total_balance_transfer','total_internal_transfer','total_internal_receive'));
    }

    /**
     * Closes currently opened register.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postCloseRegister(Request $request)
    {
        try {
            //Disable in demo
            if (config('app.env') == 'demo') {
                $output = ['success' => 0,
                                'msg' => 'Feature disabled in demo!!'
                            ];
                return redirect()->action('HomeController@index')->with('status', $output);
            }
            
            $input = $request->only(['closing_amount', 'total_card_slips', 'total_cheques',
                                    'closing_note']);
            $input['closing_amount'] = $this->cashRegisterUtil->num_uf($input['closing_amount']);
            $user_id = $request->session()->get('user.id');
            $input['closed_at'] = \Carbon::now()->format('Y-m-d H:i:s');
            $input['status'] = 'close';

            CashRegister::where('user_id', $user_id)
                                ->where('status', 'open')
                                ->update($input);
            $output = ['success' => 1,
                            'msg' => __('cash_register.close_success')
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return redirect()->action('HomeController@index')->with('status', $output);
    }
}
