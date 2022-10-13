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
        <div class="box-header">
            <h3 class="box-title">All Transaction</h3>
            <br>
            <h3 class="text-center">Your Total Balance is: {{ $total_balance}}</h3>
            @if(auth()->user()->can('ibtransaction.create'))
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                	data-href="{{action('BankTransactionController@create')}}" 
                	data-container=".bank_transaction_modal">
                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endif
        </div>
        <div class="box-body">
        @if(auth()->user()->can('ibtransaction.view'))
                <div class="table-responsive">
            	<table class="table table-bordered table-striped" id="transaction_table">
            		<thead>
            			<tr>
                            <th>@lang('lang_v1.t_id')</th>
                            <th>@lang('lang_v1.t_date')</th>
                            <th>User</th>
                            <th>@lang('lang_v1.type')</th>
                            <th>@lang('lang_v1.balance')</th>
                            <th>Action</th>
            			</tr>
            		</thead>
                    <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td colspan="4" ><strong>@lang('sale.total'):</strong></td>
                            <td ><span class="display_currency" id="footer_contact_due" data-currency_symbol ="true"></span></td>
                            <th>Action </th>
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
