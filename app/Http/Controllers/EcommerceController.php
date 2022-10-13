<?php

namespace App\Http\Controllers;

use App\EcomCustommers;
use App\EcomUsers;
use App\Upload;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class EcommerceController extends Controller
{
    public function index(Request $request)
    {
        $sort_search = null;
        $customers = EcomCustommers::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $user_ids = EcomUsers::where('user_type', 'customer')->where(function($user) use ($sort_search){
                $user->where('name', 'like', '%'.$sort_search.'%')->orWhere('email', 'like', '%'.$sort_search.'%');
            })->pluck('id')->toArray();
            $customers = $customers->where(function($customer) use ($user_ids){
                $customer->whereIn('user_id', $user_ids);
            });
        }
        $customers = $customers->paginate(15);

        return view('EcomCustomers.index', compact('customers', 'sort_search'));

    }
    public function getSlider(Request $request) {

        $all_uploads =   Upload::query();
        $search = null;
        $sort_by = null;

        if ($request->search != null) {
            $search = $request->search;
            $all_uploads->where('file_original_name', 'like', '%'.$request->search.'%');
        }

        $sort_by = $request->sort;
        switch ($request->sort) {
            case 'newest':
                $all_uploads->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $all_uploads->orderBy('created_at', 'asc');
                break;
            case 'smallest':
                $all_uploads->orderBy('file_size', 'asc');
                break;
            case 'largest':
                $all_uploads->orderBy('file_size', 'desc');
                break;
            default:
                $all_uploads->orderBy('created_at', 'desc');
                break;
        }

        $all_uploads = $all_uploads->paginate(60)->appends(request()->query());




      return   view('EcomSliders.index', compact('all_uploads', 'search', 'sort_by'));


    }
   public function deleteSlider($id){
       $file =  Upload::find($id);

  $file->delete();
$image = "/uploads/all/" . $file->file_original_name;
Storage::delete($image);
//   unlink($image);


   return back();
   }
    public function createSlider(Request $request ) {
        return   view('EcomSliders.create');


    }

    public function storeSlider(Request $request) {

        $request->validate([
            'file' => 'required|image|min:100|max:3000',
        ]);



        $img_name_full =  time() . '.' . $request->file->extension();

//   Storage::disk('local')->put('uploads/all', $img_name_full);



    $imageUpload = new Upload();
    $imageUpload->file_original_name =   $img_name_full;
    $imageUpload->file_name = "uploads/all/" . $img_name_full;
    $imageUpload->extension =  $request->file->extension();
    $imageUpload->type =  "image";

    $imageUpload->save();
    $request->file->move(public_path('uploads/all'), $img_name_full);
    return response()->json(['success' => $img_name_full]);

    }





    public function getDeliveryMan() {

        $deliveryMans = DB::table("delivery_man")->paginate(10);



        return view("EcomDeliveryMan.index",["deliveryMans" =>$deliveryMans]);


    }
    public function deleteDeliveryMan($id) {

         DB::table("delivery_man")->where('id',$id)->delete();

       return back()->with(["message" => "Delivery Man Deleted Successfully"]);


    }

    public function createDeliveryMan(Request $request) {



        return view("EcomDeliveryMan.create");


    }
    public function editDeliveryMan($id) {

      $delivery_man =  DB::table("delivery_man")->where('id',$id)->first();

        return view("EcomDeliveryMan.edit",['delivery_man'=>$delivery_man]);


    }

    public function updateDeliveryMan(Request $request) {


        DB::table("delivery_man")->where(['id'=>$request->id])->update(["name" => $request->name]);

        return redirect()->route('delivery-man.view')->with(["message" => "Delivery Man updated Successfully"]);
       return  back()->with(["message" => "Delivery Man Deleted Successfully"]);

    }


    public function storeDeliveryMan(Request $request) {

     $validatedData =    $request->validate([
            "name"=>"required"
        ]);
     $delivery_man =   DB::table("delivery_man")->insert(["name" => $validatedData["name"]]);
return back()->with(["message" => "Delivery Man Inserted"]);

    }




}
