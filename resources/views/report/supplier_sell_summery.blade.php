@extends('layouts.app')
@section('title', __( 'Supplier Product Sell' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Supplier Wise Product Sell Report</h1>
    <a onclick="window.print();" class="btn btn-sm btn-info" style="margin-top:26px"><i class="fa fa-print"></i></a>
    <a class="btn btn-xs btn-info" style="margin-top:26px" id="excel"><i class="fa fa-download"> EXcel </i></a>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12 no-print">
           <form>
              <div class="col-md-4">
                  <div class="form-group">
                     <label>Supplier</label>
                     <select class="form-control" name="supplier_id" onchange="this.form.submit()">
                         @foreach($suppliers as $key=> $item)
                         <option value="{{$key}}" {{ request()->supplier_id==$key ?'selected':''}}>{{$item}}</option>
                         @endforeach
                     </select>
                  </div>
              </div>

              <div class="col-md-3">
                <label>Date From</label>
                <input type="text" name="start" class="form-control" id="start" value="{{request()->start ? request()->start:''}}" autocomplete="off">
              </div>
              <div class="col-md-3">
                <label>Date To</label>
                <input type="text" name="end" class="form-control" id="end" value="{{request()->end ? request()->end:''}}" autocomplete="off">
              </div>
              <div class="col-md-2">
                  <button type="submit" class="btn btn-xs btn-success" style="margin-top:26px">SUBMIT</button>
                  <a onclick="window.print();" class="btn btn-xs btn-info" style="margin-top:26px"><i class="fa fa-print"></i></a>
                  <a class="btn btn-xs btn-info" style="margin-top:26px" id="excel"><i class="fa fa-download"></i></a>
                  <a class="btn btn-xs btn-info" style="margin-top:26px" href="{{ action ('ReportController@supplierSellSumery')}}">Refresh</a>
              </div>
           </form>
       </div>
       <div class="col-md-12">
           <div class="box box-solid">
               <div class="table">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th>Supplier Name</th>
                            <th>Sell Qty</th>
                            <th>Return Qty</th>
                            <th>Purchase Amount</th>
                            <th>Sale Amount</th>
                            <th>Discount</th>
                            <th>Vat</th>
                            <th>Return Amount</th>
                            <th>Net Sale</th>
                            <th>Gross Profit</th>

                        </tr>
                        <tbody>
                            @php
                            $total_discount=0;
                            $total_tax=0;
                            $total_sell_quantity=0;
                            $total_return_quantity=0;
                            $total_purchase_price=0;
                            $total_sell_price=0;
                            $total_return_price=0;
                            $total_net_sell_price=0;
                            @endphp
                            
                            
                            @foreach($results as $item)
                            @if($item->sell_qty)
                            @php
                            $total_sell_quantity +=$item->sell_qty;
                            $total_return_quantity +=$item->return_qty;
                            $total_sell_price +=$item->sell_price;
                            $total_purchase_price +=$item->purchase_price;
                            $total_return_price +=$item->return_price;
                            $total_net_sell_price +=($item->sell_price);
                            $total_discount +=$item->discount_price;
                            $total_tax +=$item->tax_price;
                            @endphp
                            @endif
                            @endforeach
                            <tr>
                                <td>{{$sup}}</td>
                                <td>{{number_format($total_sell_quantity,2)}}</td>
                                <td>{{number_format($total_return_quantity,2)}}</td>
                                <td>{{number_format($total_purchase_price,2)}}</td>
                                <td>{{number_format($total_sell_price,2)}}</td>
                                <td>{{number_format($total_discount,2)}}</td>
                                <td>{{number_format($total_tax,2)}}</td>
                                <td>{{number_format($total_return_price,2)}}</td>
                                <td>{{number_format($total_net_sell_price,2)}}</td>
                                <td>{{number_format(($total_net_sell_price )- $total_purchase_price,2)}}</td>
                            </tr>
                            
                        </tbody>
                        
                    </table>  
                </div>
            </div>
       </div>
    </div>
</section>
@stop

@section('javascript')
<script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>
<script type="text/javascript">
$('#start, #end').datepicker({  format: 'yyyy-mm-dd' });
    $("#excel").click(function () {
        $(".table").table2excel({
            exclude: '.exclude',
            filename: 'stock_summery.xls'
        });
    })
</script>
@endsection
