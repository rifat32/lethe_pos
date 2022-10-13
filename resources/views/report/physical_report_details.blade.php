@extends('layouts.app')
@section('title', __('report.stock_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Physical Stock Report Details</h1>
</section>

<!-- Main content -->
<section class="content">
    <form>
    <div class="row">
        <div class="col-md-3">
            <label for="exampleInputEmail1">From:</label>
            <input type="text" value="" class="form-control" id="datepicker" name="start_date" placeholder="yyyy-mm-dd">
        </div>
        <div class="col-md-3">
            <label for="exampleInputEmail1">To:</label>
            <input value="" type="text" class="form-control" id="datepicker1" name="end_date" placeholder="yyyy-mm-dd">
        </div>
        <div class="col-md-3">
            
        </div>
        <div class="col-md-2">
            
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
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>@lang('business.product')</th>
                                <th>Current Stock <br/><small>(balancing time)</small></th>
                                <th>Balance</th>
                                <th>Physical Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $item)     
                            <tr>
                                <td>{{ $item->sku }}</td>
                                <td>{{ $item->name }}</td>
                                <td>
                                    {{ $item->current_stock }}
                                </td>
                                <td>
                                    {{ $item->current_stock - $item->physical_qty }}
                                </td>
                                <td>
                                    {{ $item->physical_qty }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray font-17  footer-total">
                                <td colspan="2"><strong>@lang('sale.total'):</strong></td>
                                <td >{{$products->sum('current_stock')}}</td>
                                <td ></td>
                                <td >{{$products->sum('physical_qty')}}</td>
                            </tr>
                        </tfoot>
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
        
        // eexcel 
        
        $(document).ready(function(){
    $("#btnExport").click(function() {
        let table = document.getElementsByTagName("table");
        TableToExcel.convert(table[0], { // html code may contain multiple tables so here we are refering to 1st table tag
           name: `export.xls`, // fileName you could use any name
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