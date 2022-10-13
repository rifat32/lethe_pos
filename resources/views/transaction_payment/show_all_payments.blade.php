<div class="modal-dialog" role="document">
  <div class="modal-content">

    <div class="modal-header">
      <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      
    </div>

    <div class="modal-body">

        <div class="row invoice-info">
          <div class="col-sm-4 invoice-col">
             @lang('contact.customer'):
            <address>
              <strong>{{ $contact->name }}</strong>
             
              @if(!empty($contact->landmark))
                <br>{{$contact->landmark}}
              @endif
              @if(!empty($contact->city) || !empty($contact->state) || !empty($contact->country))
                <br>{{implode(',', array_filter([$contact->city, $contact->state, $contact->country]))}}
              @endif
              @if(!empty($contact->tax_number))
                <br>@lang('contact.tax_no'): {{$contact->tax_number}}
              @endif
              @if(!empty($contact->mobile))
                <br>@lang('contact.mobile'): {{$contact->mobile}}
              @endif
              @if(!empty($contact->email))
                <br>Email: {{$contact->email}}
              @endif
            </address>
          </div>
          <div class="col-md-4 invoice-col">
            @lang('business.business'):
            <address>
              <strong>{{ $contact->business->name }}</strong>
            
             
            
              
              @if(!empty($contact->business->tax_number_1))
                <br>{{$contact->business->tax_label_1}}: {{$contact->business->tax_number_1}}
              @endif

              @if(!empty($contact->business->tax_number_2))
                <br>{{$contact->business->tax_label_2}}: {{$contact->business->tax_number_2}}
              @endif

            
        
            </address>
          </div>
          <div class="col-sm-4 ">
          
           
        
          </div>
        </div>
   
 
     
      <div class="row">
        <div class="col-md-12">
          <table class="table table-striped">
            <tr>
              <th>@lang('messages.date')</th>
              <th>@lang('purchase.ref_no')</th>
              <th>@lang('purchase.amount')</th>
              <th>@lang('purchase.payment_method')</th>
              <th>@lang('purchase.payment_note')</th>
              <th>Total Due</th>
              <th>Total Paid</th>
              <th>Total Balance</th>
              @if($accounts_enabled)
                <th>@lang('lang_v1.payment_account')</th>
              @endif
            
            </tr>
            @forelse ($payments as $payment)

            @php
    $total_transaction_amount =    \App\Transaction::where("transactions.contact_id",$payment->contact_id)
      ->where('transactions.type', 'sell')
      ->where('transactions.status', 'final')
      ->where("transactions.order_id",null)
      ->where("transactions.created_at","<=",$payment->created_at)
     ->get()
     ->sum("final_total");
   
     $total_transaction_payments =   \App\TransactionPayment::join('transactions', 'transaction_payments.transaction_id', '=', 'transactions.id')

       ->where("transactions.contact_id",$payment->contact_id)
        ->where('transactions.type', 'sell')
        ->where('transactions.status', 'final')
        ->where("transactions.order_id",null)
        ->where("transaction_payments.created_at","<=",$payment->created_at)
       ->get()
       ->sum("amount");
@endphp
                <tr>
                  <td>{{ @format_date($payment->paid_on) }}</td>
                  <td>{{ $payment->payment_ref_no }}</td>
                  <td><span class="display_currency" data-currency_symbol="true">{{ $payment->amount }}</span></td>
                  <td>{{ $payment_types[$payment->method] }}</td>
                  <td>{{ $payment->note }}</td>
                  @if($accounts_enabled)
                    <td>{{$payment->payment_account->name or ''}}</td>
                  @endif
                  <td>
                              
                    {{
                        $total_transaction_amount 
                        - 
                        $total_transaction_payments
                        
                        }}
                   
                </td>
                <td>
                  
                    {{
                       $total_transaction_payments
                        
                        }}
                   
                </td>
                <td>
                  
                    {{
                       $total_transaction_amount
                        
                        }}
                   
                </td>
                </tr>
            @empty
                <tr class="text-center">
                  <td colspan="6">@lang('purchase.no_records_found')</td>
                </tr>
            @endforelse
          </table>
        </div>

      </div>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn btn-primary no-print" 
          aria-label="Print" 
            onclick="$(this).closest('div.modal').printThis();">
          <i class="fa fa-print"></i> @lang( 'messages.print' )
      </button>
      <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->