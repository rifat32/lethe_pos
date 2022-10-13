@extends('layouts.app')
@section('title', 'Users')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Users
        <small>Manage Your Users</small>
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
        	<h3 class="box-title">All Users</h3>
            @if(auth()->user()->can('ibuser.create'))
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                	data-href="{{action('BankingController@create')}}" 
                	data-container=".bank_user_modal">
                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endif
        </div>
        <div class="box-body">
        @if(auth()->user()->can('ibuser.view'))
                <div class="table-responsive">
            	<table class="table table-bordered table-striped" id="users_bank_table">
            		<thead>
                    <tr>
            			<th>Name</th>
                        <th>Type</th>
                        <th>Phone</th>
                        <th>Account No</th>
            			<th>@lang( 'messages.action' )</th>
            		</tr>
            		</thead>
            	</table>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade bank_user_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
