@extends('layouts.app')
@section('title', "Add Commission")

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Add New Commission</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">




{!! Form::open(['url' => action('DoctorController@CommissionStore'), 'method' => 'post',
'id' => 'product_add_form','class' => 'product_form', 'files' => true ]) !!}




	<div class="box box-solid">
    <div class="box-body">
      <div class="row">

        <div class="col-sm-6 ">
            <div class="form-group" style="width: 100% !important">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-user"></i>
                    </span>




                    {!! Form::select('doctor_id',
                        [], null, ['class' => 'form-control mousetrap', 'id' => 'doctor_id', 'placeholder' => 'Enter Doctor Name / phone', 'required', 'style' => 'width: 100%;']); !!}
                    <span class="input-group-btn">
                        <a href="{{action('DoctorController@create')}}"  class="btn btn-default bg-white btn-flat add_new_doctor" data-name=""  @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-primary fa-lg"></i></a>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 ">
            <div class="form-group" style="width: 100% !important">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-user"></i>
                    </span>




                    {!! Form::select('service_id',
                        [], null, ['class' => 'form-control mousetrap', 'id' => 'service_id', 'placeholder' => 'Enter Service Name', 'required', 'style' => 'width: 100%;']); !!}
                    <span class="input-group-btn">
                        <a href="{{action('ServiceController@create')}}"  class="btn btn-default bg-white btn-flat add_new_doctor" data-name=""  @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-primary fa-lg"></i></a>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                {!! Form::label('name', "Doctor Commission". ':*') !!}
                  {!! Form::text('doctor_commission', null, ['class' => 'form-control', 'required', 'placeholder' =>"Commission"]); !!}
              </div>
          </div>


        <!--custom fields-->
      </div>
    </div>
  </div>


  <div class="row">
    <div class="col-sm-12">
      <input type="hidden" name="submit_type" id="submit_type">
      <div class="text-center">
      <div class="btn-group">
      

        {{-- <button id="opening_stock_button" @if(!empty($duplicate_product) && $duplicate_product->enable_stock == 0) disabled @endif type="submit" value="submit_n_add_opening_stock" class="btn bg-purple submit_product_form">@lang('lang_v1.save_n_add_opening_stock')</button> --}}

        <button type="submit" value="save_n_add_another" class="btn bg-maroon submit_product_form">@lang('lang_v1.save_n_add_another')</button>

        <button type="submit" value="submit" class="btn btn-primary submit_product_form">@lang('messages.save')</button>
      </div>

      </div>
    </div>
  </div>
  {!! Form::close() !!}
</section>
<!-- /.content -->

@endsection

@section('javascript')
  @php $asset_v = env('APP_VERSION'); @endphp
  {{-- <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script> --}}

  <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
@endsection
