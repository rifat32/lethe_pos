@extends('layouts.app')
@section('title', __( 'Supplier Stock Receipts' ))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Supplier Wise Stock Receipts Report</h1>
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
                <input type="text" name="start" class="form-control" id="start" value="{{request()->start ? request()->start:''}}" placeholder="yyyy-mm-dd">
              </div>
              <div class="col-md-3">
                <label>Date To</label>
                <input type="text" name="end" class="form-control" id="end" value="{{request()->end ? request()->end:''}}" placeholder="yyyy-mm-dd">
              </div>
              <div class="col-md-2">
                  <button type="submit" class="btn btn-xs btn-success" style="margin-top:26px">SUBMIT</button>
                   <a onclick="window.print();" class="btn btn-sm btn-info" style="margin-top:26px"><i class="fa fa-print"></i></a>
                   <a class="btn btn-xs btn-info" style="margin-top:26px" id="excel"><i class="fa fa-download"></i></a>
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
                            <th>Challan</th>
                            <th>Supplier</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="no-print noExl">Action</th>
                        </tr>
                        @foreach($results as $item)
                        <tr>
                            <td>{{$item->ref_no}}</td>
                            <td>{{$item->contact->name}}</td>
                            <td>{{$item->final_total}}</td>
                            <td>{{$item->status}}</td>
                            <td>{{ date('Y-m-d', strtotime($item->transaction_date))}}</td>
                            <td class="no-print noExl">
                              <a data-href="{{ action('ReportController@StockReceiveDetails',[$item->id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".brands_modal">view</a>
                            </td>
                        </tr>
                        @endforeach
                    </table>  
                </div>
            </div>
            <p>{!! urldecode(str_replace("/?","?",$results->appends(Request::all())->render())) !!}</p>
       </div>
    </div>

    <div class="modal fade brands_modal" tabindex="-1" role="dialog" 
      aria-labelledby="gridSystemModalLabel">
    </div>
</section>
@stop


@section('javascript')
<script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>
<script type="text/javascript">
    $('#start, #end').datepicker({  format: 'yyyy-mm-dd' });
    
    $(document).on('click','a#print_',function(){
        
        var printContents = document.getElementById("details_print").innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        
    })
    
    $("#excel").click(function () {
        $(".table").table2excel({
            exclude: '.noExl',
            filename: 'stock_receive.xls'
        });
    })
</script>
@endsection
