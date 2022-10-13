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
use App\CashRegister;
use App\CashRegisterTransaction;
use Illuminate\Routing\Controller;
use App\Restaurant\InternalKitchen;
use App\Restaurant\DishCategory;
use App\Restaurant\DishList;
use App\UsedItems;
class BankingController extends Controller{
 
    public function index(Request $request){
        
        if (!auth()->user()->can('ibuser.view') || !auth()->user()->can('ibuser.create')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
           $business_id = $request->session()->get('user.business_id');
            $brands = BankUsers::where('bank_users.business_id', $business_id)->leftjoin('banking_categories', 'banking_categories.id', '=', 'bank_users.type_id')->select(
                'bank_users.id as id',
                'bank_users.name as name',
                'banking_categories.name as type_id',
                'bank_users.phone as phone',
                'bank_users.account_no as account_no',
            );
            return Datatables::of($brands)
            ->addColumn(
                'action',
                '@can("ibuser.update")
                <button data-href="{{action(\'BankingController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".bank_user_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                &nbsp;
                @endcan
                @can("ibuser.delete")
                <button data-href="{{action(\'BankingController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_brand_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                @endcan'
            )->removeColumn('id')
             ->rawColumns([4])
             ->make(false);
        }
        return view('banking.index');
    }

    public function create(){
        if (!auth()->user()->can('ibuser.create')) {
            abort(403, 'Unauthorized action.');
        }
        $quick_add = false;
        if (!empty(request()->input('quick_add'))) {
            $quick_add = true;
        }
        $type = DB::table('banking_categories')->get();
        return view('banking.create',compact('type','quick_add'));
    }

    public function store(Request $request){
        if (!auth()->user()->can('ibuser.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $input = $request->only(['name','type_id','phone','account_no']);
            $input['business_id'] = $request->session()->get('user.business_id');
            BankUsers::create($input);
                $output = ['success' => true,
                                'msg' => __("User added Successfully")
                            ];
           
        }catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }
        return $output;
        // return redirect()->action('BankingController@index');
    }

    public function edit($id){
        if (!auth()->user()->can('ibuser.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $bank_user = BankUsers::where('business_id', $business_id)->find($id);
            $type_id = BankUsers::select('type_id as type_id')->where('id', $id)->get();
            $type = DB::table('banking_categories')->get();
            return view('banking.edit')->with(compact('bank_user','type','type_id'));
        }
    }

    public function update(Request $request, $id){
        if (!auth()->user()->can('ibuser.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $input = $request->only(['name','type_id','phone','account_no']);
                $business_id = $request->session()->get('user.business_id');
                $bank_user = BankUsers::where('business_id', $business_id)->findOrFail($id);
                $bank_user->name = $input['name'];
                $bank_user->type_id = $input['type_id'];
                $bank_user->phone = $input['phone'];
                $bank_user->account_no = $input['account_no'];
                $bank_user->save();
                $output = ['success' => true,
                            'msg' => __("Internal Banking User updated successfully")
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

    public function destroy(Request $request , $id){
        if (!auth()->user()->can('ibuser.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $business_id = $request->session()->get('user.business_id');

                $brand = BankUsers::where('business_id', $business_id)->where('id', $id)->findOrFail($id);
                $brand->delete();

                $output = ['success' => true,
                            'msg' => __("brand.u_deleted_success")
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
