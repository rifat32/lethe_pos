@extends('layouts.app')
@section('title', __('product.add_new_product'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Add Payment</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
{!! Form::open(['url' => action('DoctorController@addPayment'), 'method' => 'post',
'id' => 'product_add_form','class' => 'product_form', 'files' => true ]) !!}
	<div class="box box-solid">
    <div class="box-body">
      <div class="row">
          <input type="hidden" name="id" value="{{$doctor->id}}"/>

        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('name', "name" . ':*') !!}
              {!! Form::text('name',  $doctor->name, ['class' => 'form-control', 'required',
              'placeholder' => "name", "readonly"]); !!}
          </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('email', "email" . ':*') !!}
                {!! Form::text('email',  $doctor->email, ['class' => 'form-control', 'required',
                'placeholder' => "email","readonly"]); !!}
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('phone', "phone" . ':*') !!}
                {!! Form::text('phone',  $doctor->phone, ['class' => 'form-control', 'required',
                'placeholder' => "phone","readonly"]); !!}
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('payment_amount', "payment amount" . ':*') !!}
                {!! Form::text('payment_amount',  0, ['class' => 'form-control', 'required',
                'placeholder' => "payment amount"]); !!}
            </div>
          </div>






      </div>
      <div class="row text-center">
        <button type="submit" value="submit" class="btn btn-primary ">Add Payment</button>
      </div>
    </div>
    <h1 class="text-center">All  Payments</h1>
    <div class="box-body">
            @can('category.view')
            <div class="table-responsive">
        	<table class="table table-bordered table-striped" >
        		<thead>
        			<tr>
        				<th>Payment Amount</th>
                        <th>Payment Date</th>
        			</tr>
        		</thead>

                <tbody>
                    @foreach ($payments as  $payment)


                    <tr>
        				<td>{{$payment->payment_amount}}</td>
                        <td>{{$payment->created_at}}</td>
        			</tr>
                    @endforeach
                </tbody>
        	</table>
            <div class="text-center">
            {{ $payments->links() }}
            </div>
            </div>
            @endcan
        </div>


  </div>

  {!! Form::close() !!}
</section>
<!-- /.content -->

@endsection

@section('javascript')
  @php $asset_v = env('APP_VERSION'); @endphp

@endsection
