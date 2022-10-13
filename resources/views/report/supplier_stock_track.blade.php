@extends('layouts.app')
@section('title', __( 'Supplier Product Sell' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>SUPPLIER WISE SALES POSITION</h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
       <div class="col-md-12 no-print">
           <form>
                <div class="col-md-1">
                    <label>Shorting</label>
                    <select name="shorting" onchange="this.form.submit()">
                        <option value="50" {{ request('shorting')==50 ? 'selected' :''}}>50</option>
                        <option value="100" {{ request('shorting')==100 ? 'selected' :''}}>100</option>
                        <option value="500" {{ request('shorting')==500 ? 'selected' :''}}>500</option>
                        <option value="1000" {{ request('shorting')==1000 ? 'selected' :''}}>1000</option>
                        <option value="5000" {{ request('shorting')==5000 ? 'selected' :''}}>All</option>
                    </select>
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
                  <a href="{{ action('ReportController@supplierStockTrack')}}" class="btn btn-xs btn-info" style="margin-top:26px">Refrsh</a>
                  <a onclick="window.print();" class="btn btn-xs btn-info" style="margin-top:26px"><i class="fa fa-print"></i></a>
                  <a class="btn btn-xs btn-info" style="margin-top:26px" id="excel"><i class="fa fa-download"></i></a>
              </div>
           </form>
       </div>
       <div class="col-md-12">
           <div class="box box-solid">
               <div class="table">
                    <table class="table table-striped">
                        <tr>
                            <th>Suuplier : </th>
                            <th colspan="9"></th>
                        </tr>
                        <tr>
                            <th>Barcode</th>
                            <th>Product</th>
                            <th>Purchase Qty</th>
                            <th>Sell Qty</th>
                            <th>CPU</th>
                            <th>RPU</th>
                            <th>Discount</th>
                            <th>Vat</th>
                            <th>Total Sale</th>
                            <th>Total Purchase</th>
                        </tr>
                        <tbody>
                            @php
                                $count=0;
                                $total=0;
                                $total_purchase=0;
                                $total_discount=0;
                                $total_tax=0;
                            @endphp
                            @foreach($results as $result)
                            
                            @php
                            
                            $sell_stock=DB::table('transaction_sell_lines_purchase_lines as tslpl')
                                        ->join('transaction_sell_lines as tsl','tsl.id','=','tslpl.sell_line_id')
                                        ->where('tslpl.purchase_line_id',$result->id)
                                        ->sum('tslpl.quantity');
                            @endphp
                            
                            <tr>
                                <td>{{ $result->sku}}</td>
                                <td>{{ $result->name}} ({{$result->id}})</td>
                                <td>{{ $result->quantity ?? 0}}</td>
                                <td>{{ $sell_stock ?? 0}}</td>
                            
                            </tr>
                            @endforeach
                        </tbody>
                        
                    </table>  
                </div>
            </div>
            <p>{!! urldecode(str_replace("/?","?",$results->appends(Request::all())->render())) !!}</p>
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
            filename: 'sales_position.xls'
        });
    })
</script>
@endsection
