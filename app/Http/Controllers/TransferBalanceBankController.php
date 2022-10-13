<?php
namespace App\Http\Controllers;
use App\ExpenseCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\TransferBalanceBank;
use App\ReceiveBalanceBank;
use App\TransferBalancePersonal;
use App\ReceiveBalancePersonal;
use DB;
use App\Utils\CashRegisterUtil;
use App\CashRegister;
use App\CashRegisterTransaction;
use App\Transaction;
class TransferBalanceBankController extends Controller{
    protected $cashRegisterUtil;

    public function __construct(CashRegisterUtil $cashRegisterUtil)
    {
        $this->cashRegisterUtil = $cashRegisterUtil;
    }

    public function index(Request $request){
        if (!auth()->user()->can('tbbank.view')  && !auth()->user()->can('tbbank.create')) {
            abort(403, 'Unauthorized action.');
        }   
        $business_id = $request->session()->get('user.business_id');
        $cash_register_id=DB::table('cash_registers')->where('business_id',$business_id)->select('id','status')->orderby('id','DESC')->first();
        if($cash_register_id->status=="close"){
            $total_balance=0;
        }else{
            $register_details =  $this->cashRegisterUtil->getRegisterDetails($cash_register_id->id);
            $total_bank_receive=DB::table('receive_balance_bank')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_receive=DB::table('receive_balance_personal')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_bank_transfer=DB::table('transfer_balance_bank')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_transfer=DB::table('transfer_balance_personal')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_internal_transfer=DB::table('bank_transactions')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->where('type','Transfered')->select(DB::raw("SUM(balance) as total"))->first();
            $total_internal_receive=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Received')->select(DB::raw("SUM(balance) as total"))->first();
            $total_balance_receive= $total_bank_receive->total + $total_personal_receive->total;
            $total_balance_transfer= $total_bank_transfer->total +  $total_personal_transfer->total;
            $total_balance=$register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund + $total_balance_receive - $total_balance_transfer - $total_internal_transfer->total + $total_internal_receive->total;
        }
        if (request()->ajax()) {
            $business_id = $request->session()->get('user.business_id');
            $expense_category = TransferBalanceBank::where('transfer_balance_bank.business_id', $business_id)->leftjoin('users', 'transfer_balance_bank.sender', '=', 'users.id')->select(['transfer_balance_bank.created_at','transfer_balance_bank.bank_name as bankName', 'transfer_balance_bank.branch','users.first_name','transfer_balance_bank.account_no','transfer_balance_bank.amount', 'transfer_balance_bank.id']);
            return Datatables::of($expense_category)
                ->editColumn('bankName', function ($row) {
                    $bankName = $row->bankName;
                    return '<a href="'.action('TransferBalanceBankController@transactionByBank', [$row->bankName,$row->branch,$row->account_no]) . '" target="_blank">'.$bankName .'</a>';
                })->addColumn(
                'action',
                '@can("brand.update")
                <button data-href="{{action(\'TransferBalanceBankController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".transfer_balance_bank_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                &nbsp;
                @endcan
                @can("brand.delete")
                <button data-href="{{action(\'TransferBalanceBankController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_transfer_balance_bank"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                @endcan'
                )->removeColumn('id')
                ->rawColumns([6])
                ->escapeColumns(null)
                ->make(false);
        }
        return view('transfer_balance.bank',compact('total_balance'));
    }

    public function create(Request $request){
        if (!auth()->user()->can('tbbank.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $cash_register_id=DB::table('cash_registers')->where('business_id',$business_id)->select('id','status')->orderby('id','DESC')->first();
        if($cash_register_id->status=="close"){
            $total_balance=0;
        }else{
            $register_details =  $this->cashRegisterUtil->getRegisterDetails($cash_register_id->id);
            $total_bank_receive=DB::table('receive_balance_bank')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_receive=DB::table('receive_balance_personal')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_bank_transfer=DB::table('transfer_balance_bank')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_transfer=DB::table('transfer_balance_personal')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_internal_transfer=DB::table('bank_transactions')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->where('type','Transfered')->select(DB::raw("SUM(balance) as total"))->first();
            $total_internal_receive=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Received')->select(DB::raw("SUM(balance) as total"))->first();
            $total_balance_receive= $total_bank_receive->total + $total_personal_receive->total;
            $total_balance_transfer= $total_bank_transfer->total +  $total_personal_transfer->total;
            $total_balance=$register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund + $total_balance_receive - $total_balance_transfer - $total_internal_transfer->total + $total_internal_receive->total;
        }
        return view('transfer_balance.createBank',compact('total_balance'));
    }

    public function store(Request $request){
        if (!auth()->user()->can('tbbank.create')) {
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
            $total_bank_receive=DB::table('receive_balance_bank')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_receive=DB::table('receive_balance_personal')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_bank_transfer=DB::table('transfer_balance_bank')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_transfer=DB::table('transfer_balance_personal')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_internal_transfer=DB::table('bank_transactions')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->where('type','Transfered')->select(DB::raw("SUM(balance) as total"))->first();
            $total_internal_receive=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Received')->select(DB::raw("SUM(balance) as total"))->first();
            $total_balance_receive= $total_bank_receive->total + $total_personal_receive->total;
            $total_balance_transfer= $total_bank_transfer->total +  $total_personal_transfer->total;
            $total_balance=$register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund + $total_balance_receive - $total_balance_transfer - $total_internal_transfer->total + $total_internal_receive->total;
            try {
                $input = $request->only(['bank_name','branch','account_no','amount','sender','receiver']);
                $input['business_id'] = $request->session()->get('user.business_id');
                $input['cash_register_id'] = $cash_register_id->id;
                $inputs = $request->get('amount');
                if($total_balance >= $inputs){
                    TransferBalanceBank::create($input);
                    $output = ['success' => true,
                        'msg' => __("Balance transfered to bank Successfully")
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
            return $output;
        }
    }

    public function destroy(Request $request, $id){
        if (!auth()->user()->can('tbbank.delete')) {
            abort(403, 'Unauthorized action.');
        }
        $tbp_crid=TransferBalanceBank::where('id',$id)->select('cash_register_id')->first();
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
                    $expense_category = TransferBalanceBank::where('business_id', $business_id)->findOrFail($id);
                    $expense_category->delete();
                    $output = ['success' => true,
                                'msg' => __("Deleted Successfully")
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

    public function transactionByBank(Request $request, $id,$id2,$id3){
        if (!auth()->user()->can('tbbank.view')) {
            abort(403, 'Unauthorized action.');
        }
        $receive_bank_balance = ReceiveBalanceBank::select(DB::raw("SUM(amount) as total_receive_balance"))->where('bank_name', $id)->where('branch', $id2)->where('account_no', $id3)->get();
        $transfer_bank_balance = TransferBalanceBank::select(DB::raw("SUM(amount) as total_transfer_balance"))->where('bank_name', $id)->where('branch', $id2)->where('account_no', $id3)->get();
        $total_bank_balance=( $transfer_bank_balance[0]->total_transfer_balance- $receive_bank_balance[0]->total_receive_balance);
        $business_id = $request->session()->get('user.business_id');
        $transferBank = TransferBalanceBank::where('transfer_balance_bank.bank_name',$id)->where('branch', $id2)->where('account_no', $id3)->where('transfer_balance_bank.business_id', $business_id)->leftjoin('users', 'transfer_balance_bank.sender', '=', 'users.id')->select(['transfer_balance_bank.created_at as at','transfer_balance_bank.bank_name as bank', 'transfer_balance_bank.branch as branch','users.first_name as sender','transfer_balance_bank.account_no as account_no','transfer_balance_bank.amount as amount', 'transfer_balance_bank.id as id'])->get();
        $receiverBank= ReceiveBalanceBank::where('receive_balance_bank.bank_name',$id)->where('branch', $id2)->where('account_no', $id3)->where('receive_balance_bank.business_id', $business_id)->leftjoin('users', 'receive_balance_bank.receiver', '=', 'users.id')->select(['receive_balance_bank.created_at as at','receive_balance_bank.bank_name as bank', 'receive_balance_bank.branch as branch','users.first_name as receiver','receive_balance_bank.account_no as account_no','receive_balance_bank.amount as amount', 'receive_balance_bank.id as id'])->get();
        return view('transfer_balance.transactionByBank',compact('transferBank','receiverBank','total_bank_balance','id','receive_bank_balance','transfer_bank_balance'));
    }
   
    public function edit($id){
        if (!auth()->user()->can('tbbank.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $tb_bank = TransferBalanceBank::where('business_id', $business_id)->find($id);
            return view('transfer_balance.editBank')
                ->with(compact('tb_bank'));
        }
    }

    public function update(Request $request, $id){
        if (!auth()->user()->can('tbbank.update')) {
            abort(403, 'Unauthorized action.');
        }
        $tbp_crid=TransferBalanceBank::where('id',$id)->select('cash_register_id')->first();
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
                $total_internal_transfer=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$tbp_crid->cash_register_id)->where('type','Transfered')->select(DB::raw("SUM(balance) as total"))->first();
                $total_internal_receive=DB::table('bank_transactions')->where('deleted_at',null)->where('cash_register_id',$tbp_crid->cash_register_id)->where('type','Received')->select(DB::raw("SUM(balance) as total"))->first();
                $total_balance_receive= $total_bank_receive->total + $total_personal_receive->total;
                $total_balance_transfer= $total_bank_transfer->total +  $total_personal_transfer->total;
                $total_balance=$register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund + $total_balance_receive - $total_balance_transfer - $total_internal_transfer->total + $total_internal_receive->total;
                if (request()->ajax()) {
                    try {
                        $input = $request->only(['bank_name', 'branch','account_no','amount']);
                        $business_id = $request->session()->get('user.business_id');
                        $raw_items = TransferBalanceBank::where('business_id', $business_id)->findOrFail($id);
                        $prev_amount =  $raw_items->amount;
                        if(($total_balance+$prev_amount) >=$input['amount']){
                            $raw_items->bank_name = $input['bank_name'];
                            $raw_items->branch = $input['branch'];
                            $raw_items->account_no = $input['account_no'];
                            $raw_items->amount = $input['amount'];
                            $raw_items->save();
                            $output = ['success' => true,
                                    'msg' => __("Balance Transfered Successfully")
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
}