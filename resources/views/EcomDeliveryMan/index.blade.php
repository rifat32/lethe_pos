@extends('layouts.app')
@section('title', "delivery man")

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('sale.products')
        <small>@lang('lang_v1.manage_products')</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">@lang('lang_v1.all_products')</h3>

            	<div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action('EcommerceController@createDeliveryMan')}}">
    				<i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>

        </div>
        @if (Session::has("message"))
        <div class="alert alert-success">
            {{Session::get("message")}}
        </div>
    @endif
        <div class="box-body">
            @can('product.view')
                <div class="table-responsive">
            	<table class="table table-bordered table-striped ajax_view table-text-center" id="product_table">
            		<thead>
            			<tr>

                            <th>Id</th>
            				<th>Name</th>

    						<th>@lang('messages.action')</th>
            			</tr>
            		</thead>
                    <tbody>
                        @foreach ($deliveryMans as $deliveryMan)


                            <tr>
                                <td>{{$deliveryMan->id}}</td>
                                <td>{{$deliveryMan->name}}</td>
                                <td>
                                    <a href="{{route('delivery-man.edit',$deliveryMan->id)}}" class="btn btn-primary">Edit</a>
                                    <a href="{{route('delivery-man.delete',$deliveryMan->id)}}" class="btn btn-danger">Delete</a>




                                </td>


                            </tr>


                        @endforeach


                    </tbody>
            	</table>
                {{$deliveryMans->links()}}
                </div>
            @endcan
        </div>
    </div>


</section>
<!-- /.content -->

@endsection

@section('javascript')


@endsection
