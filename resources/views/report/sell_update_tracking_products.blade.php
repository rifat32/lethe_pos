@extends('layouts.app')
@section('title', __( 'report.purchase_sell' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> Sell Update Report Product Details
        <small>(Sell Invoice Tracking, Before Update)</small>
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
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Tax</th>
                                <th>Price inc. tax</th>
                                <th>Subtotal</th>
                            </tr>
                           @foreach($transactions as $item)
                           <tr>
                               <td>#{{$loop->index+1}}</td>
                               <td>{{$item->products->name}}</td>
                               <td>{{$item->quantity}}</td>
                               <td>{{$item->unit_price}}</td>
                               <td>{{$item->item_tax}}</td>
                               <td>{{$item->unit_price_inc_tax}}</td>
                               <td>{{number_format($item->quantity * $item->unit_price,2)}}</td>
                           </tr>
                           @endforeach
                           <tr>
                               <td colspan="6" style="text-align:right;">Total</td>
                               <td >{{number_format($total_amount,2)}}</td>
                           </tr>
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
