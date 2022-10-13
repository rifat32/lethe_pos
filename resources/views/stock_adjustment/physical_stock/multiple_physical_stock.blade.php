@extends('layouts.app')
@section('title', __('report.stock_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Physical Quantity Store</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row" style="margin-left:5%;">
        <div class="col-md-10">
            <label for="Product">Product SKU</label>
            <input type="text" id="product_sky" class="form-control" autocomplete="off" autofocus>
        </div>
    </div>
    <br/>

    <form action="{{ action('StockAdjustmentController@multiProductPhysicalStockPost') }}" method="POST">
        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-body">
                        
                        <input type="text" name="date" class="form-control" style="width:300px" id="date" placeholder="yyyy-mm-dd" autocomplete="off">
                        <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>SKU</th>
                                    <th>@lang('business.product')</th>
                                    <th>@lang('sale.unit_price')</th>
                                    <th>@lang('report.current_stock')</th>
                                    <th>Balance</th>
                                    <th>Physical Stock</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody  id="showResult">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="8">
                                        @php
                                            $sessionStatus = session()->has('physicalStockSession') ? session()->get('physicalStockSession')  : [];
                                            //$total = array_sum(array_column($cart,'total_price'));
                                        @endphp
                                        <input type="submit" value="Submit" id="submit" class="btn btn-primary">
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
<!-- /.content -->

@endsection

@section('javascript')

    <input type="hidden" id="default" data-url="{{ action('StockAdjustmentController@multiProductPhysicalStockAjaxSessionDefault') }}">
    
    <script>
        $(document).ready(function(){
            var url = $('#default').data('url');
            $.ajax({
                url:url,
                type:'GET',
                datatype:'html',
                cache : false,
                async: false,
                success:function(response)
                {
                    if(response.status){
                        $('#showResult').html(response.data);
                        $('#product_sky').val('');
                        $('#product_sky').focus();
                    }
                    else{
                        return ;
                    }
                },
            });
        });
    </script>


    <input type="hidden" id="getUrl" data-url="{{ action('StockAdjustmentController@multiProductPhysicalStockAjaxSession') }}">
    
    <script>
        $(document).ready(function(){
            $('#product_sky').keyup(function(e){
                var keyCode = e.which;
                //var product_id =$(this).data('id');
                var sku = $(this).val();
                
                //var total_stock = $(this).data('stock');
            setTimeout(function(){
                if($('#product_sky').val() == sku)
                {
                    var url = $('#getUrl').data('url');
                    $.ajax({
                        url:url,
                        type:'GET',
                        datatype:'html',
                        cache : false,
                        async: false,
                        data:{sku},
                        success:function(response)
                        {
                            if(response.status){
                                $('#showResult').html(response.data);
                                $('#product_sky').val('');
                                $('#product_sky').focus();
                            }
                            else{
                                return ;
                            }
                        },
                    });
                }
            }, 3000);
            });
        });
    </script>


    <input type="hidden" id="getInsetUrlSingleSession" data-url="{{ action('StockAdjustmentController@multiProductPhysicalStockAjaxSessionSingel') }}">
    <script>
        $(document).ready(function(){
            $(document).on('blur','.physical_qty',function(){
                var product_id =$(this).data('id');
                var physical_qty = $(this).val();
                
          
                var total_stock = $(this).data('stock');
                var total_phy_stock = physical_qty- total_stock;
                $("#"+product_id).text(total_phy_stock);
       

                var url = $('#getInsetUrlSingleSession').data('url');
                $.ajax({
                    url:url,
                    type:'GET',
                    datatype:'html',
                    cache : false,
                    async: false,
                    data:{product_id,physical_qty},
                    success:function(response)
                    {
                        if(response.status){
                            $('#showResult').html(response.data);
                            $('#product_sky').val('');
                            $('#product_sky').focus();
                           
                        }
                        else{
                            return ;
                        }
                    },
                });
            });
        });
    </script>


    <input type="hidden" id="getInsetUrl" data-url="{{ action('ReportController@getPhysicalStockReportAajax') }}">
    
    <script>
        $(document).ready(function(){
            $(document).on('keyup','.physical_qty',function(){
                var product_id =$(this).data('id');
                var physical_qty = $(this).val();
                
                var total_stock = $(this).data('stock');
                var total_phy_stock =  physical_qty -total_stock;
                $("#"+product_id).text(total_phy_stock);

                var url = $('#getInsetUrl').data('url');
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
    </script>
    
    <script>
        $(document).ready(function(){
            $(document).on('click','.remove',function(){
                var product_id =$(this).data('id');
                var url = $(this).data('url');
                $.ajax({
                    url:url,
                    type:'GET',
                    datatype:'html',
                    cache : false,
                    async: false,
                    data:{product_id},
                    success:function(response)
                    {
                        if(response.status){
                            $('#showResult').html(response.data);
                            $('#product_sky').val('');
                            $('#product_sky').focus();
                           
                        }
                        else{
                            return ;
                        }
                    },
                });
            });
     
        });
        
         $('#date').datepicker({  format: 'yyyy-mm-dd' });
    </script>
    
@endsection