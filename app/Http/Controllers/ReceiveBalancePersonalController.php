<?php
namespace App\Http\Controllers;
use App\ExpenseCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\ReceiveBalancePersonal;
use App\TransferBalancePersonal;
use App\User;
use DB;
use App\CashRegister;
use App\Utils\CashRegisterUtil;
class ReceiveBalancePersonalController extends Controller{

    protected $cashRegisterUtil;

    public function __construct(CashRegisterUtil $cashRegisterUtil){
        $this->cashRegisterUtil = $cashRegisterUtil;
    }

    public function index(Request $request){
        if (!auth()->user()->can('rbpersonal.view') && !auth()->user()->can('rbpersonal.create')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $expense_category = ReceiveBalancePersonal::where('receive_balance_personal.business_id', $business_id)->leftjoin('users', 'receive_balance_personal.sender', '=', 'users.id')->select(['receive_balance_personal.created_at','receive_balance_personal.receiver as receiver','receive_balance_personal.phone','receive_balance_personal.reason','users.first_name','receive_balance_personal.amount','receive_balance_personal.id']);
            return Datatables::of($expense_category)
            ->editColumn('receiver', function ($row) {
                $receiver = $row->receiver;
                return '<a href="'.action('ReceiveBalancePersonalController@transactionByUser', [$row->receiver]) . '" target="_blank">'.$receiver .'</a>';
            })->addColumn(
                'action',
                '@can("rbpersonal.update")
                <button data-href="{{action(\'ReceiveBalancePersonalController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".receive_balance_personal_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                &nbsp;
                @endcan
                @can("rbpersonal.delete")
                <button data-href="{{action(\'ReceiveBalancePersonalController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_transfer_balance_personal"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                @endcan')
                ->removeColumn('id')
                ->rawColumns([7])
                ->escapeColumns(null)
                ->make(false);
        }
        return view('receive_balance.personal');
    }

    public function create(){
        if (!auth()->user()->can('rbpersonal.create')) {
            abort(403, 'Unauthorized action.');
        }
        return view('receive_balance.createPersonal');
    }
    
    public function store(Request $request){
        if (!auth()->user()->can('rbpersonal.create')) {
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
            try {
                $input = $request->only(['receiver','phone','amount','reason','sender']);
                $input['business_id'] = $request->session()->get('user.business_id');
                $input['cash_register_id'] = $cash_register_id->id;
                    ReceiveBalancePersonal::create($input);
                    $output = ['success' => true,
                        'msg' => __("Balance received  Successfully")
                    ];
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
        if (!auth()->user()->can('rbpersonal.view')) {
            abort(403, 'Unauthorized action.');
        }
        $tbp_crid=ReceiveBalancePersonal::where('id',$id)->select('cash_register_id')->first();
        $cash_register_status=DB::table('cash_registers')->where('id',$tbp_crid->cash_register_id)->select('status')->first();
        if($cash_register_status->status=='close'){
            if (request()->ajax()) {
                $output = ['success' => false,
                    'msg' => __("You Can Not Delete This Transaction as Register is closed For this Transaction.")
                ];
                return $output;
            }
        }else{
            if (request()->ajax()) {
                try {
                    $business_id = request()->session()->get('user.business_id');
                    $expense_category = ReceiveBalancePersonal::where('business_id', $business_id)->findOrFail($id);
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
    public function transactionByUser(Request $request, $id){
        if (!auth()->user()->can('rbpersonal.view')) {
            abort(403, 'Unauthorized action.');
        }
        $receive_bank_balance = ReceiveBalancePersonal::select(DB::raw("SUM(amount) as total_receive_balance"))->where('receiver', $id)->get();
        $transfer_bank_balance = TransferBalancePersonal::select(DB::raw("SUM(amount) as total_transfer_balance"))->where('receiver', $id)->get();
        $total_bank_balance=($receive_bank_balance[0]->total_receive_balance + $transfer_bank_balance[0]->total_transfer_balance);
        $business_id = $request->session()->get('user.business_id');
        $receive_personal = ReceiveBalancePersonal::where('receive_balance_personal.receiver',$id)->where('receive_balance_personal.business_id', $business_id)->leftjoin('users', 'receive_balance_personal.sender', '=', 'users.id')->select(['receive_balance_personal.created_at as at','receive_balance_personal.receiver as receiver', 'receive_balance_personal.phone as phone','users.first_name as sender','receive_balance_personal.reason as reason','receive_balance_personal.amount as amount', 'receive_balance_personal.id as id'])->get();
        $transfer_personal = TransferBalancePersonal::where('transfer_balance_personal.receiver',$id)->where('transfer_balance_personal.business_id', $business_id)->leftjoin('users', 'transfer_balance_personal.sender', '=', 'users.id')->select(['transfer_balance_personal.created_at as at','transfer_balance_personal.receiver as receiver', 'transfer_balance_personal.phone as phone','users.first_name as sender','transfer_balance_personal.reason as reason','transfer_balance_personal.amount as amount', 'transfer_balance_personal.id as id'])->get();
        return view('transfer_balance.transactionByUser',compact('transfer_personal','receive_personal','total_bank_balance','id','receive_bank_balance','transfer_bank_balance'));
    }

    public function edit($id){
        if (!auth()->user()->can('rbpersonal.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $rb_personal = ReceiveBalancePersonal::where('business_id', $business_id)->find($id);
            return view('receive_balance.editPersonal')->with(compact('rb_personal'));
        }
    }

    public function update(Request $request, $id){
        if (!auth()->user()->can('rbpersonal.update')) {
            abort(403, 'Unauthorized action.');
        }
        $tbp_crid=ReceiveBalancePersonal::where('id',$id)->select('cash_register_id')->first();
        $cash_register_status=DB::table('cash_registers')->where('id',$tbp_crid->cash_register_id)->select('status')->first();
        if($cash_register_status->status=='close'){
            $output = ['success' => false,
                'msg' => __("You  can Not Update This Transaction as Register is closed For this Transaction.")
            ];
            return $output;
        }else{
            if (request()->ajax()) {
                try {
                    $input = $request->only(['receiver', 'phone','amount','reason']);
                    $business_id = $request->session()->get('user.business_id');
                    $raw_items = ReceiveBalancePersonal::where('business_id', $business_id)->findOrFail($id);
                    $raw_items->receiver = $input['receiver'];
                    $raw_items->phone = $input['phone'];
                    $raw_items->amount = $input['amount'];
                    $raw_items->reason = $input['reason'];
                    $raw_items->save();
                    $output = ['success' => true,
                        'msg' => __("Balance Transfered Successfully")
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
}