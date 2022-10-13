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
    <div class="box-body">
            <span id="view_contact_page"></span>
            <div class="row">
                <div class="col-sm-3">
                    <div class="well well-sm">
                        <strong>Name: {{$id}}</strong><br>
                        <strong>Total Received: {{$receive_bank_balance[0]->total_receive_balance}} </strong><br>
                        <strong>Total Transfered: {{$transfer_bank_balance[0]->total_transfer_balance}}</strong><br>
                        <strong>Available Balance: {{$total_bank_balance}}</strong><br>
                  
                    </div>
                </div>
                </div>
                </div>
                
        <div class="box-header">
        	<h3 class="box-title">Bank Transfer Transactions</h3>
            <br>
          
           
        </div>
        <div class="box-body">
            @if(auth()->user()->can('tbbank.view') )
                <div class="table-responsive">
            	<table class="table table-bordered table-striped">
            		<thead>
            			<tr>
                            <th>Transaction Date</th>
                            <th>Bank Name</th>
                            <th>Branch</th>
                            <th>Sender</th>
                            <th>Account No</th>
                            <th>Amount</th>
            			</tr>
            		</thead>
                    <tbody>
                    @if($transferBank)
                    @foreach($transferBank as $transfer)
                        <tr>
                            <td>{{$transfer->at}}</td>
                            <td>{{$transfer->bank}}</td>
                            <td>{{$transfer->branch}}</td>
                            <td>{{$transfer->sender}}</td>
                            <td>{{$transfer->account_no}}</td>
                            <td>{{$transfer->amount}}</td>  
                        </tr>
                        @endforeach
                        <tr>
                    @else
                
                    <td colspan="6" ><strong>No Data Found</strong></td>
                    </tr>
                        @endif
                        </tbody>
                        <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td colspan="5" ><strong>@lang('sale.total'):</strong></td>
                            <td>৳ {{$transfer_bank_balance[0]->total_transfer_balance}}</td>
                           
                        </tr>
                        </tr>
                    </tfoot>
            	</table>
                </div>
            @endif
        </div>
                  
        <div class="box-header">
        	<h3 class="box-title">Bank Receive Transactions</h3>
            <br>
          
           
        </div>
        <div class="box-body">
            @if(auth()->user()->can('tbbank.view') )
                <div class="table-responsive">
            	<table class="table table-bordered table-striped">
            		<thead>
            			<tr>
                            
                            <th>Transaction Date</th>
                            <th>Bank Name</th>
                            <th>Branch</th>
                            <th>Receiver</th>
                            <th>Account No</th>
                            <th>Amount</th>
                       
            			</tr>
            		</thead>
                    <tbody>
                    @if($receiverBank)
                    @foreach($receiverBank as $receiver)
                        <tr>
                            <td>{{$receiver->at}}</td>
                            <td>{{$receiver->bank}}</td>
                            <td>{{$receiver->branch}}</td>
                            <td>{{$receiver->receiver}}</td>
                            <td>{{$receiver->account_no}}</td>
                            <td>{{$receiver->amount}}</td>  
                        </tr>
                        @endforeach
                  
                    @else
                    <tr>
                    <td colspan="6" ><strong>No Data Found</strong></td>
                    </tr>
                        @endif
                        </tbody>
                        <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td colspan="5" ><strong>@lang('sale.total'):</strong></td>
                            <td>৳ {{$receive_bank_balance[0]->total_receive_balance}}</td>
                        </tr>
                        </tr>
                    </tfoot>
            	</table>
                </div>
            @endif
        </div>
    </div>

</section>
<!-- /.content -->

@endsection
