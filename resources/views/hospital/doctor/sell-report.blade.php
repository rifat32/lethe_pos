@extends('layouts.app')
@section('title', 'Categories')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Report
        <small>Doctor Sell Report</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content" id="content">

	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">Doctor Sell Report</h3>
            
        </div>
        <div class="box-body">
            @can('category.view')
<form action="{{route("doctors.report")}}" method="get">
<div class="row">
    <div class="col-sm-4">
      <input class="form-control" name="from_date" type="date" value="{{$from_date}}" />
    </div>
    <div class="col-sm-4">
        <input class="form-control" name="to_date" type="date" value="{{$to_date}}" />
      </div>
      <div class="col-sm-4">
       <button type="submit" class="btn btn-primary"> Filter</button>
      </div>
</div>

</form>


            <div class="table-responsive">
        	<table class="table table-bordered table-striped" >
        		<thead>
        			<tr>
        				<th>Id</th>
        				<th>Date</th>
                        <th>Invoice No</th>
                        {{-- <th>Commission</th> --}}
                        <th>Earnings</th>
                        <th>Amount</th>

                        <th>@lang( 'messages.action' )</th>
        			</tr>
        		</thead>
@php
    $total_earnings = 0;
    $total_amount = 0;
@endphp
                <tbody>
                    @foreach ($transactions as  $transaction)
                   @php
                       $earnings = 0;
                       foreach ($transaction->sell_lines as $key => $sell_line) {
                        if($sell_line->doctor_id) {
                            $earnings += ((($sell_line->unit_price_inc_tax - $sell_line->cost) * $sell_line->doctor_commission)/100) * $sell_line->quantity;
                        }
                       
                       }
                       $total_earnings += $earnings;
                       $total_amount += $transaction->final_total
                   @endphp

                    <tr>
        				<td>{{$transaction->id}}</td>
        				<td>{{$transaction->created_at}}</td>
        				<td>{{$transaction->invoice_no}}</td>
                        <td>{{$earnings}}</td>
                        <td>{{$transaction->final_total}}</td>
                        <td>
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm"
                            onclick="invoice('{{route('sell.printInvoice', $transaction->id)}}')"
                             title="Print Invoice">
                                <i class="fa fa-print"></i>
                            </a>
                        </td>
        				

        			</tr>
                    @endforeach

                    <tr>
        				<td></td>
        				<td></td>
        				<td></td>
                        <td>Total Earning: <br> {{$total_earnings}} Taka</td>
                        <td>Sub Total: <br> {{$total_amount}} Taka</td>
                        
        				

        			</tr>
                    
                </tbody>
        	</table>
            <div class="text-center">
                {{ $transactions->links() }}
                </div>
            </div>
            @endcan
        </div>
    </div>

    <div class="modal fade category_modal" tabindex="-1" role="dialog"
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<section id="print-section1"></section>
<!-- /.content -->
<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
<script>
    const invoice = (link) => {


$.get(link, function(data){



pos_print2(data.receipt)

return
setTimeout(function(){
mywindow.print();
mywindow.close()
},1000);
});
}
</script>
@endsection
