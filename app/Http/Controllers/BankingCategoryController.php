<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\BankingCategory;
class BankingCategoryController extends Controller{

    public function index(){
        if (!auth()->user()->can('ibcategory.view') || !auth()->user()->can('ibcategory.create')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $ib_category = BankingCategory::where('business_id', $business_id)
                        ->select(['name', 'code', 'id']);
            return Datatables::of($ib_category)
                ->addColumn(
                    'action',
                    '@can("ibcategory.update")
                    <button data-href="{{action(\'BankingCategoryController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".banking_category_modal"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                    &nbsp;
                    @endcan
                    @can("ibcategory.delete")
                    <button data-href="{{action(\'BankingCategoryController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_banking_category"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
                ->removeColumn('id')
                ->rawColumns([2])
                ->make(false);
        }
        return view('banking_category.index');
    }

    public function create(){
        if (!auth()->user()->can('ibcategory.create')) {
            abort(403, 'Unauthorized action.');
        }
        return view('banking_category.create');
    }

    public function store(Request $request){
        if (!auth()->user()->can('ibcategory.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $input = $request->only(['name', 'code']);
            $input['business_id'] = $request->session()->get('user.business_id');

            BankingCategory::create($input);
            $output = ['success' => true,
                            'msg' => __("Banking Category Added Successfully")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }
        return $output;
    }

    public function edit($id){
        if (!auth()->user()->can('ibcategory.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $expense_category = BankingCategory::where('business_id', $business_id)->find($id);
            return view('banking_category.edit')->with(compact('expense_category'));
        }
    }

    public function update(Request $request, $id){
        if (!auth()->user()->can('ibcategory.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'code']);
                $business_id = $request->session()->get('user.business_id');

                $expense_category = BankingCategory::where('business_id', $business_id)->findOrFail($id);
                $expense_category->name = $input['name'];
                $expense_category->code = $input['code'];
                $expense_category->save();

                $output = ['success' => true,
                            'msg' => __("Banking category updated successfully")
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

    public function destroy($id){
        if (!auth()->user()->can('ibcategory.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $expense_category = BankingCategory::where('business_id', $business_id)->findOrFail($id);
                $expense_category->delete();

                $output = ['success' => true,
                            'msg' => __("expense.deleted_success")
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
