@extends('layouts.app')
@section('title', __( 'Supplier Product Sell' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>SUPPLIER WISE DETAIL STOCK POSITION OF SHOP</h1>
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
              <div class="col-md-4">
                    <a onclick="window.print();" class="btn btn-sm btn-info" style="margin-top:26px"><i class="fa fa-print"></i></a>
                    <a class="btn btn-xs btn-info" style="margin-top:26px" id="excel"><i class="fa fa-download"> EXcel </i></a>
              </div>
           </form>
       </div>
       <div class="col-md-12">
           <div class="box box-solid">
               <div class="table">
                    <table class="table table-striped">
                        <tr>
                            <th>Supplier : </th>
                            <th>{{$sup}}</th>
                        </tr>
                        
                        <tr>
                            <th>Barcode</th>
                            <th>Product</th>
                            <th>Total Received</th>
                            <th>Sold To Customer</th>
                            <th>CPU</th>
                            <th>RPU</th>
                            <th>Balace Qty</th>
                            <th>Balance Value</th>
                        </tr>
                        <tbody>
                            @php
                            $remain_price=0;
                            $remain_stock=0;
                            @endphp
                            @foreach($results as $result)
                            
                            @php
                            $stock =$result->purchase_qty - $result->sell_qty;
                            $remain_stock +=$result->purchase_qty - $result->sell_qty;
                            $remain_price +=$stock * $result->default_purchase_price;
                            @endphp
                            <tr>
                                <td>{{ $result->sku}}</td>
                                <td>{{ $result->name}}</td>
                                <td>{{ number_format($result->purchase_qty,2)}}</td>
                                <td>{{ number_format($result->sell_qty,2)}}</td>
                                <td>{{ number_format($result->default_purchase_price,2)}}</td>
                                <td>{{ number_format($result->sell_price_inc_tax,2)}}</td>
                                <td>{{ number_format($result->purchase_qty - $result->sell_qty,2)}}</td>
                                <td>{{ number_format($stock * $result->default_purchase_price,2)}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th>Total</th>
                                <th>{{ number_format($results->sum('purchase_qty'),2)}}</th>
                                <th>{{ number_format($results->sum('sell_qty'),2)}}</th>
                                <th>0</th>
                                <th>0</th>
                                <th>{{number_format($remain_stock,2)}}</th>
                                <th>{{number_format($remain_price,2)}}</th>
                            </tr>
                        </tfoot>
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
            filename: 'detail_stock.xls'
        });
    })
</script>
@endsection
