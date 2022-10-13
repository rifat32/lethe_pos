@extends('layouts.app')
@section('title', "commission")

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Commissions
        <small>Manage Commissions</small>
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
        	<h3 class="box-title">Manage Commissions</h3>
            @can('category.create')
        	<div class="box-tools">
                <a  class="btn btn-block btn-primary "
                	href="{{action('DoctorController@createCommission')}}"
                >
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
        				<th>Doctor</th>
        				<th>Service</th>
                        <th>Commission</th>

                        <th>@lang( 'messages.action' )</th>
        			</tr>
        		</thead>

                <tbody>
                    @foreach ($commissions as  $commission)
@if ($commission->service)
<tr>
    <td>{{$commission->doctor->name}}</td>
    <td>{{$commission->service->name}}</td>
    <td>{{$commission->doctor_commission}}</td>
    <td>

        <a href="{{route("commissions.edit",['id' => $commission->id])}}" class="btn btn-primary">Edit</a>
        <a href="{{route("commissions.delete",['id' => $commission->id])}}" class="btn btn-danger">Delete</a>
    </td>

</tr>
@endif

                 
                    @endforeach
                </tbody>
        	</table>
            <div class="text-center">
                {{ $commissions->links() }}
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
