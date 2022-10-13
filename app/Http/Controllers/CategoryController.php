<?php

namespace App\Http\Controllers;

use App\Category;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('category.view') && !auth()->user()->can('category.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $category = Category::where('business_id', $business_id)->where('parent_id','0')
                        ->select(['name', 'short_code', 'order',   'id', 'parent_id']);

            return Datatables::of($category)
                ->addColumn(
                    'action',
                    '@can("category.update")
                    <button data-href="{{action(\'CategoryController@sub_categories\', [$id])}}" class="btn btn-xs btn-primary edit_category_button"><i class="glyphicon glyphicon-edit"></i>  Show Sub Category</button>
                        &nbsp;
                    <button data-href="{{action(\'CategoryController@edit\', [$id])}}" class="btn btn-xs btn-primary edit_category_button"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                        &nbsp;
                    @endcan
                    @can("category.delete")
                        <button data-href="{{action(\'CategoryController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_category_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
                ->editColumn('name', function ($row) {
                    // $parent = Category::where('business_id', $business_id)->where('parent_id',$row->parent_id)
                    //     ->select(['name'])->first();
                    if ($row->parent_id != 0) {
                        $business_id = request()->session()->get('user.business_id');
                        $parent = Category::where('business_id', $business_id)->where('id',$row->parent_id)->select(['name'])->first();
                        return $parent->name ."__". $row->name;
                    } else {
                        return $row->name;
                    }
                })
                ->removeColumn('id')
                ->removeColumn('parent_id')
                ->rawColumns([3])
                ->make(false);
        }

        return view('category.index');
    }

    public function sub_categories($id)
    {
        if (!auth()->user()->can('category.view') && !auth()->user()->can('category.create')) {
            abort(403, 'Unauthorized action.');
        }
            $business_id = request()->session()->get('user.business_id');
            $parent_name = Category::where('business_id', $business_id)->where('id',$id)->first();
            $sub_categories = Category::where('business_id', $business_id)->where('parent_id',$id)->get();
            //dd($sub_categories[0]->name);
        return view('category.sub_categories',compact('sub_categories','parent_name'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('category.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $categories = Category::where('business_id', $business_id)
                        ->where('parent_id', 0)
                        ->select(['name', 'short_code', 'id'])
                        ->get();
        $parent_categories = [];
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $parent_categories[$category->id] = $category->name;
            }
        }

        return view('category.create')
                    ->with(compact('parent_categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('category.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'short_code',  'order'
            // 'shipping_price'
        ]);
            if (!empty($request->input('add_as_sub_cat')) &&  $request->input('add_as_sub_cat') == 1 && !empty($request->input('parent_id'))) {
                $input['parent_id'] = $request->input('parent_id');
            } else {
                $input['parent_id'] = 0;
            }
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['created_by'] = $request->session()->get('user.id');

            $category = Category::create($input);
            $output = ['success' => true,
                            'data' => $category,
                            'msg' => __("category.added_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('category.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $category = Category::where('business_id', $business_id)->find($id);

            $parent_categories = Category::where('business_id', $business_id)
                                        ->where('parent_id', 0)
                                        ->where('id', '!=', $id)
                                        ->pluck('name', 'id');

            $is_parent = false;

            if ($category->parent_id == 0) {
                $is_parent = true;
                $selected_parent = null;
            } else {
                $selected_parent = $category->parent_id ;
            }

            return view('category.edit')
                ->with(compact('category', 'parent_categories', 'is_parent', 'selected_parent'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('category.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'short_code', 'order'
                // 'shipping_price'
            ]);
                $business_id = $request->session()->get('user.business_id');

                $category = Category::where('business_id', $business_id)->findOrFail($id);
                $category->name = $input['name'];
                $category->short_code = $input['short_code'];
                // $category->shipping_price = $input['shipping_price'];
                $category->order = $input['order'];

                if (!empty($request->input('add_as_sub_cat')) &&  $request->input('add_as_sub_cat') == 1 && !empty($request->input('parent_id'))) {
                    $category->parent_id = $request->input('parent_id');
                } else {
                    $category->parent_id = 0;
                }
                $category->save();

                $output = ['success' => true,
                            'msg' => __("category.updated_success")
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('category.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $category = Category::where('business_id', $business_id)->findOrFail($id);
                $category->delete();

                $output = ['success' => true,
                            'msg' => __("category.deleted_success")
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
