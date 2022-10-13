@extends('layouts.app')
@section('title', __( 'Supplier Product Sell' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>SUPPLIER WISE STOCK SUMMERY</h>
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

              <div class="col-md-2">
                  <a onclick="window.print();" class="btn btn-xs btn-info" style="margin-top:26px"><i class="fa fa-print"></i></a>
                  <a class="btn btn-xs btn-info" style="margin-top:26px" id="excel"><i class="fa fa-download"></i></a>
              </div>
           </form>
       </div>
       <div class="col-md-12">
           <div class="box box-solid">
               <div class="table">
                    <table class="table table-bordered table-striped text-center">
                        <tr>
                            <th>Supplier Name</th>
                            <th colspan="2">Received</th>
                            <th colspan="2">Stock Return</th>
                            <th colspan="2">Sold</th>
                            <th colspan="2">Exchange /Void</th>
                            <th colspan="2">Balance</th>

                        </tr>

                        <tr>
                            <th></th>
                            <th>Qty</th>
                            <th>Value(Cost)</th>

                            <th>Qty</th>
                            <th>Value(Cost)</th>

                            <th>Qty</th>
                            <th>Value(Cost)</th>

                            <th>Qty</th>
                            <th>Value(Cost)</th>

                            <th>Qty</th>
                            <th>Value(Cost)</th>

                        </tr>
                        
                        <tbody>
                            @php
                            $return_price=0;
                            $purchase_price=0;
                            $sell_price=0;
                            $remain_price=0;
                            $remain_stock=0;
                            @endphp
                            @foreach($results as $result)
                            
                            @php
                            $stock =$result->purchase_qty - $result->sell_qty;
                            $purchase_price +=$result->purchase_qty * $result->default_purchase_price;
                            $sell_price +=$result->sell_qty * $result->sell_price_inc_tax;
                            $remain_stock +=$stock;
                            $remain_price +=$stock * $result->default_purchase_price;
                            $return_price +=$result->qty_returned * $result->default_purchase_price;
                            @endphp
                            @endforeach
                            <tr>
                                <td>{{$sup}}</td>
                                <td>{{ number_format($results->sum('purchase_qty'),2)}}</td>
                                <td>{{ number_format($purchase_price,2)}}</td>
                                <td>{{ number_format($results->sum('qty_returned'),2)}}</td>
                                <td>{{ number_format($return_price,2)}}</td>
                                
                                <td>{{ number_format($results->sum('sell_qty'),2)}}</td>
                                <td>{{ number_format($sell_price,2)}}</td>
                                <td>0</td>
                                <td>0</td>
                                <td>{{ number_format($remain_stock,2)}}</td>
                                <td>{{ number_format($remain_price,2)}}</td>
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
