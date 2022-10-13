@extends('layouts.app')
@section('title', __('Bank'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Bank
        <small>Manage Your Bank Transaction</small>
    </h1>
</section>

<!-- Main content -->

<section class="content">
	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">All Bank Transaction</h3>
            <br>
            <h3 class="text-center">Your Total Balance is: {{ $total_balance}}</h3>
            @if(auth()->user()->can('tbbank.create'))
            	<div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                	data-href="{{action('TransferBalanceBankController@create')}}" 
                	data-container=".transfer_balance_bank_modal">
                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endif
        </div>
        <div class="box-body">
            @if(auth()->user()->can('tbbank.view') )
                <div class="table-responsive">
            	<table class="table table-bordered table-striped" id="transfer_balance_transaction_table">
            		<thead>
            			<tr>
                            
                            <th>Transaction Date</th>
                            <th>Bank Name</th>
                            <th>Branch</th>
                            <th>Sender</th>
                            <th>Account No</th>
                            <th>Amount</th>
                            <th>Action</th>
            			</tr>
            		</thead>
                    <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td colspan="5" ><strong>@lang('sale.total'):</strong></td>
                            <td><span class="display_currency" id="" data-currency_symbol ="true"></span></td>
                            <td colspan="1" ><strong>Action</strong></td>
                        </tr>
                        </tr>
                    </tfoot>
            	</table>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade transfer_balance_bank_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
</section>
<!-- /.content -->

@endsection
