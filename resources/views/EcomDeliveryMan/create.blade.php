@extends('layouts.app')
@section('title', "delivery man")

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Delivery man
        <small>Delivery man</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    @if (Session::has("message"))
        <div class="alert alert-success">
            {{Session::get("message")}}
        </div>
    @endif

	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">Add Delivery Man</h3>

        </div>
        <div class="box-body">

            <form action="{{route('delivery-man.store')}}" method="POST">
                {{ csrf_field() }}
          <div class="row">

            <div class="col-md-6">

                 <div class="form-group">
                     <input class="form-control" name="name"/>
                 </div>


            </div>
          </div>
<button class="btn btn-primary"> Submit</button>

            </form>

        </div>
    </div>


</section>
<!-- /.content -->

@endsection

@section('javascript')


@endsection
