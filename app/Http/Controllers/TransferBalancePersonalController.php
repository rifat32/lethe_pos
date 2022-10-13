<?php
namespace App\Http\Controllers;
use App\ExpenseCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\TransferBalanceBank;
use App\ReceiveBalanceBank;
use App\TransferBalancePersonal;
use App\ReceiveBalancePersonal;
use App\Utils\CashRegisterUtil;
use DB;
use App\CashRegister;
use App\CashRegisterTransaction;
use App\Transaction;
class TransferBalancePersonalController extends Controller{

    protected $cashRegisterUtil;

    public function __construct(CashRegisterUtil $cashRegisterUtil)
    {
        $this->cashRegisterUtil = $cashRegisterUtil;
    }

    public function index(Request $request){
        if (!auth()->user()->can('tbpersonal.view') && !auth()->user()->can('tbpersonal.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $cash_register_id=DB::table('cash_registers')->where('business_id',$business_id)->select('id','status')->orderby('id','DESC')->first();
        if($cash_register_id->status=="close"){
            $total_balance=0;
        }else{
            $register_details =  $this->cashRegisterUtil->getRegisterDetails($cash_register_id->id);
            $total_bank_receive=DB::table('receive_balance_bank')->where('business_id',$business_id)->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_receive=DB::table('receive_balance_personal')->where('business_id',$business_id)->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_bank_transfer=DB::table('transfer_balance_bank')->where('business_id',$business_id)->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_transfer=DB::table('transfer_balance_personal')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_internal_transfer=DB::table('bank_transactions')->where('business_id',$business_id)->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->where('type','Transfered')->select(DB::raw("SUM(balance) as total"))->first();
            $total_internal_receive=DB::table('bank_transactions')->where('business_id',$business_id)->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Received')->select(DB::raw("SUM(balance) as total"))->first();
            $total_balance_receive= $total_bank_receive->total + $total_personal_receive->total;
            $total_balance_transfer= $total_bank_transfer->total +  $total_personal_transfer->total;
            $total_balance=$register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund + $total_balance_receive - $total_balance_transfer - $total_internal_transfer->total + $total_internal_receive->total;
        }
        if (request()->ajax()) {
            $business_id = $request->session()->get('user.business_id');
            $expense_category = TransferBalancePersonal::where('transfer_balance_personal.business_id', $business_id)->leftjoin('users', 'transfer_balance_personal.sender', '=', 'users.id')->select(['transfer_balance_personal.created_at','transfer_balance_personal.receiver as receiver','transfer_balance_personal.phone','transfer_balance_personal.reason','users.first_name','transfer_balance_personal.amount','transfer_balance_personal.id as id']);
            return Datatables::of($expense_category)
            ->editColumn('receiver', function ($row) {
                $receiver = $row->receiver;
                return '<a href="'.action('ReceiveBalancePersonalController@transactionByUser', [$row->receiver]) . '" target="_blank">'.$receiver .'</a>';
            })->addColumn(
                'action',
                    '@can("brand.update")
                        <button data-href="{{action(\'TransferBalancePersonalController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".transfer_balance_personal_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                    &nbsp;
                    @endcan
                    @can("brand.delete")
                        <button data-href="{{action(\'TransferBalancePersonalController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_transfer_balance_personal"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )->removeColumn('id')
                ->rawColumns([6])
                ->escapeColumns(null)
                ->make(false);
        }
        return view('transfer_balance.personal',compact('total_balance'));
    }

    public function create(Request $request){
        if (!auth()->user()->can('tbpersonal.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        $business_id = $request->session()->get('user.business_id');
        $cash_register_id=DB::table('cash_registers')->where('business_id',$business_id)->select('id','status')->orderby('id','DESC')->first();
        if($cash_register_id->status=="close"){
            $total_balance=0;
        }else{
            $register_details =  $this->cashRegisterUtil->getRegisterDetails($cash_register_id->id);
            $total_bank_receive=DB::table('receive_balance_bank')->where('business_id',$business_id)->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_receive=DB::table('receive_balance_personal')->where('business_id',$business_id)->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_bank_transfer=DB::table('transfer_balance_bank')->where('business_id',$business_id)->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_transfer=DB::table('transfer_balance_personal')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_internal_transfer=DB::table('bank_transactions')->where('business_id',$business_id)->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->where('type','Transfered')->select(DB::raw("SUM(balance) as total"))->first();
            $total_internal_receive=DB::table('bank_transactions')->where('business_id',$business_id)->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Received')->select(DB::raw("SUM(balance) as total"))->first();
            $total_balance_receive= $total_bank_receive->total + $total_personal_receive->total;
            $total_balance_transfer= $total_bank_transfer->total +  $total_personal_transfer->total;
            $total_balance=$register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund + $total_balance_receive - $total_balance_transfer - $total_internal_transfer->total + $total_internal_receive->total;
       }
        return view('transfer_balance.createPersonal',compact('total_balance'));
    }

    public function store(Request $request){
        if (!auth()->user()->can('tbpersonal.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $cash_register_id=DB::table('cash_registers')->where('business_id',$business_id)->select('id','status')->orderby('id','DESC')->first();
        if($cash_register_id->status=="close"){
            $output = ['success' => false,
                'msg' => __("Please Open Your Cash register First")
            ];
            return $output;
       }else{
        $register_details =  $this->cashRegisterUtil->getRegisterDetails($cash_register_id->id);
        $register_details =  $this->cashRegisterUtil->getRegisterDetails($cash_register_id->id);
        $total_bank_receive=DB::table('receive_balance_bank')->where('business_id',$business_id)->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
        $total_personal_receive=DB::table('receive_balance_personal')->where('business_id',$business_id)->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
        $total_bank_transfer=DB::table('transfer_balance_bank')->where('business_id',$business_id)->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
        $total_personal_transfer=DB::table('transfer_balance_personal')->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
        $total_internal_transfer=DB::table('bank_transactions')->where('business_id',$business_id)->where('cash_register_id',$cash_register_id->id)->where('deleted_at',null)->where('type','Transfered')->select(DB::raw("SUM(balance) as total"))->first();
        $total_internal_receive=DB::table('bank_transactions')->where('business_id',$business_id)->where('deleted_at',null)->where('cash_register_id',$cash_register_id->id)->where('type','Received')->select(DB::raw("SUM(balance) as total"))->first();
        $total_balance_receive= $total_bank_receive->total + $total_personal_receive->total;
        $total_balance_transfer= $total_bank_transfer->total +  $total_personal_transfer->total;
        $total_balance=$register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund + $total_balance_receive - $total_balance_transfer - $total_internal_transfer->total + $total_internal_receive->total;
        try {
            $input = $request->only(['receiver','phone','amount','reason','sender']);
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['cash_register_id'] = $cash_register_id->id;
            $inputs = $request->get('amount');
            if($total_balance >= $inputs){
                TransferBalancePersonal::create($input);
                $output = ['success' => true,
                    'msg' => __("Balance transfered Successfully")
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
        if (!auth()->user()->can('tbpersonal.delete')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $tbp_crid=TransferBalancePersonal::where('id',$id)->select('cash_register_id')->first();
        $cash_register_status=DB::table('cash_registers')->where('business_id',$business_id)->where('id',$tbp_crid->cash_register_id)->select('status')->first();
        if($cash_register_status->status=='close'){
            if (request()->ajax()) {
                $output = ['success' => false,
                    'msg' => __("You can Not Delete This Transaction as Register is closed For this Transaction.")
                ];
                return $output;
            }
        }else{
            if (request()->ajax()) {
                try {
                    $business_id = request()->session()->get('user.business_id');
                    $expense_category = TransferBalancePersonal::where('business_id', $business_id)->findOrFail($id);
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

    public function edit($id){
        if (!auth()->user()->can('tbpersonal.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $tb_personal = TransferBalancePersonal::where('business_id', $business_id)->find($id);
            return view('transfer_balance.editPersonal')->with(compact('tb_personal'));
        }
    }

    public function update(Request $request, $id){
        if (!auth()->user()->can('tbpersonal.update')) {
            abort(403, 'Unauthorized action.');
        }
        $tbp_crid=TransferBalancePersonal::where('id',$id)->where('business_id',$business_id)->select('cash_register_id')->first();
        $cash_register_status=DB::table('cash_registers')->where('business_id',$business_id)->where('id',$tbp_crid->cash_register_id)->select('status')->first();
        if($cash_register_status->status=='close'){
            $output = ['success' => false,
                'msg' => __("You can Not Update This Transaction as Register is closed For this Transaction.")
            ];
            return $output;
        }else{
            $register_details =  $this->cashRegisterUtil->getRegisterDetails($tbp_crid->cash_register_id);
            $total_bank_receive=DB::table('receive_balance_bank')->where('business_id',$business_id)->where('deleted_at',null)->where('cash_register_id',$tbp_crid->cash_register_id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_personal_receive=DB::table('receive_balance_personal')->where('business_id',$business_id)->where('deleted_at',null)->where('cash_register_id',$tbp_crid->cash_register_id)->select(DB::raw("SUM(amount) as total"))->first();
            $total_balance=$register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund + $total_bank_receive->total + $total_personal_receive->total;
            try {
                $input = $request->only(['receiver', 'phone','amount','reason']);
                $business_id = $request->session()->get('user.business_id');
                $raw_items = TransferBalancePersonal::where('business_id', $business_id)->findOrFail($id);
                $prev_amount = $raw_items->amount;
                if(($total_balance+4 )>= $input['amount']){
                    $prev_amount = $raw_items->amount;
                    $raw_items->receiver = $input['receiver'];
                    $raw_items->phone = $input['phone'];
                    $raw_items->amount = $input['amount'];
                    $raw_items->reason = $input['reason'];
                    $raw_items->save();
                    $output = ['success' => true,
                        'msg' => __("Balance transfer update Successfull")
                    ];
                }else{
                    $output = ['success' => false,
                        'msg' => __("You Do not have enough Balance!")
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