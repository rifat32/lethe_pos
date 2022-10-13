@extends('layouts.app')
@section('title', __('HRM Transactions'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>HRM Transactions
        <small>Manage Your HRM Transactions</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">

	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">All your transactions</h3>
            @if(auth()->user()->can('hrm_transaction.create'))
        	<div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                	data-href="{{action('HrmTransactionController@create')}}" 
                	data-container=".hrm_transactions_modal">
                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
            @endif
        </div>
        <div class="box-body">
        @if(auth()->user()->can('hrm_transaction.view'))
            <div class="table-responsive">
        	<table class="table table-bordered table-striped" id="hrm_transactions_table">
        		<thead>
        			<tr>
        				<th>Employee ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Paid At</th>
                        <th>Actions</th>
        			</tr>
        		</thead>
        	</table>
            </div>
            @endif
        </div>
    </div>

    <div class="modal fade hrm_transactions_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
