@extends('layouts.app')
@section('title', 'Categories')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Doctors
        <small>Manage Doctors</small>
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
        	<h3 class="box-title">Manage Doctors</h3>
            @can('category.create')
        	<div class="box-tools">
                <a class="btn btn-block btn-primary"
                	href="{{action('DoctorController@create')}}">

                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
            </div>
            @endcan
        </div>
        <div class="box-body">
            @can('category.view')
            <div class="table-responsive">
        	<table class="table table-bordered table-striped" >
        		<thead>
        			<tr>
        				<th>Name</th>
        				<th>Email</th>
                        <th>Phone</th>
                        {{-- <th>Commission</th> --}}
                        <th>Earnings</th>
                        <th>Payments</th>
                        <th>Due</th>
                        <th>@lang( 'messages.action' )</th>
        			</tr>
        		</thead>

                <tbody>
                    @foreach ($doctors as  $doctor)
                    @php
                    $earnings = 0;
                    $commission = 0;

                    for($i=0; $i<count($doctor->sells); $i++) {

                        for($j=0; $j<count($doctor->sells[$i]->sell_lines); $j++) {
                            $commission = 0;
                        
                            // for($k=0; $k<count($doctor->commissions); $k++) {
                            //      if($doctor->commissions[$k]->service_id == $doctor->sells[$i]->sell_lines[$j]->product_id){
                            //         $commission = $doctor->commissions[$k]->doctor_commission;
                            //      }
                            // }

                            // echo  $commission;
                            if($doctor->sells[$i]->sell_lines[$j]->doctor_commission){
                                $commission = $doctor->sells[$i]->sell_lines[$j]->doctor_commission;
                            }

                        $earnings +=    ((($doctor->sells[$i]->sell_lines[$j]->unit_price_inc_tax - $doctor->sells[$i]->sell_lines[$j]->cost) * $commission) / 100) * $doctor->sells[$i]->sell_lines[$j]->quantity ;

                    }

                    }
                    $payments = 0;
                    for($i=0; $i<count($doctor->payments); $i++) {
                        $payments +=   $doctor->payments[$i]->payment_amount;
                    }
                @endphp

                    <tr>
        				<td>{{$doctor->name}}</td>
        				<td>{{$doctor->email}}</td>
        				<td>{{$doctor->phone}}</td>
        				{{-- <td>{{$doctor->commission}}</td> --}}
                        <td>
                          {{$earnings}}

                        </td>
                        <td>
                            {{$payments}}
                          </td>
                          <td>
                            {{$earnings - $payments}}
                          </td>
        				<td>
                            <a href="{{route("doctors.payment",['id' => $doctor->id])}}" class="btn btn-warning">Payment</a>
                            <a href="{{route("doctors.edit",['id' => $doctor->id])}}" class="btn btn-primary">Edit</a>
                            <a href="{{route("doctors.delete",['id' => $doctor->id])}}" class="btn btn-danger">Delete</a>
                        </td>

        			</tr>
                    @endforeach
                </tbody>
        	</table>
            <div class="text-center">
                {{ $doctors->links() }}
                </div>
            </div>
            @endcan
        </div>
    </div>

    <div class="modal fade category_modal" tabindex="-1" role="dialog"
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
