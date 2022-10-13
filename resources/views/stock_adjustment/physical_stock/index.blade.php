@extends('layouts.app')
@section('title', __('report.stock_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Physical Stock List</h1>
    
@if(Session::has('success'))
<div class="alert alert-success">{{ Session::get('success') }}
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
</button>
</div>
@endif
</section>

<!-- Main content -->
<section class="content">
    <form>
    <div class="row">
        <div class="col-md-3">
            <input type="text" value="{{request()->name ?request()->name:''}}" class="form-control" name="name" placeholder="search Here..">
        </div>
    </div>
    </form><br/>

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table" border="1">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Date</th>
                                <th>@lang('business.product')</th>
                                <th>Current Stock <br/><small>(balancing time)</small></th>
                                <th>Balance</th>
                                <th>Physical Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $item)     
                            <tr>
                                <td>{{ date('d-m-Y', strtotime($item->created_at)) }}</td>
                                <td>{{ $item->sku }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->current_stock }}</td>
                                <td>{{$item->physical_qty- $item->current_stock }}</td>
                                <td>{{ $item->physical_qty }}</td>
                                <td>
                                    <a class="btn btn-success btn-sm btn-modal" data-href="{{ action('StockAdjustmentController@PhysicalStockEdit',$item->id)}}" data-container=".container">Edit</a>
                                    <a class="btn btn-danger btn-sm" href="{{ action('StockAdjustmentController@PhysicalStockDelete',$item->id)}}">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray font-17  footer-total">
                                <td colspan="1"><strong>@lang('sale.total'):</strong></td>
                                <th>{{$products->count()}}</th>
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

<div class="modal fade container" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true"></div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
<script>
    var amount=0;
     $(document).on('keyup','#physical_qty',function(){
         amount=$(this).val();
         var due=$('#current_stock').val();
         update_amount=(amount - due);
         $('#balance').val(update_amount);


     });
</script>
@endsection