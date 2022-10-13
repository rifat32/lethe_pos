@extends('layouts.app')
@section('title', __('report.stock_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Physical {{ __('report.stock_report')}}</h1>
</section>

<!-- Main content -->
<section class="content">
    <form action="" method="GET" autocomplete="off">
    <div class="row">
        <div class="col-md-3">
            <label for="exampleInputEmail1">From:</label>
            <input type="text" class="form-control" id="datepicker" name="start_date" placeholder="yyyy-mm-dd" value="{{request()->start_date ? request()->start_date:''}}">
        </div>
        <div class="col-md-3">
            <label for="exampleInputEmail1">To:</label>
            <input type="text" class="form-control" id="datepicker1" name="end_date" placeholder="yyyy-mm-dd" value="{{request()->end_date ? request()->end_date:''}}">
        </div>
        <div class="col-md-3">
            <label for="Product">Product</label>
            <input type="text" name="name" class="form-control" value="{{request()->name ? request()->name:''}}">
        </div>
        <div class="col-md-1">
            <label for="Product">&nbsp;</label>
            <input type="submit" value="Search" class="form-control btn btn-sm btn-primary btn-sm">
        </div>
        <div class="col-md-2">
            <label for="Product">&nbsp;</label> <br/>
            <a href="{{ action('ReportController@getPhysicalStockReport') }}" class="btn btn-md btn-info btn-sm">Refresh</a> 
            <a href="{{ action('ReportController@getPhysicalStockReportPrint',[$from,$end,$name]) }}" target="_blank" class="btn btn-md btn-primary btn-sm">Print</a> 
            <a id="btnExport" class="btn btn-sm btn-info">Export to excel</a>
        </div>
    </div>
    </form>
        <br/>

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                
                <div class="box-body">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table" border="1">
                   
                            <tr>
                                <th>SKU</th>
                                <th>@lang('business.product')</th>
                                <th>Current Stock <br/><small>(balancing time)</small></th>
                                <th>Balance</th>
                                <th>Physical Stock</th>
                                <th>Action</th>
                            </tr>
                       
                            @foreach ($products as $item)     
                            <tr>
                                <td>{{ $item->sku }}</td>
                                <td>{{ $item->name }}</td>
                                <td>
                                    {{ $item->current_stock }}
                                </td>
                                <td>
                                    {{ $item->physical_qty - $item->current_stock  }}
                                </td>
                                <td>
                                    {{ $item->physical_qty }}
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{action('ReportController@StockReportdetails',$item->id)}}">Details</a>
                                </td>
                            </tr>
                            @endforeach
                     
                     
                            <tr class="bg-gray font-17  footer-total">
                                <td colspan="1"><strong>@lang('sale.total'):</strong></td>
                                <td ></td>
                                <td >{{ $total_current_stock }}</td>
                                <td ></td>
                                <td >{{ $total }}</td>
                                <td ></td>
                            </tr>
                    
                        
                 
                            <tr class="bg-green font-17"><td colspan="6"><h4>Product Not In Physical Stock</h4></td></tr>
                            <tr>
                                <th>SKU</th>
                                <th>@lang('business.product')</th>
                                <th>Current Stock <br/><small>(balancing time)</small></th>
                                <th>Balance</th>
                                <th>Physical Stock</th>
                                <th>Action</th>
                            </tr>
                       
                            @foreach ($stocks as $item)     
                            <tr>
                                <td>{{ $item->product->sku }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td>
                                    {{ $item->qty_available }}
                                </td>
                                <td>
                                    -{{ $item->qty_available }}
                                </td>
                                <td>
                                    00.00
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="#">Details</a>
                                </td>
                            </tr>
                            @endforeach
                    
                            <tr class="bg-gray font-17  footer-total">
                                <td colspan="1"><strong>@lang('sale.total'):</strong></td>
                                <td ></td>
                                <td >{{ $stocks->sum('qty_available') }}</td>
                                <td ></td>
                                <td > -{{ $stocks->sum('qty_available') }}</td>
                                <td ></td>
                            </tr>
                        
                        
                        
                    </table>
                    </div>
                </div>
                
                
            </div>
        </div>
    </div>

    
</section>
<!-- /.content -->

@endsection

@section('javascript')
    <input type="hidden" id="getUrl" data-url="{{ action('ReportController@getPhysicalStockReportAajax') }}">
    <script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>
    <script>
        $(document).ready(function(){
            $('.physical_qty').keyup(function(){
                var product_id =$(this).data('id');
                var physical_qty = $(this).val();
                
                var total_stock = $(this).data('stock');
                
                
                var total_phy_stock =  total_stock - physical_qty;
                $("#"+product_id).text(total_phy_stock);
                var url = $('#getUrl').data('url');
                $.ajax({
                    url:url,
                    type:'GET',
                    datatype:'html',
                    cache : false,
                    async: false,
                    data:{product_id,physical_qty},
                    success:function(response)
                    {
                    },
                });
            });
     
        });
        
        // eexcel 
        
        $(document).ready(function(){
            $("#btnExport").click(function() {
                let table = document.getElementsByTagName("table");
                
                TableToExcel.convert(table[0],table[1], { // html code may contain multiple tables so here we are refering to 1st table tag
                   name: `export.xlsx`, // fileName you could use any name
                   sheet: {
                      name: 'Sheet 1' // sheetName
                   }
                });
            });
        });
    </script>
    
    <script>
        $('#datepicker').datepicker({  format: 'yyyy-mm-dd' });
        $('#datepicker1').datepicker({ format: 'yyyy-mm-dd' });
    </script>
@endsection