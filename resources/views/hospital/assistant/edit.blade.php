@extends('layouts.app')
@section('title', __('product.add_new_product'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Edit assistant</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
{!! Form::open(['url' => action('AssistantController@update'), 'method' => 'post',
'id' => 'product_add_form','class' => 'product_form', 'files' => true ]) !!}
	<div class="box box-solid">
    <div class="box-body">
      <div class="row">
          <input type="hidden" name="id" value="{{$doctor->id}}"/>

        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('name', "name" . ':*') !!}
              {!! Form::text('name',  $doctor->name, ['class' => 'form-control', 'required',
              'placeholder' => "name"]); !!}
          </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('email', "email" . ':*') !!}
                {!! Form::text('email',  $doctor->email, ['class' => 'form-control', 'required',
                'placeholder' => "email"]); !!}
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('phone', "phone" . ':*') !!}
                {!! Form::text('phone',  $doctor->phone, ['class' => 'form-control', 'required',
                'placeholder' => "phone"]); !!}
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('address', "address" . ':*') !!}
                {!! Form::textarea('address',  $doctor->address, ['class' => 'form-control', 'required',
                'placeholder' => "address", "rows" => "5"]); !!}
            </div>
          </div>

          {{-- <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('commission', "commission" . ':*') !!}
                {!! Form::text('commission',  $doctor->commission, ['class' => 'form-control', 'required',
                'placeholder' => "commission %"]); !!}
            </div>
          </div> --}}



      </div>
      <div class="row text-center">
        <button type="submit" value="submit" class="btn btn-primary ">@lang('messages.update')</button>
      </div>
    </div>
  </div>

  {!! Form::close() !!}
</section>
<!-- /.content -->

@endsection

@section('javascript')
  @php $asset_v = env('APP_VERSION'); @endphp

@endsection
