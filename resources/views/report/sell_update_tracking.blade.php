@extends('layouts.app')
@section('title', __( 'report.purchase_sell' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> Sell {{$ActionType}} Report 
        <small>(Sell Invoice Tracking, Before {{$ActionType}})</small>
    </h1>
</section>
<br/><br/>
<!-- Main content -->
<section class="content">

    <div class="row">
           <div class="col-md-12">
               <div class="box box-solid">
                   <div class="table">
                        <table class="table table-striped">
                            <tr>
                                <th>#</th>
                                <th>Sale Date</th>
                                <th>Sale Updated Date</th>
                                <th>Updated By</th>
                                <th>Invoice No</th>
                                <th>Total Amount</th>
                          <!--      <th>Total Paid</th>
                                <th>Payment Due</th>-->
                                <th>Action</th>
                            </tr>
                            @foreach($transactions as $item)
                            <tr>
                                <td># {{$loop->index +1}}</td>
                                <td>{{date('m/d/Y',strtotime($item->transaction_date))}}</td>
                                <td>{{date('m/d/Y',strtotime($item->created_at))}}</td>
                                <td>{{$item->updatedBy? $item->updatedBy->username:''}}</td>
                                <td>{{$item->invoice_no}}</td>
                                <td>{{$item->final_total}}</td>
<!--                                <td>Total Paid</td>
                                <td>Payment Due</td>-->
                                <td>
                                    <small> <a href='{{ route("sellUpdateTrackingProduct",$item->transaction_id) }}' class="btn btn-sm btn-info">View</a> </small>
                                </td>
                            </tr>
                            @endforeach
                        </table>  
                    </div>
                </div>
           </div>

    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')


@endsection
