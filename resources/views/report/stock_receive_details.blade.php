<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header text-center">
		  <h3 style="color:blue">DEDAR MEGA SHOP</h3>
		  <h6 style="">G-1,2 & 3,DCC NORTH SUPER MARKET(EXT),GULSHAN-2</h6>
		  <h3 style="color:green">SUPPLIER WISE DETAILS STOCK POSITION OF SHOP</h3>
		  <p>Report Date & Time Upto: <b> {{ date('d/m/Y', strtotime($row->transaction_date))}}</b></p>
		  <a class="btn btn-sm btn-info" style="margin-top:26px" id="print_"><i class="fa fa-print"></i></a>
		  <a class="btn btn-xs btn-info" style="margin-top:26px" id="excel_"><i class="fa fa-download"> EXcel </i></a>
	    </div>
	    <div class="modal-body" id="details_print">
      		<div class="row">
      			<div class="col-md-12">
      				<table class="table table-bordered" id="table">
      				    <tr>
      						<th>Challan No</th>
      						<th>{{$row->ref_no}}</th>
      						<th>Suppllier</th>
      						<th><b style="color:blue">{{$row->contact->name}}</b></th>
      						<th>Challan Date</th>
      						<th>{{ date('d/m/Y', strtotime($row->transaction_date))}}</th>
      					</tr>
      					<tr>
      						<th>Barcode</th>
      						<th>Product Information</th>
      						<th>Total Receive</th>
      						<th>Sold To Customer</th>
      						<th>CPU</th>
      						<th>RPU</th>
      						<th>Balance Qty</th>
      						<th>Balance Value at Cost</th>
      					</tr>
      					@php
      						$total_qty=0;
      						$total_price=0;
      					@endphp
      					@foreach($cats as $cat => $category_id)
      					@php
      					$rows=DB::table('purchase_lines as pl')
      						->join('products as p','p.id','=','pl.product_id')
      						->join('variations as v','v.id','=','pl.variation_id')
      						->where('pl.transaction_id',$row->id)
      						->where('p.category_id', $cat)
      						->select('p.name','p.sku','pl.*','v.sell_price_inc_tax','v.default_purchase_price',
      						    DB::raw("(pl.quantity - pl.quantity_sold) as remain_qty"),
      						    DB::raw("((pl.quantity) * v.default_purchase_price) as sub_total"),
      						    DB::raw("((pl.quantity - pl.quantity_sold) * v.sell_price_inc_tax) as remain_price")
      						    )
      						->get();
      					@endphp
      					<tr style="background-color:#eee; font-weight: bold">
      						<td>category :</td>
      						<th colspan="7" style="color:red">{{$category_id}}</th>
      					</tr>
      					@foreach($rows as $item)
      					<tr>
      						<td>{{$item->sku}}</td>
      						<td>{{$item->name}}</td>
      						<td>{{$item->quantity}}</td>
      						<td>{{$item->quantity_sold}}</td>
      						<td>{{$item->default_purchase_price}}</td>
      						<td>{{$item->sell_price_inc_tax}}</td>
      						<td>{{number_format($item->remain_qty,1)}}</td>
      						<td>{{number_format($item->remain_price,1)}}</td>
      					</tr>
      					@php
      						$total_qty +=$rows->sum('remain_qty');
      						$total_price +=$rows->sum('remain_price');
      					@endphp
      					@endforeach
      					<tr style="background-color:#eee; font-weight: bold">
      					    <th></th>
      						<th>Total Of </th>
      						<th colspan="5"><b style="color:red">{{$category_id}}</b></th>
      						<th>{{ number_format($rows->sum('remain_price'),2) }}</th>
      					</tr>
      					@endforeach

      					<tr style="background-color:#ccc; font-weight: bold">
      						<th></th>
      						<th colspan="5">Total</th>
      						<th>{{ number_format($total_qty,1) }}</th>
      						<th>{{ number_format($total_price,1) }}</th>
      					</tr>

      				</table>
      			</div>
      			<div class="col-md-12 text-center">
      			    <div>
      			        <p>Total Stock Qty and Value  at <b>{{ date('d/m/Y', strtotime($row->transaction_date))}}</b>  against this supplier</p>
      			        <p style="color:blue"><b>Total Balance Quantity :  {{ number_format($total_qty,1) }}</b></p>
      			        <p style="color:blue"><b>Value at Cost :  {{ number_format($total_price,1) }}</b></p>
      			    </div>
      			</div>
      	
      		</div>
    
      	</div>
      	<div class="modal-footer">
      	    
	      	<button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
	    </div>
	</div>
</div>


<script type="text/javascript">
    $("#excel_").click(function () {
        $("#table").table2excel({
            exclude: '.exclude',
            filename: 'detail_receipt_stock.xls'
        });
    })
</script>

