@extends('layouts.app')
@section('title', __('Personal'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Personal
        <small>Manage Your Personal Transaction</small>
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
        	<h3 class="box-title">All Personal Transaction</h3>
            <br>
            <h3 class="text-center" >Your Total Balance is: {{ $total_balance}}</h3>
            @if(auth()->user()->can('tbpersonal.create'))
            	<div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                	data-href="{{action('TransferBalancePersonalController@create')}}" 
                	data-container=".transfer_balance_personal_modal">
                	<i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                </div>
            @endif
        </div>
        <div class="box-body">
            @if(auth()->user()->can('tbpersonal.view') )
                <div class="table-responsive">
            	<table class="table table-bordered table-striped" id="transfer_balance_personal_transaction_table">
            		<thead>
            			<tr> 
                            <th>Transaction Date</th>
                            <th>Receiver</th>
                            <th>Phone</th>
                            <th>Reason</th>
                            <th>Sender</th>
                            <th>Amount</th>
                            <th>Action</th>
            			</tr>
            		</thead>
                    <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td colspan="5" ><strong>@lang('sale.total'):</strong></td>
                            <td><span class="display_currency" id="footer_contact_due" data-currency_symbol ="true"></span></td>
                            <td colspan="1" ><strong>Action</strong></td>
                        </tr>
                    </tfoot>
            	</table>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade transfer_balance_personal_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
