@extends('layouts.app')
@section('title', __('Attendence'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Employee Attendence
        <small>Manage Your Employees Attendence</small>
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
        	<h3 class="box-title">All employees attendence</h3>
            @if(auth()->user()->can('hrm_attendence.create'))
            <div class="box-tools">
                    <a class="btn btn-block btn-primary" 
                    	href="{{action('HrmAttendenceController@create')}}" >
                    	<i class="fa fa-plus"></i> Give Attendence</a>
                </div>
        	<!-- <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary" 
                	data-href="{{action('HrmAttendenceController@create')}}">
                	<i class="fa fa-plus"></i> Give Attendence</button>
            </div> -->
            @endif
        </div>
        <div class="box-body">
        @if(auth()->user()->can('hrm_attendence.view'))
            <div class="table-responsive">
        	<table class="table table-bordered table-striped" id="hrm_attendence_table">
        		<thead>
        			<tr>
        				<th>Employee ID</th>
                        <th>Date</th>
        				<th>Status</th>
                        <th>Actions</th>
        			</tr>
        		</thead>
        	</table>
            </div>
            @endif
        </div>
    </div>

    <div class="modal fade hrm_attendence_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@endsection
