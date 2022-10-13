@extends('layouts.app')
@section('title', __('Transaction'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Transaction
        <small>Manage Your Transaction</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->

<section class="content">
	<div class="box">
    <div class="box-body">
            <span id="view_contact_page"></span>
            <div class="row">
                <div class="col-sm-3">
                    <div class="well well-sm">
                        <strong>Name: {{$userInfo[0]->name}}</strong><br>
                        <strong>Type: {{$typeInfo[0]->name}} </strong><br>
                        <strong>Phone: {{$userInfo[0]->phone}}</strong><br>
                        <strong>Account_No: {{$userInfo[0]->account_no}}</strong><br>
                        <strong>Total Transfred: {{$minusInfo[0]->total}}</strong><br>
                        <strong>Total Received: {{$addInfo[0]->total}}</strong><br>
                        <!-- <strong>Total Balance: {{$userInfo[0]->balance}}</strong><br> -->
                        <strong><i class="fa fa-briefcase margin-r-5"></i> 
                        {{$businessInfo[0]->name}}</strong>
                    </div>
                </div>
           
                </div>
                </div>
                </div>
                
        <div class="box-header">
        	<h3 class="box-title">All Transaction</h3>    	
            <a href="{{route('banking.transaction.type.user',['type'=>'Received','id'=>$id])}}"class="btn btn-danger">Show Receive Ledger</a>
            <a href="{{route('banking.transaction.type.user',['type'=>'Transfered','id'=>$id])}}" class="btn btn-danger">Show Transfer Ledger</a>
            @if(auth()->user()->can('transaction.create'))
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                	data-href="{{action('BankTransactionController@create')}}" 
                	data-container=".bank_transaction_modal">
                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endif
        </div>
        <div class="box-body">
            @if(auth()->user()->can('banking.view') )
                <div class="table-responsive">
            	<table class="table table-bordered table-striped" id="transaction_table">
            		<thead>
            			<tr>
                            <th>@lang('lang_v1.t_id')</th>
                            <th>@lang('lang_v1.t_date')</th>
                            <th>User</th>
                            <th>@lang('lang_v1.type')</th>
                            <th>@lang('lang_v1.balance')</th>
                            <th>Actions</th>
                           
                          
            			</tr>
            		</thead>
                    <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td colspan="4" ><strong>@lang('sale.total'):</strong></td>
                            <td><span class="display_currency" id="footer_contact_due" data-currency_symbol ="true"></span></td>
                            <td colspan="1" ></td>
                           
                            
                        </tr>
                    </tfoot>
            	</table>
                </div>
            @endif
        </div>
    </div>
    <div class="modal fade bank_transaction_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

   

</section>
<!-- /.content -->

@endsection
