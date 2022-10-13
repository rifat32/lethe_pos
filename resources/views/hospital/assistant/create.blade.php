@extends('layouts.app')
@section('title', __('product.add_new_product'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Add new Assistant</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
{!! Form::open(['url' => action('AssistantController@store'), 'method' => 'post',
'id' => 'product_add_form','class' => 'product_form', 'files' => true ]) !!}
	<div class="box box-solid">
    <div class="box-body">
      <div class="row">
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('name', "name" . ':*') !!}
              {!! Form::text('name',  null, ['class' => 'form-control', 'required',
              'placeholder' => "name"]); !!}
          </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('email', "email" . ':*') !!}
                {!! Form::text('email',  null, ['class' => 'form-control', 'required',
                'placeholder' => "email"]); !!}
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('phone', "phone" . ':*') !!}
                {!! Form::text('phone',  null, ['class' => 'form-control', 'required',
                'placeholder' => "phone"]); !!}
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('address', "address" . ':*') !!}
                {!! Form::textarea('address',  null, ['class' => 'form-control', 'required',
                'placeholder' => "address", "rows" => "5"]); !!}
            </div>
          </div>

        



      </div>
      <div class="row text-center">
        <button type="submit" value="submit" class="btn btn-primary ">@lang('messages.save')</button>
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
