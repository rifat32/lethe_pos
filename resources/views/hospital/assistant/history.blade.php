@extends('layouts.app')
@section('title', "Assistant History")

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>History</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

	<div class="box box-solid">
    <div class="box-body">
      <div class="row">
          <input type="hidden" name="id" value="{{$doctor->id}}"/>

        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('name', "name" . ':*') !!}
              {!! Form::text('name',  $doctor->name, ['class' => 'form-control', 'required',
              'placeholder' => "name",
              "readonly"
              ]); !!}
          </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('email', "email" . ':*') !!}
                {!! Form::text('email',  $doctor->email, ['class' => 'form-control', 'required',
                'placeholder' => "email",
              "readonly"]); !!}
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('phone', "phone" . ':*') !!}
                {!! Form::text('phone',  $doctor->phone, ['class' => 'form-control', 'required',
                'placeholder' => "phone",
              "readonly"]); !!}
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
              {!! Form::label('address', "address" . ':*') !!}
                {!! Form::textarea('address',  $doctor->address, ['class' => 'form-control', 'required',
                'placeholder' => "address", "rows" => "5",
              "readonly"]); !!}
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
    
    </div>
  </div>
  <div class="box box-solid">
    <div class="box-body">
     <table class="table table-stripe">
      <thead>
        <tr>
          <th>Id</th>
          <th>Date</th>
          <th>Invoice No</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($doctor->sells as $sell)
        <tr>
       <td>{{$sell->id}}</td>
       <td>{{$sell->created_at}}</td>
       <td>{{$sell->invoice_no}}</td>
       <th>
        <a href="{{route("assistants.details",['id' => $sell->id])}}" class="btn btn-success">View</a>
        
      </th>
        </tr>
        @endforeach
       
      </tbody>
    

     </table>
    
    </div>
  </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
  @php $asset_v = env('APP_VERSION'); @endphp

@endsection
