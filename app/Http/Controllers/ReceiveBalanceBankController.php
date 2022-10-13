<?php
namespace App\Http\Controllers;
use App\ExpenseCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\ReceiveBalanceBank;
use DB;
use App\CashRegister;
use App\Utils\CashRegisterUtil;
class ReceiveBalanceBankController extends Controller{

    protected $cashRegisterUtil;

    public function __construct(CashRegisterUtil $cashRegisterUtil)
    {
        $this->cashRegisterUtil = $cashRegisterUtil;
    }

    public function index(Request $request){
        if (!auth()->user()->can('rbbank.view') && !auth()->user()->can('rbbank.create')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $expense_category = ReceiveBalanceBank::where('receive_balance_bank.business_id', $business_id)->leftjoin('users', 'receive_balance_bank.receiver', '=', 'users.id')->select(['receive_balance_bank.created_at','receive_balance_bank.bank_name as bankName', 'receive_balance_bank.branch','users.first_name','receive_balance_bank.account_no','receive_balance_bank.amount', 'receive_balance_bank.id']);
            return Datatables::of($expense_category)
            ->editColumn('bankName', function ($row){
                $bankName = $row->bankName;
                return '<a href="'.action('TransferBalanceBankController@transactionByBank', [$row->bankName,$row->branch,$row->account_no]) . '" target="_blank">'.$bankName .'</a>';
            })->addColumn(
                'action',
                    '@can("rbbank.update")
                        <button data-href="{{action(\'ReceiveBalanceBankController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".receive_balance_bank_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                    @endcan
                    @can("rbbank.delete")
                        <button data-href="{{action(\'ReceiveBalanceBankController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_receive_balance_bank"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )->removeColumn('id')
                ->rawColumns([6])
                ->escapeColumns(null)
                ->make(false);
        }
        return view('receive_balance.bank');
    }

    public function create(){
        if (!auth()->user()->can('rbbank.create')) {
            abort(403, 'Unauthorized action.');
        }
        $banks = DB::table('transfer_balance_bank')
            ->select('bank_name')
            ->groupBy('bank_name')
            ->get();
        return view('receive_balance.createBank',compact('banks'));
    }

    public function store(Request $request){
        if (!auth()->user()->can('rbbank.create')) {
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
            $bank_name = $request->get('bank_name');
            $bank_receive=DB::table('transfer_balance_bank')->where('bank_name',$bank_name)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $bank_transfer=DB::table('receive_balance_bank')->where('bank_name',$bank_name)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
            $total_balance = $bank_receive->total - $bank_transfer->total ;
            try {
                $input = $request->only(['bank_name','branch','account_no','amount','receiver']);
                $input['business_id'] = $request->session()->get('user.business_id');
                $input['cash_register_id'] = $cash_register_id->id;
                $inputs = $request->get('amount');
                if($total_balance >= $inputs){
                    ReceiveBalanceBank::create($input);
                    $output = ['success' => true,
                        'msg' => __("Balance Received from bank Successfully")
                    ];
                }else{
                    $output = ['success' => false,
                        'msg' => __("You Do not have enough Balance !")
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
        if (!auth()->user()->can('rbbank.delete')) {
            abort(403, 'Unauthorized action.');
        }
        $tbp_crid=ReceiveBalanceBank::where('id',$id)->select('cash_register_id')->first();
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
                    $expense_category = ReceiveBalanceBank::where('business_id', $business_id)->findOrFail($id);
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
        if (!auth()->user()->can('rbbank.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $rb_bank = ReceiveBalanceBank::where('business_id', $business_id)->find($id);
            $banks = DB::table('transfer_balance_bank')->where('deleted_at',null)
            ->select('bank_name')
            ->groupBy('bank_name')
            ->get();
            return view('receive_balance.editBank')->with(compact('rb_bank','banks'));
        }
    }
   
    public function update(Request $request, $id){
        if (!auth()->user()->can('rbbank.update')) {
            abort(403, 'Unauthorized action.');
        }
        $tbp_crid=ReceiveBalanceBank::where('id',$id)->select('cash_register_id')->first();
        $cash_register_status=DB::table('cash_registers')->where('id',$tbp_crid->cash_register_id)->select('status')->first();
        if($cash_register_status->status=='close'){
            $output = ['success' => false,
                'msg' => __("You  can Not Update This Transaction as Register is closed For this Transaction.")
            ];
            return $output;
        }else{
            if (request()->ajax()) {
                try {
                    $bank_name = $request->get('bank_name');
                    $bank_receive=DB::table('transfer_balance_bank')->where('bank_name',$bank_name)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
                    $bank_transfer=DB::table('receive_balance_bank')->where('bank_name',$bank_name)->where('deleted_at',null)->select(DB::raw("SUM(amount) as total"))->first();
                    $total_balance = $bank_receive->total -$bank_transfer->total;
                    $input = $request->only(['bank_name', 'branch','account_no','amount']);
                    $business_id = $request->session()->get('user.business_id');
                    $raw_items = ReceiveBalanceBank::where('business_id', $business_id)->findOrFail($id);
                    $prev_amount = $raw_items->amount;
                    $inputs = $request->get('amount');
                    if(($total_balance + $prev_amount) >= $inputs){
                        $raw_items->bank_name = $input['bank_name'];
                        $raw_items->branch = $input['branch'];
                        $raw_items->account_no = $input['account_no'];
                        $raw_items->amount = $input['amount'];
                        $raw_items->save();
                        $output = ['success' => true,
                        'msg' => __("Balance Received Successfully")
                    ];
                    }else{
                        $output = ['success' => false,
                            'msg' => __("You Do not have enough Balance !")
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