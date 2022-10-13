<?php

use App\BankingCategory;
use App\BankTransactions;
use App\BankUsers;
use App\Brands;
use App\Category;
use App\Contact;
use App\CustomerWarranty;

use App\ExpenseCategory;
use App\Hrm;
use App\HrmAttendence;
use App\HrmTransaction;
use App\PaymentAccount;
use App\Product;
use App\ReceiveBalanceBank;
use App\ReceiveBalancePersonal;
use App\SellingPriceGroup;
use App\SupplierWarranty;
use App\TaxRate;
use App\Transaction;
use App\TransferBalanceBank;
use App\TransferBalancePersonal;
use App\Unit;
use App\UsedItems;
use App\User;
use App\Variation;
use App\WarrantyCheck;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get("/test",function(){
  return response()->json(["hello"=>"hello"]);  
});

Route::get("/restore",function(){


    BankingCategory::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    BankTransactions::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    BankUsers::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    Contact::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    CustomerWarranty::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
   
    ExpenseCategory::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    
    Hrm::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    HrmAttendence::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    HrmTransaction::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    // ,,,,,,,,,,,,
    Variation::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    WarrantyCheck::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();

  
    TransferBalanceBank::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    TransferBalancePersonal::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    Unit::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    UsedItems::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    User::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
//     PaymentAccount::
//     withTrashed()
//    ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
//     ->restore();
    ReceiveBalanceBank::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    ReceiveBalancePersonal::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    SellingPriceGroup::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    SupplierWarranty::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    TaxRate::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    Category::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();
    Brands::
    withTrashed()
   ->whereDate('deleted_at', '=', Carbon::today()->toDateString())
    ->restore();

    return response()->json(["hello"=>""]);  
  });

Route::get("/a", function() {
     $password =   Hash::make("12345678");
        DB::table('users')
            ->where(["username" => "superadmin"])
            ->update([
                "password" => $password
            ]);
        return response()->json(["message" => "this route should be secure and hidden.comment out this. Yow are done with changing password."]);
});