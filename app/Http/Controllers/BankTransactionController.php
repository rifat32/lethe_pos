<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Yajra\DataTables\Facades\DataTables;
use App\Contact;
use App\User;
use App\CustomerGroup;
use App\Transaction;
use App\BankingCategory;
use App\BankTransactions;
use App\BankUsers;
use App\Brands;
use App\Business;
use App\Utils\Util;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use Excel;
use DB;
use App\TransferBalancePersonal;
use App\CashRegister;
use App\CashRegisterTransaction;
use Illuminate\Routing\Controller;
use App\Restaurant\InternalKitchen;
use App\Restaurant\DishCategory;
use App\Restaurant\DishList;
use App\UsedItems;
use App\ExpenseCategory;
use App\TransferBalanceBank;
use App\ReceiveBalanceBank;
use App\ReceiveBalancePersonal;
use App\Utils\CashRegisterUtil;

class BankTransactionController extends Controller{

    protected $cashRegisterUtil;

    public function __construct(CashRegisterUtil $cashRegisterUtil)
    {
        $this->cashRegisterUtil = $cashRegisterUtil;
    }

    public function index(Request $request){
        if (!auth()->user()->can('ibtransaction.view')  && !auth()->user()->can('ibtransaction.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $cash_register_id=DB::table('cash_registers')->where('business_id',$business_id)->select('id','status')->orderby('id','DESC')->first();
        if($cash_register_id->status=="close"){
            $total_balance=0;
        }else{
            $register_details =  $this->cashRegisterUtil->getRegisterDetails($cash_register_id->id);
            $total_bank_receive=DB::table('receive_balance_bank')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_receive=DB::table('receive_balance_personal')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_bank_transfer=DB::table('transfer_balance_bank')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_transfer=DB::table('transfer_balance_personal')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_internal_transfer=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Transfered')->where('deleted_at',null)->select(DB::raw("SUM(balance) as total"))->first();
            $total_internal_receive=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Received')->where('deleted_at',null)->select(DB::raw("SUM(balance) as total"))->first();
            $total_balance_receive= $total_bank_receive->total + $total_personal_receive->total;
            $total_balance_transfer= $total_bank_transfer->total +  $total_personal_transfer->total;
            $total_balance=$register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund + $total_balance_receive - $total_balance_transfer - $total_internal_transfer->total + $total_internal_receive->total;
        }
        if (request()->ajax()) {
            $business_id = $request->session()->get('user.business_id');
            $transaction = BankTransactions::where('bank_transactions.business_id', $business_id)->leftjoin('bank_users', 'bank_transactions.u_id', '=', 'bank_users.id')->select(
                'bank_transactions.id as id',
                'bank_transactions.updated_at as updated_at',
                'bank_users.name as u_id',
                'bank_transactions.type as type',
                'bank_transactions.balance as balance',
                'bank_users.id as uid'
            );
            return Datatables::of($transaction)
            ->addColumn(
                'action',
                '@can("ibtransaction.update")
                <button data-href="{{action(\'BankTransactionController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".bank_transaction_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                &nbsp;
                @endcan
                @can("ibtransaction.update")
                <button data-info="This Transaction will be Deleted" data-href="{{action(\'BankTransactionController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_brand_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                @endcan'
            )->editColumn('u_id', function ($row) {
                $u_id = $row->u_id;
                return '<a href="'.action('BankTransactionController@transactionByUser', [$row->uid]) . '" target="_blank">'.$u_id .'</a>';
            })->removeColumn('uid')
            ->rawColumns([5])
            ->escapeColumns(null)
            ->make(false);
        }
        return view('banking.transaction',compact('total_balance'));
    }

    public function create(){
        if (!auth()->user()->can('ibtransaction.create')) {
            abort(403, 'Unauthorized action.');
        }
        $data = DB::table('bank_users')->get();
        return view('banking.transactionCreate',compact('data'));
    }

    public function store(Request $request){
        if (!auth()->user()->can('ibtransaction.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $cash_register_id=DB::table('cash_registers')->where('business_id',$business_id)->select('id','status')->orderby('id','DESC')->first();
        if($cash_register_id->status=="close"){
                $output = ['success' => false,
                'msg' => __("Please Open Cash Registry First!")
                ];
                return $output;
        }else{
            $register_details =  $this->cashRegisterUtil->getRegisterDetails($cash_register_id->id);
            $total_bank_receive=DB::table('receive_balance_bank')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_receive=DB::table('receive_balance_personal')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_bank_transfer=DB::table('transfer_balance_bank')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_transfer=DB::table('transfer_balance_personal')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_internal_transfer=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Transfered')->select(DB::raw("SUM(balance) as total"))->first();
            $total_internal_receive=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Received')->select(DB::raw("SUM(balance) as total"))->first();
            $total_balance_receive= $total_bank_receive->total + $total_personal_receive->total;
            $total_balance_transfer= $total_bank_transfer->total +  $total_personal_transfer->total;
            $total_balance=$register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund + $total_balance_receive - $total_balance_transfer - $total_internal_transfer->total + $total_internal_receive->total;
            try {
                $input = $request->only(['u_id','balance','type']);
                $input['business_id'] = $request->session()->get('user.business_id');
                $input['cash_register_id'] = $cash_register_id->id;
                $inputs = $request->get('balance');
                $input2 = $request->get('type');
                if($total_balance >= $inputs || $input2 == "Received"){
                    BankTransactions::create($input);
                    $output = ['success' => true,
                                'msg' => __("Transaction Successfully")
                            ];
                    }else{
                        $output = ['success' => false,
                                    'msg' => __("You Do not have enough Balance!")
                                ];
                    }
            }catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                $output = ['success' => false,
                                'msg' => __("messages.something_went_wrong")
                            ];
            }
            return$output;
        } 
    }

    public function edit($id){
        if (!auth()->user()->can('ibtransaction.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $bank_transaction = BankTransactions::where('business_id', $business_id)->find($id);
            $data = DB::table('bank_users')->get();
            return view('banking.editTransaction')->with(compact('bank_transaction','data'));
        }
    }

    public function update(Request $request, $id){
        if (!auth()->user()->can('ibtransaction.update')) {
            abort(403, 'Unauthorized action.');
        }
        $tbp_crid=BankTransactions::where('id',$id)->select('cash_register_id')->first();
        $cash_register_status=DB::table('cash_registers')->where('id',$tbp_crid->cash_register_id)->select('status')->first();
        if($cash_register_status->status=='close'){
            if (request()->ajax()) {
                $output = ['success' => false,
                    'msg' => __("You  can Not Update This Transaction as Register is closed For this Transaction.")
                ];
                return $output;
            }
        }else {
            $register_details =  $this->cashRegisterUtil->getRegisterDetails($tbp_crid->cash_register_id);
            $total_bank_receive=DB::table('receive_balance_bank')->where('deleted_at',null)->where('cash_register_id',$tbp_crid->cash_register_id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_receive=DB::table('receive_balance_personal')->where('deleted_at',null)->where('cash_register_id',$tbp_crid->cash_register_id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_bank_transfer=DB::table('transfer_balance_bank')->where('deleted_at',null)->where('cash_register_id',$tbp_crid->cash_register_id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_transfer=DB::table('transfer_balance_personal')->where('deleted_at',null)->where('cash_register_id',$tbp_crid->cash_register_id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_internal_transfer=DB::table('bank_transactions')->where('cash_register_id',$tbp_crid->cash_register_id)->where('type','Transfered')->where('deleted_at',null)->select(DB::raw("SUM(balance) as total"))->first();
            $total_internal_receive=DB::table('bank_transactions')->where('cash_register_id',$tbp_crid->cash_register_id)->where('type','Received')->where('deleted_at',null)->select(DB::raw("SUM(balance) as total"))->first();
            $total_balance_receive= $total_bank_receive->total + $total_personal_receive->total;
            $total_balance_transfer= $total_bank_transfer->total +  $total_personal_transfer->total;
            $total_balance=$register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund + $total_balance_receive - $total_balance_transfer - $total_internal_transfer->total + $total_internal_receive->total;
            if (request()->ajax()) {
                try {
                    $input = $request->only(['u_id','type','balance']);
                    $business_id = $request->session()->get('user.business_id');
                    $bank_user = BankTransactions::where('business_id', $business_id)->findOrFail($id);
                    $bank_user->u_id = $input['u_id'];
                    $bank_user->type = $input['type'];
                    $bank_user->balance = $input['balance'];
                    $prev_amount = $bank_user->balance;
                    if(($total_balance+$prev_amount) >= $input['balance'] || $input['type'] == "Received"){
                        $bank_user->save();
                        $output = ['success' => true,
                                'msg' => __("Internal Banking transaction updated successfully")
                                ];
                    }else{
                        $output = ['success' => false,
                        'msg' => __("You Don't Have Enough Balance!")
                    ];
                    }
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

    public function destroy(Request $request, $id){
        if (!auth()->user()->can('ibtransaction.delete')) {
            abort(403, 'Unauthorized action.');
        }
        $tbp_crid=BankTransactions::where('id',$id)->select('cash_register_id')->first();
        $cash_register_status=DB::table('cash_registers')->where('id',$tbp_crid->cash_register_id)->select('status')->first();
        if($cash_register_status->status=='close'){
            if (request()->ajax()) {
            $output = ['success' => false,
                            'msg' => __("You  can Not Delete This Transaction as Register is closed For this Transaction.")
                        ];
            return $output;
                    }
        }else{
            if (request()->ajax()) {
                try {
                    $business_id = request()->session()->get('user.business_id');
                    $brand = BankTransactions::where('business_id', $business_id)->where('id', $id)->findOrFail($id);
                    $brand->delete();
                    $output = ['success' => true,
                        'msg' => __("brand.t_deleted_success")
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

    public function transactionByUser(Request $request, $id){
        if (!auth()->user()->can('ibtransaction.view')){
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $userInfo = BankUsers::where('business_id', $business_id)->where('id', $id)->select('name','phone','account_no')->get();
        $addInfo = BankTransactions::where('business_id', $business_id)->where('u_id', $id)->where('type','Transfered')->select( DB::raw("SUM(balance) as total"))->get();
        $minusInfo = BankTransactions::where('business_id', $business_id)->where('u_id', $id)->where('type','Received')->select( DB::raw("SUM(balance) as total"))->get();
        $businessInfo = Business::where('id','=', $business_id)->select('name')->get();
        $typeInfoHelper = BankUsers::where('id','=', $id)->select('type_id')->get();
        $typeInfo = BankingCategory::where('id','=', $typeInfoHelper[0]->type_id)->select('name')->get();
        if (request()->ajax()){
            $business_id = $request->session()->get('user.business_id');
            $transaction = BankTransactions::where('bank_users.business_id', $business_id)->where('bank_users.id','=', $id)->leftjoin('bank_users', 'bank_transactions.u_id', '=', 'bank_users.id')->select(
                'bank_transactions.id as id',
                'bank_transactions.updated_at as updated_at',
                'bank_users.name as u_id',
                'bank_transactions.type as type',
                'bank_transactions.balance as balance'
            );
            return Datatables::of($transaction)
            ->addColumn(
                'action',
                '@can("ibtransaction.uodate")
                <button data-href="{{action(\'BankTransactionController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".bank_transaction_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                &nbsp;
                @endcan
                @can("ibtransaction.delete")
                <button data-info="This Transaction will be Deleted" data-href="{{action(\'BankTransactionController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_brand_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                @endcan'
            )->rawColumns([5])
            ->make(false);
        }
        return view('banking.transactionByUser',compact('userInfo','businessInfo','typeInfo','addInfo','minusInfo','id'));
    }

    public function transactionByTypeUser(Request $request, $type,$id){
        if (!auth()->user()->can('ibtransaction.view')){
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $userInfo = BankUsers::where('business_id', $business_id)->where('id', $id)->select('name','phone','account_no')->get();
        $addInfo = BankTransactions::where('business_id', $business_id)->where('u_id', $id)->where('type','Transfered')->select( DB::raw("SUM(balance) as total"))->get();
        $minusInfo = BankTransactions::where('business_id', $business_id)->where('u_id', $id)->where('type','Received')->select( DB::raw("SUM(balance) as total"))->get();
        $businessInfo = Business::where('id','=', $business_id)->select('name')->get();
        $typeInfoHelper = BankUsers::where('id','=', $id)->select('type_id')->get();
        $typeInfo = BankingCategory::where('id','=', $typeInfoHelper[0]->type_id)->select('name')->get();
        if (request()->ajax()){
            $business_id = $request->session()->get('user.business_id');
            $transaction = BankTransactions::where('bank_users.business_id', $business_id)->where('bank_users.id', $id)->where('bank_transactions.type', $type)->leftjoin('bank_users', 'bank_transactions.u_id', '=', 'bank_users.id')->select(
                'bank_transactions.id as id',
                'bank_transactions.updated_at as updated_at',
                'bank_users.name as u_id',
                'bank_transactions.type as type',
                'bank_transactions.balance as balance'
            );
            return Datatables::of($transaction)
            ->addColumn(
                'action',
                '@can("ibtransaction.update")
                <button data-href="{{action(\'BankTransactionController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".bank_transaction_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>\
                @endcan
                &nbsp;
                @can("ibtransaction.delete")
                <button data-info="This Transaction will be Deleted" data-href="{{action(\'BankTransactionController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_brand_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                @endcan'
            )->rawColumns([5])
            ->make(false);
        }
        return view('banking.transactionByUser',compact('userInfo','businessInfo','typeInfo','addInfo','minusInfo','id'));
    }
}
