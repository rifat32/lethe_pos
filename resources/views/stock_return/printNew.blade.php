<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Print Report</title>
<!-- Bootstrap 3.3.6 -->
<link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
</head>
<body>
<div class="report_print_area">
	<div class="container">
		<div class="row">
		  <div class="col-xs-12">
		    <h2 class="page-header">
		      Purchase Return (<b>@lang('purchase.ref_no'):</b> #{{ $sell_transfer->ref_no }})
		      <small class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($sell_transfer->transaction_date) }}</small>
		    </h2>
		  </div>
		</div>
		<div class="row invoice-info">


		  <div class="col-sm-4 invoice-col">
		    <b>@lang('purchase.ref_no'):</b> #{{ $sell_transfer->ref_no }}<br/>
		    <b>@lang('messages.date'):</b> {{ @format_date($sell_transfer->transaction_date) }}<br/>
		    <b>Supplier NAme:</b> {{ ($sell_transfer->name) }}<br/>
		  </div>
		</div>

		<br>
		<div class="row">
		  <div class="col-xs-12">
		    <div class="table-responsive">
		      <table class="table bg-gray">
		        <tr class="bg-green">
		          <th>#</th>
		          <th>@lang('sale.product')</th>
		          <th>@lang('sale.qty')</th>
		          <th>@lang('sale.unit_price')</th>
		          <th>@lang('sale.subtotal')</th>
		        </tr>
		        @php 
		          $total = 0.00;
		        @endphp
		        @foreach($products as $value)
		          <tr>
		            <td>{{ $loop->iteration }}</td>
		            <td>
		              {{ $value->name }}
		           
		            </td>
		            <td>{{ $value->quantity }}</td>
		            <td>{{ $value->unit_price }}</td>
		            <td>{{ ( $value->quantity * $value->unit_price) }}</td>
		          </tr>
		        @endforeach
		      </table>
		    </div>
		  </div>
		</div>
		<br>
		<div class="row">
		  
		  <div class="col-xs-6">
		    <div class="table-responsive">
		      <table class="table">
		        <tr>
		          <th>total purchase return:</th>
		          <td></td>
		          <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ $sell_transfer->final_total }}</span></td>
		        </tr>
		      </table>
		    </div>
		  </div>
		</div>
		<div class="row">
		  <div class="col-sm-6">
		    <strong>@lang('purchase.additional_notes'):</strong><br>
		    <p class="well well-sm no-shadow bg-gray">
		      @if($sell_transfer->additional_notes)
		        {{ $sell_transfer->additional_notes }}
		      @else
		        --
		      @endif
		    </p>
		  </div>
		</div>
	</div>
	
	
			<div class="row" style="margin-top:7%;margin-left:2%;margin-right:2%;">
			<table style="width:100%;">
				<tr>
					<td style="width:33%;text-align: center;">
						<div style="border-bottom: 1px solid #ddd;width: 80%;margin-left:10%;">
						</div>
						<strong>Receiver</strong>
					</td>
					<td  style="width:33%;text-align: center;">
						<div style="border-bottom: 1px solid #ddd;width: 80%;margin-left:10%;">
						</div>
							<strong style="">Prepared by</strong>
					</td>
					<td style="width:33%;text-align: center;">
						<div style="border-bottom: 1px solid #ddd;width: 80%;margin-left:10%;">
						</div>
						<strong style="">Authorized by</strong>
					</td>
				</tr>
			</table>
		</div>
	
	
</div>
</body>
</html>