@extends('layouts.app')
@section('title', __( 'lang_v1.all_sales'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang( 'sale.sells')
        <small></small>
    </h1>
</section>

<!-- Main content -->

<section class="content no-print">
	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">@lang( 'lang_v1.all_sales')</h3>
            @can('sell.create')
            	<div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action('SellController@create')}}">
    				<i class="fa fa-plus"></i> @lang('messages.add')</a>
                   
                </div>
            @endcan
        </div>
        <div class="box-body">
            @can('direct_sell.access')
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="input-group">
                              <button type="button" class="btn btn-primary" id="sell_date_filter">
                                <span>
                                  <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                                </span>
                                <i class="fa fa-caret-down"></i>
                              </button>
                            </div>
                          </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <div style="display: flex; justify-content: end;">
                        <button class="btn btn-primary" style="margin-right:.5rem;" onclick="tableToExcel('due_table','shit1','Payment')">Excel</button>
                        <button class="btn btn-primary" onclick="ExportPdf()">PDF</button>
                    </div>
                
                    
            	<table class="table table-bordered table-striped ajax_view" id="due_table">
            		<thead>
            			<tr>
            				<th>@lang('messages.date')</th>
                            <th>@lang('sale.invoice_no')</th>
                            <th>Credit</th>
                            {{-- <th>Due</th>
                            <th>Balance</th> --}}
                            <th>Total Due</th>
                            {{-- <th>Total Paid</th> --}}
                            <th>Total Balance</th>
    						{{-- <th>@lang('sale.customer_name')</th>
                            <th>@lang('sale.location')</th>
                            <th>@lang('sale.payment_status')</th>
    						<th>@lang('sale.total_amount')</th>
    						
    						<th>Discount Amount</th>
                            <th>@lang('sale.total_paid')</th>
                            <th>@lang('purchase.payment_due')</th>
                              <th>Type</th>
    						<th>@lang('messages.action')</th> --}}
            			</tr>
            		</thead>
                    <tbody>
                        
                  @php
                        $paid = 0;
                  @endphp     

                        @foreach ($transaction_payments as $transaction_payment)

@php
//  $total_transaction_return_amount =    \App\Transaction::where("transactions.contact_id",$transaction_payment->contact_id)
//       ->where('transactions.type', 'sell')
//       ->where('transactions.status', 'final')
//       ->where("transactions.order_id",null)
//       ->where("transactions.created_at","<=",$transaction_payment->created_at)
//      ->get()
//      ->sum("final_total");
    $total_transaction_amount =    \App\Transaction::where("transactions.contact_id",$transaction_payment->contact_id)
      ->where('transactions.type', 'sell')
      ->where('transactions.status', 'final')
      ->where("transactions.order_id",null)
      ->where("transactions.created_at","<=",$transaction_payment->created_at)
     ->get()
     ->sum("final_total");
   
     $total_transaction_payments =   \App\TransactionPayment::join('transactions', 'transaction_payments.transaction_id', '=', 'transactions.id')

       ->where("transactions.contact_id",$transaction_payment->contact_id)
        ->where('transactions.type', 'sell')
        ->where('transactions.status', 'final')
        ->where("transactions.order_id",null)
        ->where("transaction_payments.created_at","<=",$transaction_payment->created_at)
       ->get()
       ->sum("amount");
     
       $paid += $transaction_payment->amount;
@endphp


                      {{-- @php  

                       if($transaction_payment->is_return){
                        $transaction_payment->amount *= -1;
                      }

$paymentTotal = \App\TransactionPayment::where("transaction_id",$transaction_payment->transaction_id)
->where("id" , "<=" , $transaction_payment->id)
->get()
->sum("amount");
        
                      @endphp --}}
                        <tr>  
                            <td>{{$transaction_payment->created_at}}</td>
                            <td>{{$transaction_payment->invoice_no}}</td>
                            <td>
                              
                                {{$transaction_payment->amount}}
                               
                            </td>
                            {{-- <td>
                              
                                {{$transaction_payment->final_total - $paymentTotal }}
                               
                            </td>
                           
                            <td>
                              
                                {{$transaction_payment->final_total}}
                               
                            </td> --}}
                            <td>
                              
                                {{
                                    $total_transaction_amount 
                                    - 
                                    $total_transaction_payments
                                    
                                    }}
                               
                            </td>
                            {{-- <td>
                              
                                {{
                                   $total_transaction_payments
                                    
                                    }}
                               
                            </td> --}}
                            <td>
                              
                                {{
                                   $total_transaction_amount
                                    
                                    }}
                               
                            </td>
                            
                         </tr>     
                       
                     
                        @endforeach 
                        
                      
                        
                        
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray font-17 footer-total text-center">
                            <td colspan="2"><strong>@lang('sale.total'):</strong></td>
                            <td>{{$paid}}</td>
                            {{-- <td id="footer_payment_status_count"></td>
                            <td><span class="display_currency" id="footer_sale_total" data-currency_symbol ="true"></span></td>
                            <td><span class="display_currency" id="footer_total_discount" data-currency_symbol ="true"></span></td>
                            <td><span class="display_currency" id="footer_total_paid" data-currency_symbol ="true"></span></td>
                            <td class="text-left"><small>@lang('lang_v1.sell_due') - <span class="display_currency" id="footer_total_remaining" data-currency_symbol ="true"></span><br>@lang('lang_v1.sell_return_due') - <span class="display_currency" id="footer_total_sell_return_due" data-currency_symbol ="true"></span></small></td>
                            <td></td> --}}
                        </tr>
                    </tfoot>
            	</table>
                {{$transaction_payments->links()}}
                </div>
            @endcan
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<!-- This will be printed -->
<!-- <section class="invoice print_section" id="receipt_section">
</section> -->

@stop

@section('javascript')
<script>
    function tableToExcel(table, name, filename) {
        
        let uri = 'data:application/vnd.ms-excel;base64,', 
        template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><title></title><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head><body><table>{table}</table></body></html>', 
        base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) },         format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; })}
        
        if (!table.nodeType) table = document.getElementById(table)
        var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}

        var link = document.createElement('a');
        link.download = filename;
        link.href = uri + base64(format(template, ctx));
        link.click();
}



</script>


<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.22/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
<script type="text/javascript">
    function ExportPdf() {
        html2canvas(document.getElementById('due_table'), {
            onrendered: function (canvas) {
                var data = canvas.toDataURL();
                var docDefinition = {
                    content: [{
                        image: data,
                        width: 500
                    }]
                };
                pdfMake.createPdf(docDefinition).download("Table.pdf");
            }
        });
    }
</script>
@endsection
