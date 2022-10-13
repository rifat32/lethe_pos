<div class="row">
  <div class="col-xs-12">
    <h2 class="page-header">
      Purchase Return (<b>@lang('purchase.ref_no'):</b> #{{ $sell_transfer->ref_no }})
      <small class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($sell_transfer->transaction_date) }}</small>
    </h2>
  </div>
</div>
<div class="row invoice-info">


  <div class="col-sm-4 invoice-col">
    <b>@lang('purchase.ref_no'):</b> #{{ $sell_transfer->ref_no }}<br/>
    <b>@lang('messages.date'):</b> {{ @format_date($sell_transfer->transaction_date) }}<br/>
    <b>Supplier NAme:</b> {{ ($sell_transfer->name) }}<br/>
  </div>
</div>

<br>
<div class="row">
  <div class="col-xs-12">
    <div class="table-responsive">
      <table class="table bg-gray">
        <tr class="bg-green">
          <th>#</th>
          <th>@lang('sale.product')</th>
          <th>@lang('sale.qty')</th>
          <th>@lang('sale.unit_price')</th>
          <th>@lang('sale.subtotal')</th>
        </tr>
        @php 
          $total = 0.00;
        @endphp
        @foreach($products as $value)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
              {{ $value->name }}
           
            </td>
            <td>{{ $value->quantity }}</td>
            <td>{{ $value->unit_price }}</td>
            <td>{{ ( $value->quantity * $value->unit_price) }}</td>
          </tr>
        @endforeach
      </table>
    </div>
  </div>
</div>
<br>
<div class="row">
  
  <div class="col-xs-6">
    <div class="table-responsive">
      <table class="table">
        <tr>
          <th>total purchase return:</th>
          <td></td>
          <td><span class="display_currency pull-right" data-currency_symbol="true" >{{ $sell_transfer->final_total }}</span></td>
        </tr>
      </table>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-sm-6">
    <strong>@lang('purchase.additional_notes'):</strong><br>
    <p class="well well-sm no-shadow bg-gray">
      @if($sell_transfer->additional_notes)
        {{ $sell_transfer->additional_notes }}
      @else
        --
      @endif
    </p>
  </div>
</div>

<!-- {{-- Barcode --}}
<div class="row print_section">
  <div class="col-xs-12">
    <img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($sell_transfer->ref_no, 'C128', 2,30,array(39, 48, 54), true)}}">
  </div>
</div> -->